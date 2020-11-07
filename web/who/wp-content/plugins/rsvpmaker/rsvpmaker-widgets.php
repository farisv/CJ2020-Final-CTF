<?php 
/*
RSVPMaker Widgets
*/

/**
 * CPEventsWidget Class
 */
class CPEventsWidget extends WP_Widget {
    function __construct() {
        parent::__construct(false, $name = 'RSVPMaker Events');
    }

    function widget($args, $instance) {		
        extract( $args );
		global $rsvpwidget;
		$rsvpwidget = true;
		$title = (isset($instance['title'])) ? $instance['title'] : __('Events','rsvpmaker');
        $title = apply_filters('widget_title', $title);
		$atts["limit"] = (isset($instance["limit"])) ? $instance["limit"] : 10;
		if(!empty($instance["event_type"]))
		$atts["type"] = (isset($instance["event_type"])) ? $instance["event_type"] : NULL;
		$dateformat = (isset($instance["dateformat"])) ? $instance["dateformat"] : 'M. j';
        global $rsvp_options;
		;?>
              <?php echo $before_widget;?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title;?>
              <?php 
			  
			  $events = rsvpmaker_upcoming_data($atts);
			  if(!empty($events))
			  {
			  echo "\n<ul>\n";
			  foreach($events as $event)
			  	{
				$datestr = '';
				foreach($event['dates'] as $date)
					{
						if(!empty($datestr))
							$datestr .= ', ';
						$datestr .= rsvpmaker_date($dateformat,rsvpmaker_strtotime($date["datetime"]));
					}
				printf('<li><a href="%s">%s</a> - %s</li>',$event['permalink'],$event['title'],$datestr);
					
				}
			
			if(!empty($rsvp_options["eventpage"]))
			  	echo '<li><a href="'.$rsvp_options["eventpage"].'">'.__("Go to Events Page",'rsvpmaker')."</a></li>";
			
			  echo "\n</ul>\n";
			  }
			  			  
			  echo $after_widget;?>
        <?php
		$rsvpwidget = false;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['dateformat'] = strip_tags($new_instance['dateformat']);
	$instance['limit'] = (int) $new_instance['limit'];
	$instance['event_type'] = $new_instance['event_type'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = (isset($instance['title'])) ? esc_attr($instance['title']) : __('Events','rsvpmaker');
		$limit = (isset($instance["limit"])) ? $instance["limit"] : 10;
		$dateformat = (isset($instance["dateformat"])) ? $instance["dateformat"] : 'M. j';
		$event_type = (!empty($instance["event_type"])) ? $instance["event_type"] : '';
        ?>
            <p><label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:','rsvpmaker');?> <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title;?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('limit');?>"><?php _e('Number to Show:','rsvpmaker');?> <input class="widefat" id="<?php echo $this->get_field_id('limit');?>" name="<?php echo $this->get_field_name('limit');?>" type="text" value="<?php echo $limit;?>" /></label></p>

            <p><label for="<?php echo $this->get_field_id('dateformat');?>"><?php _e('Date Format:','rsvpmaker');?> <input class="widefat" id="<?php echo $this->get_field_id('dateformat');?>" name="<?php echo $this->get_field_name('dateformat');?>" type="text" value="<?php echo $dateformat;?>" /></label> (PHP <a target="_blank" href="http://us2.php.net/manual/en/function.date.php">date</a> format string)</p>

<p><label for="<?php echo $this->get_field_id('event_type');?>">
<?php
$tax_terms = get_terms('rsvpmaker-type');
?>
<select class="widefat" id="<?php echo $this->get_field_id('event_type');?>" name="<?php echo $this->get_field_name('event_type');?>" ><option value=""><?php _e('All','rsvpmaker'); ?></option>
<?php
if(is_array($tax_terms))
	{
		foreach ($tax_terms as $tax_term) {
		$s = ($tax_term->name == $event_type) ? ' selected="selected" ' : '';
		echo '<option value="'.$tax_term->name . '" ' . $s . '>' . $tax_term->name.'</option>';
		}
	}
?>
</select>
</p>

        <?php 
    }

} // class CPEventsWidget

/**
 * RSVPTypeWidget Class
 */
class RSVPTypeWidget extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::__construct('rsvpmaker_type_widget', $name = 'RSVPMaker Events by Type');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		if(empty($title))
			$title = __('Events by Type','rsvpmaker');
		$atts["limit"] = ($instance["limit"]) ? $instance["limit"] : 10;
		if(!empty($instance["event_type"]))
        global $rsvp_options;
		;?>
              <?php echo $before_widget;?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title;?>
              <?php 


$args = array( 'hide_empty=0' );
 
$terms = get_terms( 'rsvpmaker-type', $args );
echo '<ul>';
if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
    $count = count( $terms );
    $i = 0;
    foreach ( $terms as $term ) {
        $i++;
		$atts["type"] = $term->name;
	  	$events = rsvpmaker_upcoming_data($atts);
		$count = sizeof($events);
		$countstr = ($count) ? '('.$count.')' : '';
		printf('<li><a href="%s">%s</a> %s</li>',esc_url( get_term_link( $term ) ),$term->name,$countstr);
    }
}
	  echo "\n</ul>\n";
		
			  echo $after_widget;?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['limit'] = (int) $new_instance['limit'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = (isset($instance['title'])) ? esc_attr($instance['title']) : '';
		$limit = (isset($instance["limit"])) ? $instance["limit"] : 10;
        ?>
            <p><label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:','rsvpmaker');?> <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title;?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('limit');?>"><?php _e('Number to Show:','rsvpmaker');?> <input class="widefat" id="<?php echo $this->get_field_id('limit');?>" name="<?php echo $this->get_field_name('limit');?>" type="text" value="<?php echo $limit;?>" /></label></p>

        <?php 
    }

}

class RSVPMakerByJSON extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::__construct('rsvpmaker_by_json', $name = 'RSVPMaker Events (API)');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		if(empty($title))
            $title = __('Events','rsvpmaker');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/','',$title));
		$url = ($instance["url"]) ? $instance["url"] : site_url('/wp-json/rsvpmaker/v1/future');
		$limit = ($instance["limit"]) ? $instance["limit"] : 0;
		$morelink = ($instance["morelink"]) ? $instance["morelink"] : '';
        global $rsvp_options;
		;?>
              <?php echo $before_widget;?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title;?>
<div id="rsvpjsonwidget-<?php echo $slug; ?>"><?php _e('Loading','rsvpmaker'); ?> ...</div>
<script>
var jsonwidget<?php echo $slug; ?> = new RSVPJsonWidget('rsvpjsonwidget-<?php echo $slug; ?>','<?php echo $url; ?>',<?php echo $limit; ?>,'<?php echo $morelink; ?>');
</script>
<?php		
  echo $after_widget;?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['url'] = trim($new_instance['url']);
	$instance['limit'] = $new_instance['limit'];
	$instance['morelink'] = trim($new_instance['morelink']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = (isset($instance['title'])) ? esc_attr($instance['title']) : '';
        if(function_exists('rsvpmaker_upcoming'))
    		$url = (isset($instance["url"])) ? $instance["url"] : site_url('/wp-json/rsvpmaker/v1/future');
        else
    		$url = (isset($instance["url"])) ? $instance["url"] : 'rsvpmaker.com/wp-json/rsvpmaker/v1/future';
		$limit = (isset($instance["limit"])) ? $instance["limit"] : 10;
		$morelink = (isset($instance["morelink"])) ? $instance["morelink"] : '';
        ?>
            <p><label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:','rsvpmaker');?> <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title;?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('url');?>"><?php _e('JSON URL:','rsvpmaker');?> <input class="widefat" id="<?php echo $this->get_field_id('url');?>" name="<?php echo $this->get_field_name('url');?>" type="text" value="<?php echo $url;?>" /></label>
            <br />Examples from rsvpmaker.com demo:
            <br /><a target="_blank" href="https://rsvpmaker.com/wp-json/rsvpmaker/v1/future">all future events</a>
            <br /><a target="_blank" href="https://rsvpmaker.com/wp-json/rsvpmaker/v1/type/featured">events tagged type/featured</a></p>
          <p><label for="<?php echo $this->get_field_id('limit');?>"><?php _e('Maximum # Displayed:','rsvpmaker');?> <input class="widefat" id="<?php echo $this->get_field_id('limit');?>" name="<?php echo $this->get_field_name('limit');?>" type="text" value="<?php echo $limit;?>" /></label><br /><em>Use 0 for no limit</em></p>
            <p><label for="<?php echo $this->get_field_id('morelink');?>"><?php _e('URL for more events:','rsvpmaker');?> <input class="widefat" id="<?php echo $this->get_field_id('morelink');?>" name="<?php echo $this->get_field_name('morelink');?>" type="text" value="<?php echo $morelink;?>" /></label></p>

        <?php 
    }
}
?>