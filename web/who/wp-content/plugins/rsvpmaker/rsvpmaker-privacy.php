<?php



function register_rsvpmaker_exporter( $exporters ) {

  $exporters['rsvpmaker'] = array(

    'exporter_friendly_name' => __( 'RSVPMaker' ),

    'callback' => 'rsvpmaker_exporter',

  );

  return $exporters;

}

add_filter(

  'wp_privacy_personal_data_exporters',

  'register_rsvpmaker_exporter',

  10

);

function rsvpmaker_exporter( $email_address, $page = 1 ) {

global $wpdb;

global $rsvp_options;

$number = 500; // Limit us to avoid timing out

$page = (int) $page;

$start = ($page > 1) ? ($page-1)*500 : 0;

 

$export_items = array();



$sql = "SELECT * FROM ".$wpdb->prefix."rsvpmaker WHERE email LIKE '$email_address' ORDER BY event LIMIT $start, " . ($number + 5);

// get rsvps matching that email

$results = $wpdb->get_results($sql, ARRAY_A);

$group_id = 'rsvpmaker';

$group_label = 'RSVPMaker';

  if(is_array($results))

  foreach ($results as $index => $rsvprow ) {

	  if($index > $number)

		  break;

	  $data = array();

		$title = get_the_title($rsvprow["event"]);

	  	$date = get_rsvp_date($rsvprow["event"]);

	  	$profile = rsvp_row_to_profile($rsvprow);

	  	if(empty($title))

			$title = 'Event deleted?';

	  	else

			$title .= ' ('.date('F j, Y',rsvpmaker_strtotime($date)).')';

	    $data[] = array('name' => 'event_title', 'value'=> $title);

			

	  foreach($profile as $name => $value)

	  {

		  if(!empty($value))

		  $data[] = array('name' => $name, 'value' => $value);		  

	  }



      $export_items[] = array(

        'group_id' => $group_id,

        'group_label' => $group_label,

        'item_id' => 'rsvp-'.$rsvprow['id'],

        'data' => $data,

      );

  

  }

	if(isset($_GET['export_test']))

	{

		echo $sql .'<br />';

		print_r($results);

		print_r($export_items);

		die();

	}

 

  // Tell core if we have more comments to work on still

  $done = count( $results ) < $number;

  return array(

    'data' => $export_items,

    'done' => $done,

  );

}



function rsvpmaker_eraser ($email_address, $page = 1)

{

	global $wpdb;

	$sql = 'DELETE FROM '.$wpdb->prefix."rsvpmaker WHERE email='$email_address'";

	$wpdb->query($sql);

	return array( 'items_removed' => true,

    'items_retained' => false, // always false in this example

    'messages' => array(), // no messages in this example

    'done' => true,

  );

}



function register_rsvpmaker_eraser( $erasers ) {

  $erasers['rsvpmaker'] = array(

    'eraser_friendly_name' => __( 'RSVPMaker' ),

    'callback'             => 'rsvpmaker_eraser',

    );

  return $erasers;

}

 

add_filter(

  'wp_privacy_personal_data_erasers',

  'register_rsvpmaker_eraser',

  10

);



function rsvpmaker_plugin_add_privacy_policy_content() {

    if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {

        return;

    }

 

    $content = sprintf(

        __( 'When you register for an event managed with the RSVPMaker plugin, your name, email, and any other information you choose to supply to a database on the website and associated with that event. RSVPMaker does not collect other technical information such as IP address or location. It does attempt to add cookie files that make it easier for individuals to update their event registrations.',

        'rsvpmaker' )

    );

 

    wp_add_privacy_policy_content(

        'RSVPMaker',

        wp_kses_post( wpautop( $content, false ) )

    );

}





?>