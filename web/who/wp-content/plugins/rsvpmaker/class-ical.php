<?php

/**
 * RSVPMaker_iCal Class.
 * Some of this code is derived from http://wordpress.org/plugins/events-maker
 */
class RSVPMaker_iCal {

	public function __construct() {
		//if ( (int) RSVPMaker()->options['general']['ical_feed'] != false ) {
			//actions
			add_action( 'init', array( $this, 'add_events_ical_feed' ) );

			//filters
			add_filter( 'parse_request', array( $this, 'parse_ical_request' ) );
		//}
	}

	/**
	 * Add events feed to WP RSS feeds.
	 */
	public function add_events_ical_feed() {
		add_feed( 'ical', array( $this, 'generate_ical_feed' ) );
	}

	/**
	 * Recognize iCal request.
	 */
	public function parse_ical_request( $request ) {
		if ( is_admin() )
			return $request;

		// is this an ical feed request
		if ( isset( $request->query_vars['feed'] ) && $request->query_vars['feed'] === 'ical' ) {
			// is this an event post type feed request
			if ( isset( $request->query_vars['post_type'] ) && (is_array( $request->query_vars['post_type'] ) ? in_array( 'event', $request->query_vars['post_type'] ) : $request->query_vars['post_type'] === 'event') ) {
				// single event
				if ( isset( $request->query_vars['event'] ) && ! empty( $request->query_vars['event'] ) )
					$this->generate_ical_feed( 'single', sanitize_title( $request->query_vars['event'] ) );
				// event-category
				elseif ( isset( $request->query_vars['event-category'] ) && ! empty( $request->query_vars['event-category'] ) )
					$this->generate_ical_feed( 'event-category', sanitize_title( $request->query_vars['event-category'] ) );
				// event-tag
				elseif ( isset( $request->query_vars['event-tag'] ) && ! empty( $request->query_vars['event-tag'] ) )
					$this->generate_ical_feed( 'event-tag', sanitize_title( $request->query_vars['event-tag'] ) );
				// event-location
				elseif ( isset( $request->query_vars['event-location'] ) && ! empty( $request->query_vars['event-location'] ) )
					$this->generate_ical_feed( 'event-location', sanitize_title( $request->query_vars['event-location'] ) );
				// event-organizer
				elseif ( isset( $request->query_vars['event-organizer'] ) && ! empty( $request->query_vars['event-organizer'] ) )
					$this->generate_ical_feed( 'event-organizer', sanitize_title( $request->query_vars['event-organizer'] ) );
				// events archive
				else
					$this->generate_ical_feed( 'post_type', 'event' );
			}
		}

		return $request;
	}

	/**
	 * Generate iCal file content.
	 */
	public function generate_ical_feed( $query, $item ) {
		$events = array();
		$output = '';

		switch ( $query ) {
			case 'single':
				$args = array(
					'posts_per_page' => 1,
					'no_found_rows'	 => true,
					'name'			 => $item
				);
				$feedname = 'single-event-' . $item;
				break;

			case 'post_type':
				$args = array(
					'posts_per_page' => -1
				);
				$feedname = 'events';
				break;

			case 'event-category':
			case 'event-tag':
			case 'event-location':
			case 'event-organizer':
				$args = array(
					'posts_per_page' => -1,
					'tax_query'		 => array(
						array(
							'taxonomy'	 => $query,
							'field'		 => 'slug',
							'terms'		 => $item
						)
					)
				);
				$feedname = $query . '-' . $item;
				break;
		}

		$args = apply_filters( 'em_icl_feed_query_args', array_merge( $args, array(
			'post_type'				 => 'event',
			'suppress_filters'		 => false,
			'event_show_past_events' => false,
			'event_show_occurrences' => false,
			'orderby'				 => 'event_start_date',
			'order'					 => 'asc'
				)
			) );

		// get events data
		$events = get_posts( $args );

		// events query
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				// get the event date
				$start_date = get_post_meta( $event->ID, '_event_start_date', true );
				$end_date = get_post_meta( $event->ID, '_event_end_date', true );

				// convert to gmt, all day
				if ( em_is_all_day( $event->ID ) ) {
					$event_start = date( 'Ymd', rsvpmaker_strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', rsvpmaker_strtotime( $start_date ) ) ) ) );
					$event_end = date( 'Ymd', rsvpmaker_strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', rsvpmaker_strtotime( $end_date ) ) ) ) );
				}
				// convert to gmt, other
				else {
					$event_start = date( 'Ymd\THis\Z', rsvpmaker_strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', rsvpmaker_strtotime( $start_date ) ) ) ) );
					$event_end = date( 'Ymd\THis\Z', rsvpmaker_strtotime( get_gmt_from_date( date( 'Y-m-d H:i:s', rsvpmaker_strtotime( $end_date ) ) ) ) );
				}

				// get categories, if available
				if ( $categories = wp_get_post_terms( $event->ID, 'event-category', array( 'fields' => 'names' ) ) ) {
					if ( ! empty( $categories ) && is_array( $categories ) && ! is_wp_error( $categories ) )
						$categories_output = "CATEGORIES:" . $this->escape_string( implode( ',', (array) $categories ) ) . "\r\n";
				}

				// get location, if available	
				if ( $location = em_get_locations_for( $event->ID ) ) {
					$location_output = "LOCATION:" . $location[0]->name . " ";
					$location_meta = array();

					if ( ! empty( $location[0]->location_meta ) ) {
						foreach ( $location[0]->location_meta as $key => $value ) {
							if ( in_array( $key, array( 'address', 'city', 'state', 'zip', 'country' ) ) && ! empty( $value ) )
								$location_meta[] = $this->escape_string( esc_attr( $value ) );
						}
						$location_output .= implode( ', ', $location_meta );
					}
					$location_output .= "\r\n";
				}

				// get organizer, if available	
				if ( $organizer = em_get_organizers_for( $event->ID ) ) {
					$organizer_meta = array();

					if ( ! empty( $organizer[0]->organizer_meta ) ) {
						$organizer_output = "ORGANIZER;";

						// contact name
						if ( isset( $organizer[0]->organizer_meta['contact_name'] ) && ! empty( $organizer[0]->organizer_meta['contact_name'] ) ) {
							$organizer_output .= "CN=" . $this->escape_string( $organizer[0]->organizer_meta['contact_name'] );
							// email
							if ( isset( $organizer[0]->organizer_meta['email'] ) && ! empty( $organizer[0]->organizer_meta['email'] ) )
								$organizer_output .= ":MAILTO:" . $this->escape_string( esc_url( $organizer[0]->organizer_meta['email'] ) );
							else
								$organizer_output .= ";";
						}

						$organizer_output .= "\r\n";
					}
				}

				// single event output
				$output .= "BEGIN:VEVENT\r\n";
				$output .= "DTEND:" . $event_end . "\r\n";
				$output .= "UID:" . uniqid() . "\r\n";
				$output .= "DTSTAMP:" . date( 'Ymd\THis\Z', current_time('timestamp') ) . "\r\n";
				$output .= ! empty( $categories_output ) ? $categories_output : '';
				$output .= ! empty( $location_output ) ? $location_output : '';
				$output .= ! empty( $organizer_output ) ? $organizer_output : '';
				$output .= "DESCRIPTION:" . $this->escape_string( str_replace( array( "\r", "\n" ), " ", wp_strip_all_tags( $event->post_content ) ) ) . "\r\n";
				$output .= "URL;VALUE=URI:" . $this->escape_string( esc_url( get_permalink( $event->ID ) ) ) . "\r\n";
				$output .= "SUMMARY:" . $this->escape_string( esc_attr( $event->post_title ) ) . "\r\n";
				$output .= "DTSTART:" . $event_start . "\r\n";
				$output .= "END:VEVENT\r\n";
			}

			// set the correct headers for this file
			header( 'Content-type: text/calendar; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=' . apply_filters( 'em_icl_feed_filename', 'em-' . date( 'Ymd', current_time('timestamp') ) . '-' . $feedname, $query, $item ) . '.ical' );

			echo "BEGIN:VCALENDAR\r\n";
			echo "VERSION:2.0\r\n";
			echo "PRODID:-//DIGITAL FACTORY//EVENTS MAKER V" . get_option( 'RSVPMaker_version' ) . "//EN\r\n";
			echo "CALSCALE:GREGORIAN\r\n";

			// all events data
			echo $output;

			echo "END:VCALENDAR\r\n";
		}

		exit;
	}

	/**
	 * Helper: escape a string of characters.
	 */
	public function escape_string( $string ) {
		return preg_replace( '/([\,;])/', '\\\$1', $string );
	}

	/**
	 * Helper: convert br to nl.
	 */
	public function br2nl( $string ) {
		return preg_replace( '/\<br(\s*)?\/?\>/i', "\r\n", $string );
	}

}

new RSVPMaker_iCal();
