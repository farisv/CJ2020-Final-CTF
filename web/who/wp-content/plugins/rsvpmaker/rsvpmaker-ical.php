<?php

function rsvpmaker_to_ical_email ($post_id = 0, $from_email, $rsvp_email) {
global $post;
global $rsvp_options;
if($post_id > 0)
	$post = get_post($post_id);
global $wpdb;
if(($post->post_type != 'rsvpmaker') )
	return;
$sql = "SELECT meta_value FROM ".$wpdb->postmeta." WHERE meta_key='_rsvp_dates' AND post_id=".$post->ID.' ORDER BY meta_value';
$datetime = $wpdb->get_var($sql);
$duration = get_post_meta($post_id,'_'.$datetime, true);

$start_ts = rsvpmaker_strtotime($datetime);
$duration_ts = (empty($duration)) ? rsvpmaker_strtotime($datetime . ' +1 hour') : rsvpmaker_strtotime($duration);
$hangout = get_post_meta($post->ID, '_hangout',true);
$description = '';
if(!empty($hangout))
	$description .= "Google Hangout: ".$hangout."\n";
$description .= "Event info: " . get_permalink($post->ID);
$summary = $post->post_title;
$venue_meta = get_post_meta($post->ID,'venue',true);
$venue = (empty($venue_meta)) ? 'See: '. get_permalink($post->ID) : $venue_meta;
$dtstamp = gmdate("Ymd")."T".gmdate("His")."Z";
$start = gmdate('Ymd',$start_ts);
$start_time = gmdate('His',$start_ts);
$end = gmdate('Ymd',$duration_ts);
$end_time = gmdate('His',$duration_ts);
$event_id = $post->ID;
$sequence = 0;
$status = 'CONFIRMED';
$ical[] = "BEGIN:VCALENDAR";
$ical[] ="VERSION:2.0";
$ical[] ="PRODID:-//WordPress//RSVPMaker//EN";
$ical[] ="METHOD:REQUEST";
$ical[] ="BEGIN:VEVENT";
$ical[] ="DTSTAMP:".$dtstamp;
$ical[] ="ORGANIZER;SENT-BY=\"MAILTO:".$from_email."\":MAILTO:".$from_email;
$ical[] ="ATTENDEE;CN=".$rsvp_email.";ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;"."RSVP=TRUE:mailto:".$from_email;
$ical[] ="UID:".strtoupper(md5($event_id))."-rsvpmaker.com";
$ical[] ="SEQUENCE:".$sequence;
$ical[] ="STATUS:".$status;
$ical[] ="DTSTART:".$start."T".$start_time."Z";
$ical[] ="DTEND:".$end."T".$end_time."Z";
$ical[] ="LOCATION:".$venue;
$ical[] ="SUMMARY:".$summary;
$ical[] ="DESCRIPTION:".$description;
$ical[] ="BEGIN:VALARM";
$ical[] ="TRIGGER:-PT15M";
$ical[] ="ACTION:DISPLAY";
$ical[] ="DESCRIPTION:Reminder";
$ical[] ="END:VALARM";
$ical[] ="END:VEVENT";
$ical[] ="END:VCALENDAR";
$icalstring = '';
foreach($ical as $line)
	{
		if(strlen($line) >= 70)
		{
			$line = trim(chunk_split($line,70,"\r\n "));
		}
		$icalstring .= $line."\r\n";
	}
return trim($icalstring);
}
?>