<?php
global $wpdb;
$tables = array('rsvp_dates', 'rsvp_volunteer_time', 'wp_rsvpmaker');
foreach($tables as $slug)
	{
	$sql = "DROP TABLE ".$wpdb->prefix.$slug;
	$wpdb->query($sql);
	}
$sql = "DELETE FROM  ".$wpdb->posts." WHERE post_type='rsvpmaker' ";
$wpdb->query($sql);
delete_option('RSVPMAKER_Options');
;?>