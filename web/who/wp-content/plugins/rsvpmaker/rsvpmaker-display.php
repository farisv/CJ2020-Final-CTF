<?php

if(!wp_is_json_request()){
	add_shortcode('rsvpautorenew_test','rsvpautorenew_test');
	add_shortcode('rsvpmaker_embed_form','rsvpmaker_form');
	add_shortcode('rsvpmaker_form','rsvpmaker_form');
	add_shortcode('event_listing', 'event_listing');
	add_shortcode("rsvpmaker_upcoming","rsvpmaker_upcoming");
	add_shortcode("rsvpmaker_calendar","rsvpmaker_calendar");
	add_shortcode('rsvpmaker_timed','rsvpmaker_timed');
	add_shortcode('rsvpmaker_looking_ahead','rsvpmaker_looking_ahead');
	add_shortcode('ylchat','ylchat');
	add_shortcode("rsvpmaker_next","rsvpmaker_next");
	add_shortcode("rsvpmaker_one","rsvpmaker_one");
	add_shortcode('rsvpdateblock','rsvpdateblock');
	add_shortcode('rsvpmaker_daily_schedule','rsvpmaker_daily_schedule');
	add_shortcode('rsvpmaker_email_content', 'rsvpmaker_email_content');
	add_shortcode('rsvpmaker_upcoming_email','rsvpmaker_upcoming_email');
	add_shortcode('rsvpmaker_recent_blog_posts','rsvpmaker_recent_blog_posts');
	add_shortcode('rsvpcount','rsvpcount');
	add_shortcode('embed_dateblock','embed_dateblock');
	add_shortcode('rsvp_report_shortcode','rsvp_report_shortcode');
	add_shortcode('rsvpguests','rsvpguests');
	add_shortcode('rsvpprofiletable','rsvpprofiletable');
	add_shortcode('rsvpnote','rsvpnote');
	add_shortcode('rsvpfield','rsvpfield');
	add_shortcode('rsvpmaker_stripe_checkout','rsvpmaker_stripe_checkout');
	add_shortcode('RSVPMaker_chimpshort', 'RSVPMaker_chimpshort');
	//primarily used in email confirmation messages etc.
	add_shortcode('rsvptitle', 'rsvptitle_shortcode');
	add_shortcode('rsvpdate', 'rsvpdate_shortcode');
	add_shortcode('datetime', 'rsvpdatetime_shortcode');
	add_shortcode('event_title_link', 'event_title_link');
}

//event_content defined in rsvpmaker-pluggable.php to allow for variations
add_filter('the_content','event_content_anchor',50);

function event_content_anchor ($content) {
global $post;
if(!is_single() || ($post->post_type != 'rsvpmaker') )
	return $content;
return '<div id="rsvpmaker_top"></div>'.$content;
}

add_filter('the_content','event_content',5);

function event_js($content) {
global $post;
if(is_email_context ())
	return $content;
if(!is_single())
	return $content;
if(!strpos($content,'id="rsvpform"') )
	return $content;
if($post->post_type != 'rsvpmaker')
	return $content;
return $content . rsvp_form_jquery();
}

add_filter('the_content','event_js',15);

function rsvp_url_date_query ($direction = '') {
	$date = '';
	if(!isset($_GET["cy"]) || !isset($_GET["cm"]))
		return '';
	$date .= (int) $_GET["cy"];
	$cm = (int) $_GET["cm"];
	$date .= ($cm < 10) ? '-0'.$cm : '-'.$cm;
	if(isset($_GET["cd"]))
	{
		$cd = (int) $_GET["cd"];
		$date .= ($cd < 10) ? '-0'.$cd : '-'.$cd;
	}
	elseif($direction == 'past')
		$date .= '-31';
	else
		$date .= '-01';
	return $date;
}

function event_listing($atts = array()) {
global $rsvp_options;
$events = rsvpmaker_upcoming_data($atts);
$date_format = (isset($atts["date_format"])) ? $atts["date_format"] : $rsvp_options["long_date"];

if(is_array($events))
foreach($events as $event)
	{
	$dateline = '';
	if(is_array($event['dates']))
	foreach($event['dates'] as $date)
		{
		$t = rsvpmaker_strtotime($date['datetime']);
		$dateline .= rsvpmaker_strftime($date_format, $t).' ';
		}
	$listings .= sprintf('<li><a href="%s">%s</a> %s</li>'."\n",esc_url_raw($event['permalink']),esc_html($event["title"]),$dateline);
	}	

	if(!empty($atts["limit"]) && !empty($rsvp_options["eventpage"]))
		$listings .= '<li><a href="'.esc_url($rsvp_options["eventpage"]).'">'.__("Go to Events Page",'rsvpmaker')."</a></li>";

	if(!empty($atts["title"]))
		$listings = "<p><strong>".esc_html($atts["title"])."</strong></p>\n<ul id=\"eventheadlines\">\n$listings</ul>\n";
	else
		$listings = "<ul id=\"eventheadlines\">\n$listings</ul>\n";

	if(isset($_GET['debug']))
		$listings .= '<pre>'.var_export($events, true).'</pre>';
	
	return $listings;
}

function get_next_events_link( $label = '', $no_events = '' ) {
global $last_time;
global $wpdb;

$sql = "SELECT post_id from $wpdb->postmeta JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE meta_key='_rsvp_dates' AND meta_value > '".rsvpmaker_date("Y-m-d H:i:s",$last_time)."' AND post_status='publish' ";

$at_least_one = $wpdb->get_var($sql);
if(!$at_least_one)
	{
	if(!empty($no_events))
		return '<p class="no_events">'.$no_events.'</p>';;
	}
	
	$link = get_rsvpmaker_archive_link();
	$link .= (strpos($link,'?')) ? "&" : '?';
	$link .= 'cd='.rsvpmaker_date('d',$last_time).'&cm='.date('m',$last_time).'&cy='.date('Y',$last_time);
		$attr = apply_filters( 'next_posts_link_attributes', '' );
		$link = '<a href="' . $link ."\" $attr>" . $label . ' &raquo;</a>';
	if(isset($link))
		return "<p class=\"more_events\">$link</p>";
}

function rsvpmaker_select($select) {
  global $wpdb;

    $select .= ", rsvpdates.meta_value as datetime, meta_id, date_format(rsvpdates.meta_value,'%M %e, %Y') as date";
  return $select;
}

function rsvpmaker_join($join) {
  global $wpdb;
    return $join." JOIN ".$wpdb->postmeta." rsvpdates ON rsvpdates.post_id = $wpdb->posts.ID AND rsvpdates.meta_key='_rsvp_dates'";
}

function rsvpmaker_groupby($groupby) {
  global $wpdb;
  return " $wpdb->posts.ID ";
}

function rsvpmaker_distinct($distinct){
  return 'DISTINCT';
}

function rsvpmaker_where($where) {

global $startday;
global $datelimit;

$where .= " AND rsvpdates.meta_key='_rsvp_dates' ";

if(isset($_REQUEST["cm"]))
	{
	$date = rsvp_url_date_query();
	$where .= " AND rsvpdates.meta_value >= '".$date."' ";
	if(!empty($datelimit))
	$where .= "AND rsvpdates.meta_value < DATE_ADD('".$date."',INTERVAL $datelimit) ";
	return $where;
	}
elseif(isset($startday) && $startday)
	{
		$t = rsvpmaker_strtotime($startday);
		$d = rsvpmaker_date('Y-m-d',$t);
		 $where .= " AND rsvpdates.meta_value > '$d' ";
		 if(!empty($datelimit))
		 	$where .= " AND rsvpdates.meta_value < DATE_ADD('$d',INTERVAL $datelimit) ";
		 return $where;
	}
elseif(!empty($startday))
	{
		$t = rsvpmaker_strtotime($startday);
		$d = rsvpmaker_date('Y-m-d',$t);
		$where .= " AND rsvpdates.meta_value > '$d' AND rsvpdates.meta_value > '$d' ";
		if(!empty($datelimit))
			$where .= "AND rsvpdates.meta_value < DATE_ADD('$d',INTERVAL $datelimit) ";
		return $where;
	}
elseif(isset($_GET["startdate"]))
	{
		$d = $_GET["startdate"];
		$where .= " AND rsvpdates.meta_value > '$d' ";
		if(!empty($datelimit))
			$where .=  " AND rsvpdates.meta_value < DATE_ADD('$d',INTERVAL $datelimit) ";		
		return $where;
	}
else
	{
		$where .= " AND rsvpdates.meta_value > CURDATE( ) ";
		if(!empty($datelimit))
			$where .=  " AND rsvpdates.meta_value < DATE_ADD(CURDATE( ),INTERVAL $datelimit) ";		
		return $where;
	}
}

function rsvpmaker_orderby($orderby) {
  return " rsvpdates.meta_value";
}

// if listing past dates
function rsvpmaker_where_past($where) {

global $startday;
$where .= " AND rsvpdates.meta_key='_rsvp_dates' ";

if(isset($_REQUEST["cm"]))
   {
	$date = rsvp_url_date_query('past');
	return $where . " AND rsvpdates.meta_value < '".$date."'";
   }
elseif(isset($startday) && $startday)
	{
		$t = rsvpmaker_strtotime($startday);
		$d = rsvpmaker_date('Y-m-d',$t);
		return $where . " AND rsvpdates.meta_value < '$d'";
	}
elseif(isset($_GET["startday"]))
	{
		$t = rsvpmaker_strtotime($_GET["startday"]);
		$d = date('Y-m-d',$t);
		return $where . " AND rsvpdates.meta_value < '$d'";
	}
else
	return $where . " AND rsvpdates.meta_value < CURDATE( )";

}

function rsvpmaker_orderby_past($orderby) {
  return " rsvpdates.meta_value DESC";
}

function rsvpmaker_upcoming ($atts = array())
{
$no_events = (isset($atts["no_events"])) ? $atts["no_events"] : 'No events currently listed.';

if(isset($atts["calendar"]) && ($atts["calendar"] == 2) )
	return rsvpmaker_calendar($atts);
global $post;

if(!empty($post->post_type) && ($post->post_type == 'rsvpmaker'))
{
	// no infinite loops, please
	return 'The events listing cannot be displayed inside an individual event';
}

$post_backup = $post;
global $wp_query;
global $wpdb;
global $showbutton;
global $startday;
global $rsvp_options;
global $datelimit;
global $last_time;
$last_time = time();
$listings = '';
$showbutton = true;

$backup = $wp_query;

if(isset($_GET["debug_query"]))
	add_filter('posts_request','rsvpmaker_examine_query');

if(isset($atts["startday"]))
	$startday = $atts["startday"];

$limit = isset($atts["limit"]) ? $atts["limit"] : 10;
if(isset($atts["posts_per_page"]))
	$limit = $atts["posts_per_page"];
if(isset($atts["days"]))
		$datelimit = $atts["days"].' DAY';
else
		$datelimit = '180 DAY';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

add_filter('posts_join', 'rsvpmaker_join' );
add_filter('posts_groupby', 'rsvpmaker_groupby' );
add_filter('posts_distinct', 'rsvpmaker_distinct' );
add_filter('posts_fields', 'rsvpmaker_select' );

if(isset($atts["past"]) && $atts["past"])
	{
	add_filter('posts_where', 'rsvpmaker_where_past' );
	add_filter('posts_orderby', 'rsvpmaker_orderby_past' );
	}
else
	{
	add_filter('posts_where', 'rsvpmaker_where' );
	add_filter('posts_orderby', 'rsvpmaker_orderby' );
	if($paged == 1)
		cache_rsvp_dates($limit + 20);
	}

$querystring = "post_type=rsvpmaker&post_status=publish&paged=$paged";

if(!empty($atts["author"]))
	$querystring .= "&author=".$atts["author"];
if(isset($atts["one"]) && !empty($atts["one"]))
	{
	$querystring .= "&posts_per_page=1";
	if(is_numeric($atts["one"]))
		$querystring .= '&p='.$atts["one"];
	elseif($atts["one"] != 'next')
		$querystring .= '&name='.$atts["one"];
	}
if(isset($atts["type"]))
	$querystring .= "&rsvpmaker-type=".$atts["type"];
if($limit)
	$querystring .= "&posts_per_page=".$limit;
if(isset($atts["add_to_query"]))
	{
		if(!strpos($atts["add_to_query"],'&'))
			$atts["add_to_query"] = '&'.$atts["add_to_query"];
		$querystring .= $atts["add_to_query"];
	}

$wpdb->show_errors();

$wp_query = new WP_Query($querystring);
// clean up so this doesn't interfere with other operations
remove_filter('posts_join', 'rsvpmaker_join' );
remove_filter('posts_groupby', 'rsvpmaker_groupby' );
remove_filter('posts_distinct', 'rsvpmaker_distinct' );
remove_filter('posts_fields', 'rsvpmaker_select' );

if(isset($atts["past"]) && $atts["past"])
	{
	remove_filter('posts_where', 'rsvpmaker_where_past' );
	remove_filter('posts_orderby', 'rsvpmaker_orderby_past' );
	}
remove_filter('posts_where', 'rsvpmaker_where' );
remove_filter('posts_orderby', 'rsvpmaker_orderby' );

ob_start();

if(isset($atts["demo"]))
	{
		$demo = "<div><strong>Shortcode:</strong></div>\n<code>[rsvpmaker_upcoming";
		if(is_array($atts))
		foreach($atts as $name => $value)
			{
			if($name == "demo")
				continue;
			$demo .= ' '.$name.'="'.$value.'"';
			}
		$demo .= "]</code>\n";
		$demo .= "<div><strong>Output:</strong></div>\n";
		echo $demo;
	}

echo '<div class="rsvpmaker_upcoming">';
	
if ( have_posts() ) {
global $events_displayed;
while ( have_posts() ) : the_post();
if(!empty($atts['exclude_type']))
{
	$termscheck = array();
	$terms = get_the_terms($post->ID,'rsvpmaker-type');

	if($terms)
	foreach($terms as $term)
	{
		$termscheck[] = $term->slug;
	}
	if(in_array($atts['exclude_type'],$termscheck))
		continue;
}
$events_displayed[] = $post->ID;
?>

<div id="rsvpmaker-<?php the_ID();?>" <?php post_class();?> itemscope itemtype="http://schema.org/Event" >  
<h1 class="rsvpmaker-entry-title"><a href="<?php the_permalink(); ?>"  itemprop="url"><span itemprop="name"><?php the_title(); ?></span></a></h1>
<div class="rsvpmaker-entry-content">

<?php the_content(); ?>

</div><!-- .entry-content -->

<?php

if(!isset($atts["hideauthor"]) || !$atts["hideauthor"])
{
$authorlink = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
	get_author_posts_url( get_the_author_meta( 'ID' ) ),
	/* translators: placeholder = author name */
	sprintf( esc_attr__( 'View all posts by %s', 'rsvpmaker' ), get_the_author() ),
	get_the_author());
?>
<div class="event_author"><?php _e('Posted by','rsvpmaker'); echo " $authorlink on ";?><span class="rsvpupdated" datetime="<?php the_modified_date('c');?>"><?php the_modified_date(); ?></span></div>
<?php 
}
?>
</div>
<?php
if(current_user_can('edit_post',$post->ID) && !is_email_context())
	{
		echo '<p><a href="'.admin_url('post.php?action=edit&post='.$post->ID).'">Edit</a></p>';
	}
endwhile;
?>
<p><?php
if(isset($atts['one']) && $atts['one'])
	echo get_next_events_link(__('More Events','rsvpmaker'));
} 
else
	echo get_next_events_link(__('More Events','rsvpmaker'),$no_events);
echo '</div><!-- end rsvpmaker_upcoming -->';

$wp_query = $backup;
wp_reset_postdata();
$post = $post_backup;
if(	( isset($atts["calendar"]) && $atts["calendar"]) || (isset($atts["format"]) && ($atts["format"] == "calendar") ) )
	if(!(isset($atts['one']) && $atts['one']))
		$listings = rsvpmaker_calendar($atts);

$listings .= ob_get_clean();
if(is_email_context())
	{
		$listings = str_replace('>Edit<','><',$listings); //todo preg replace
		$listings = str_replace('><a','> <a',$listings); //todo preg replace
	}
return $listings;
}

//get all of the dates for the month
function rsvpmaker_calendar_where($where) {

global $startday;

if(isset($_REQUEST["cm"]))
	$d = "'".rsvp_url_date_query()."'";
elseif(isset($startday) && $startday)
	{
		$t = rsvpmaker_strtotime($startday);
		$d = "'".rsvpmaker_date('Y-m-d',$t)."'";
	}
elseif(isset($_GET["startday"]))
	{
		$t = rsvpmaker_strtotime($_GET["startday"]);
		$d = "'".rsvpmaker_date('Y-m-d',$t)."'";
	}
else
		$d = "'".rsvpmaker_date('Y-m')."-01'";
	//$d = ' CURDATE() ';
	return $where . " AND meta_value > ".$d.' AND meta_value < DATE_ADD('.$d.', INTERVAL 5 WEEK) ';
}

function rsvpmaker_calendar_clear($g) {
return '';
}

function rsvpmaker_item_class($post_id,$post_title) {
$tp = preg_split('/[^A-Za-z]{1,5}/',$post_title);
$tp = array_slice($tp,0,4);
$class = implode('_',$tp);

$tax_terms = wp_get_post_terms($post_id, 'rsvpmaker-type');
if(is_array($tax_terms))
	{
		foreach ($tax_terms as $tax_term) {
			$class .= ' '.preg_replace('/[^A-Za-z]{1,5}/','_',$tax_term->name);
		}
	}

return $class;
}

function rsvpmaker_calendar($atts = array()) 
{
if(is_admin() || wp_is_json_request())
	return;
global $post;
$post_backup = $post;
global $wp_query;
global $wpdb;
global $showbutton;
global $startday;
global $rsvp_options;
$date_format = (isset($atts["date_format"])) ? $atts["date_format"] : $rsvp_options["short_date"];
if(isset($atts["startday"]))
	$startday = $atts["startday"];
$self = $req_uri = get_permalink();
$req_uri .= (strpos($req_uri,'?') ) ? '&' : '?';

$showbutton = true;

$backup = $wp_query;

//removing groupby, which interferes with display of multi-day events
add_filter('posts_join', 'rsvpmaker_join' );
add_filter('posts_where', 'rsvpmaker_calendar_where',99 );
add_filter('posts_orderby', 'rsvpmaker_orderby' );
add_filter('posts_groupby', 'rsvpmaker_calendar_clear' );
add_filter('posts_distinct', 'rsvpmaker_calendar_clear' );
add_filter('posts_fields', 'rsvpmaker_select' );

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$querystring = "post_type=rsvpmaker&post_status=publish&posts_per_page=-1&paged=$paged";

if(isset($atts["type"]))
	$querystring .= "&rsvpmaker-type=".$atts["type"];
if(isset($atts["add_to_query"]))
	{
		if(!strpos($atts["add_to_query"],'&'))
			$atts["add_to_query"] = '&'.$atts["add_to_query"];
		$querystring .= $atts["add_to_query"];
	}

$wpdb->show_errors();

$wp_query = new WP_Query($querystring);

// clean up so this doesn't interfere with other operations
remove_filter('posts_join', 'rsvpmaker_join' );
remove_filter('posts_where', 'rsvpmaker_calendar_where',99 );
remove_filter('posts_orderby', 'rsvpmaker_orderby' );
remove_filter('posts_groupby', 'rsvpmaker_calendar_clear' );
remove_filter('posts_distinct', 'rsvpmaker_calendar_clear' );
remove_filter('posts_fields', 'rsvpmaker_select' );
$eventarray = array();
if ( have_posts() ) {
while ( have_posts() ) : the_post();

if(!empty($atts['exclude_type']))
{
	$termscheck = array();
	$terms = get_the_terms($post->ID,'rsvpmaker-type');

	if($terms)
	foreach($terms as $term)
	{
		$termscheck[] = $term->slug;
	}
	if(in_array($atts['exclude_type'],$termscheck))
		continue;
}

//calendar entry
	if(empty($post->post_title))
		$post->post_title = __('Title left blank','rsvpmaker');
	
	$t = rsvpmaker_strtotime($post->datetime);
	$duration_type = get_post_meta($post->ID,'_firsttime', true);
	if(empty($duration_type))
		$duration_type = get_post_meta($post->ID,'_'.$post->datetime, true);
	$end = get_post_meta($post->ID,'_endfirsttime', true);
	if(empty($end))
		$end = get_post_meta($post->ID,'_end'.$post->datetime, true);
	$time = ($duration_type == 'allday') ? '' : '<br />&nbsp;'.rsvpmaker_strftime($rsvp_options["time_format"],$t);
	if(($duration_type == 'set') && !empty($end) )
		{
		$time .= '-'.rsvpmaker_strftime($rsvp_options["time_format"],rsvpmaker_strtotime($end));
		}
	if(isset($_GET["debug"]))
		{
			$msg = sprintf('%s %s %s',$post->post_title,$post->datetime,$post->meta_id);
		}	
	$key = rsvpmaker_date('Y-m-d',$t);
	$eventarray[$key] = (isset($eventarray[$key])) ? $eventarray[$key] . '<div><a class="calendar_item '.rsvpmaker_item_class($post->ID,$post->post_title).'" href="'.get_post_permalink($post->ID).'" title="'.htmlentities($post->post_title).'">'.$post->post_title.$time."</a></div>\n" : '<div><a class="calendar_item '.rsvpmaker_item_class($post->ID,$post->post_title).'" href="'.get_post_permalink($post->ID).'" title="'.htmlentities($post->post_title).'">'.$post->post_title.$time."</a></div>\n";
endwhile;
}

$wp_query = $backup;
wp_reset_postdata();

// calendar display routine
$nav = isset($atts["nav"]) ? $atts["nav"] : 'bottom';

$months = array('','January','February','March','April','May','June','July','August','September','October','November','December');

$cm = (isset($_REQUEST["cm"])) ? (int) $_REQUEST["cm"] : (int) rsvpmaker_date("m");
$cy = (isset($_REQUEST["cy"])) ? (int) $_REQUEST["cy"] : (int) rsvpmaker_date("Y");
$monthname = $months[$cm];
$date = $bom = rsvpmaker_strtotime('first day of '.$monthname.' '.$cy);
$eom = rsvpmaker_strtotime('last day of '.$monthname.' '.$cy);
$nowdate = rsvpmaker_date("Y-m-d", $bom );
$yearmonth = rsvpmaker_date("Y-m-", $bom );

$monthafter = $eom + (DAY_IN_SECONDS * 32);

$eonext = rsvpmaker_date("Y-m-d",'month after '.$monthname.' '.$cy);

// Link to previous month (but do not link to too early dates)
   $prev_link = '<a href="' . $req_uri . rsvpmaker_strftime('cm=%m&cy=%Y">%B %Y</a>', $bom - 90000);

// Link to next month (but do not link to too early dates)
   $next_link = '<a href="' . $req_uri . rsvpmaker_strftime('cm=%m&cy=%Y">%B %Y</a>', $eom + 90000);

$current_link = ' &nbsp;&lt;&nbsp; <a href="' . $req_uri . rsvpmaker_strftime('cm=%m&cy=%Y">%B %Y',$bom).'</a> &nbsp;&gt;&nbsp; ';

	$page_id = (isset($_GET["page_id"])) ? '<input type="hidden" name="page_id" value="'. (int) $_GET["page_id"].'" />' : '';

// $Id: cal.php,v 1.47 2003/12/31 13:04:27 goba Exp $

// Begin the calendar table
$content = '';
if(($nav == 'top') || ($nav == 'both')) // either it's top or both
$content .= '<div class="rsvpmaker_nav"><span class="navprev">'. $prev_link. '</span> '.$current_link.' <span class="navnext">'.
     '' . $next_link . "</span></div>";

$content .= '

<table id="cpcalendar" width="100%" cellspacing="0" cellpadding="3"><caption>'.rsvpmaker_strftime('<b>%B %Y</b>', $bom)."</caption>\n".'<tr>'."\n";

if(isset($atts["weekstart"]) && ($atts["weekstart"] == 'Monday'))
{
$content .= '<thead>
<tr>
<th>'.__('Monday','rsvpmaker').'</th> 
<th>'.__('Tuesday','rsvpmaker').'</th> 
<th>'.__('Wednesday','rsvpmaker').'</th> 
<th>'.__('Thursday','rsvpmaker').'</th> 
<th>'.__('Friday','rsvpmaker').'</th> 
<th>'.__('Saturday','rsvpmaker').'</th> 
<th>'.__('Sunday','rsvpmaker').'</th> 
</tr>
</thead>
';
$weekstart = 1;
}
else
{
$content .= '<thead>
<tr>
<th>'.__('Sunday','rsvpmaker').'</th> 
<th>'.__('Monday','rsvpmaker').'</th> 
<th>'.__('Tuesday','rsvpmaker').'</th> 
<th>'.__('Wednesday','rsvpmaker').'</th> 
<th>'.__('Thursday','rsvpmaker').'</th> 
<th>'.__('Friday','rsvpmaker').'</th> 
<th>'.__('Saturday','rsvpmaker').'</th> 
</tr>
</thead>
';
$weekstart = 0;
}

$content .= "\n<tbody><tr id=\"rsvprow1\">\n";
$rowcount = 1;
// Generate the requisite number of blank days to get things started
for ($days = $i = rsvpmaker_date("w",$bom); $i > $weekstart; $i--) {
   $content .= '<td class="notaday">&nbsp;</td>';
}
$days = $days - $weekstart;// adjust if first day not sunday

if(isset($_GET['debugpast']))
	$todaydate = rsvpmaker_date('Y-m-d',rsvpmaker_strtotime('+2 weeks'));
else
$todaydate = rsvpmaker_date('Y-m-d');
// Print out all the days in this month
for ($i = 1; $i <= rsvpmaker_date("t",$bom); $i++) {
  
   // Print out day number and all events for the day
	$thisdate = $yearmonth.sprintf("%02d",$i);
	$class = ($thisdate == $todaydate ) ? 'today day' : 'day';
	if($thisdate < $todaydate)
		$class .= ' past';
	if($thisdate > $todaydate)
		$class .= ' future';
	if(isset($_GET['debug']))
	{
		rsvpmaker_debug_log($thisdate,'calendar thisdate ');
	}
	$content .= '<td valign="top" class="'.$class.'">';
   if(!empty($eventarray[$thisdate]) )
   {
   $content .= $i;
   $content .= $eventarray[$thisdate];
   if(isset($_GET['debug']))
   {
	   rsvpmaker_debug_log($eventarray[$thisdate],'calendar eventarray '.$thisdate);
   }
  $t = rsvpmaker_strtotime($thisdate);
   }
   else
   	$content .= '<div class="'.$class.'">' . $i . "</div><p>&nbsp;</p>";
   $content .= '</td>';
  
   // Break HTML table row if at end of week
   if (++$days % 7 == 0)
   	{
		$content .= "</tr>\n";
		$rowcount++;
		$content .= '<tr id="rsvprow'.$rowcount.'">';
	}
}

// Generate the requisite number of blank days to wrap things up
for (; $days % 7; $days++) {
   $content .= '<td class="notaday">&nbsp;</td>';
}

$content .= "\n</tr>";
$content .= "<tbody>\n";

// End HTML table of events
$content .= "\n</table>\n";

if($nav != 'top') // either it's bottom or both
$content .= '<div class="rsvpmaker_nav"><span class="navprev">'. $prev_link. '</span> '.$current_link.' <span class="navnext">'.
     '' . $next_link . "</span></div>";

//jump form
$content .= sprintf('<form id="rsvpmaker_jumpform" action="%s" method="get"> %s <input type="text" name="cm" value="%s" size="4" class="jump" />/<input type="text" name="cy" value="%s" size="4" class="jump" /><button>%s</button>%s</form>', $self,__('Month/Year','rsvpmaker'),rsvpmaker_date('m',$monthafter),rsvpmaker_date('Y',$monthafter),__('Go','rsvpmaker'),$page_id);

$calj = "

    $( '.calendar_item' ).tooltip({
        show: null, // show immediately 
        position: { my: \"right top\", at: \"left top\" },
        content: $(this).html(),
        hide: { effect: \"\" }, //fadeOut
        close: function(event, ui){
            ui.tooltip.hover(
                function () {
                    $(this).stop(true).fadeTo(400, 1); 
                },
                function () {
                    $(this).fadeOut(\"400\", function(){
                        $(this).remove(); 
                    })
                }
            );
        }  
    });

";

if(!empty($calj))
	{
$content .= '<script>
jQuery(document).ready(function($) {
'.$calj.'

});
</script>
';
	}
$post = $post_backup;

return $content;
}

function rsvpmaker_template_fields($select) {
  $select .= ", tmeta.meta_value as sked";
  return $select;
}

function rsvpmaker_template_join($join) {
  global $wpdb;
  return $join." JOIN $wpdb->postmeta tmeta ON tmeta.post_id = $wpdb->posts.ID ";
}

function rsvpmaker_template_where($where) {

	return " AND (BINARY tmeta.`meta_key` REGEXP '_sked_[A-Z].+' AND tmeta.meta_value)";

}

function rsvpmaker_template_orderby($orderby) {
  return " post_title ";
}

function rsvpmaker_template_events_where($where) {
global $rsvptemplate;
	if(isset($_GET["t"]))
		$rsvptemplate = (int) $_GET["t"];
	if(!$rsvptemplate)
		return $where;
	return $where . " AND meta_key='_meet_recur' AND meta_value=$rsvptemplate";
}

//utility function, template tag
function is_rsvpmaker() {
global $post;
if($post->post_type == 'rsvpmaker')
	return true;
else
	return false;
}

function rsvpmaker_timed ($atts = array(), $content = '') {
if(!empty($atts['start']))
	{
		$start = rsvpmaker_strtotime($atts['start']);
		$now = current_time('timestamp');
		if($now < $start)
			{
				if(isset($_GET["debug"]))
					return sprintf('<p>start %s / now %s</p>',date('r',$start), date('r',$now));
				elseif(isset($atts["too_early"]))
					return '<p>'.esc_html($atts["too_early"]).'</p>';
				else
					return '';
			}
	}
if(!empty($atts['end']))
	{
		$end = rsvpmaker_strtotime($atts['end']);
		$now = current_time('timestamp');
		if($now > $end)
			{
				if(isset($_GET["debug"]))
					return sprintf('<p>end %s / now %s</p>',date('r',$end), date('r',$now));
				elseif(isset($atts["too_late"]))
					return '<p>'.esc_html($atts["too_late"]).'</p>';
				else
					return '';
			}
	}

if(!empty($atts["post_id"]))
{
$qs = 'posts_per_page=1&p='. (int) $atts["post_id"];
if($atts["post_type"])
	$qs .= '&post_type='.$atts["post_type"];
$cq = new WP_Query($qs);
if ( $cq->have_posts() ) : $cq->the_post();
ob_start();
global $post;
$post_backup = $post;
?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
<div class="entry-content">
<?php 
the_content();
?>
</div><!-- .entry-content -->
</div>
<?php 
$content = ob_get_clean();	
$post = $post_backup;
endif;
}

// if we clear these two hurdles, return the content
if(!empty($atts["style"]))
	$content = '<div style="'.$atts["style"].'">'.$content.'</div>';
	
return $content;

}

function rsvpmaker_looking_ahead($atts) {
global $last_time;
global $events_displayed;
$listings = '';
$limit = isset($atts["limit"]) ? $atts["limit"] : 10;
if(isset($atts["days"]))
		$datelimit = $atts["days"].' DAY';
else
		$datelimit = '30 DAY';

if(!$last_time)
	return 'last time not found';

$results = get_future_events("meta_value > '".date('Y-m-d',$last_time)."' AND meta_value < DATE_ADD('".date('Y-m-d',$last_time)."',INTERVAL $datelimit)", $limit, ARRAY_A);

if($results)
foreach($results as $row)
	{
	if(in_array($row["postID"], $events_displayed) )
		continue;
		
	$t = rsvpmaker_strtotime($row["datetime"]);
	if(empty($dateline[$row["postID"]]))
		$dateline[$row["postID"]] = '';
	else
		$dateline[$row["postID"]] .= ", ";
	$dateline[$row["postID"]] .= date('M. j',$t);
	$eventlist[$row["postID"]] = $row;
	}

//strpos test used to catch either "headline" or "headlines"
if(isset($eventlist) && is_array($eventlist))
{
foreach($eventlist as $event)
	{
	if(isset($atts["permalinkhack"]))
		$permalink = site_url() ."?p=".$event["postID"];
	else
		$permalink = get_post_permalink($event["postID"]);
	$listings .= sprintf('<li><a href="%s">%s</a> %s</li>'."\n",esc_url_raw($permalink),esc_html($event["post_title"]),esc_html($dateline[$event["postID"]]));
	}

	if(!empty($rsvp_options["eventpage"]))
		$listings .= '<li><a href="'.esc_url($rsvp_options["eventpage"]).'">'.__("Go to Events Page",'rsvpmaker')."</a></li>";

	if(isset($atts["title"]))
		$listings = "<p><strong>".esc_html($atts["title"])."</strong></p>\n<ul id=\"eventheadlines\">\n$listings</ul>\n";
	else
		$listings = "<ul id=\"eventheadlines\">\n$listings</ul>\n";
}//end if $eventlist
return $listings;
}

function get_adjacent_rsvp_join($join) {
global $post;
if($post->post_type != 'rsvpmaker')
	return $join;
global $wpdb;
return $join .' JOIN '.$wpdb->postmeta.' ON p.ID='.$wpdb->postmeta.".post_id AND meta_key='_rsvp_dates' ";
}

add_filter('get_previous_post_join','get_adjacent_rsvp_join');
add_filter('get_next_post_join','get_adjacent_rsvp_join');

function get_adjacent_rsvp_sort($sort) {
global $post;
if($post->post_type != 'rsvpmaker')
	return $sort;
global $wpdb;
$sort = str_replace('p.post_date',$wpdb->postmeta.'.meta_value',$sort);

return $sort;
}
add_filter('get_previous_post_sort','get_adjacent_rsvp_sort');
add_filter('get_next_post_sort','get_adjacent_rsvp_sort');


function get_adjacent_rsvp_where($where) {
global $post;
if($post->post_type != 'rsvpmaker')
	return $where;
global $wpdb;
$op = strpos($where, '>') ? '>' : '<';
$current_event_date = $wpdb->get_var("select meta_value from ".$wpdb->postmeta." WHERE meta_key='_rsvp_dates' AND post_id=".$post->ID);
//split and modify
$wparts = explode('p.post_type',$where);//

$where = "WHERE ".$wpdb->postmeta.".meta_value $op '$current_event_date' AND p.ID != $post->ID AND p.post_type".$wparts[1];
return $where;
}

add_filter('get_previous_post_where','get_adjacent_rsvp_where');
add_filter('get_next_post_where','get_adjacent_rsvp_where');

// based on https://gist.github.com/hugowetterberg/81747
function rsvp_ical_split($preamble, $value) {
  $value = trim($value);
  $value = strip_tags($value);
  $value = str_replace("\n", "\\n", $value);
  $value = str_replace("\r", "", $value);
  $value = preg_replace('/\s{2,}/', ' ', $value);
  $preamble_len = strlen($preamble);
  $lines = array();
  while (strlen($value)>(75-$preamble_len)) {
    $space = (75-$preamble_len);
    $mbcc = $space;
    while ($mbcc) {
      $line = mb_substr($value, 0, $mbcc);
      $oct = strlen($line);
      if ($oct > $space) {
        $mbcc -= $oct-$space;
      }
      else {
        $lines[] = $line;
        $preamble_len = 1; // Still take the tab into account
        $value = mb_substr($value, $mbcc);
        break;
      }
    }
  }
  if (!empty($value)) {
    $lines[] = $value;
  }
  return join($lines, "\n\t");
}

function rsvpmaker_to_ical () {

global $post;
global $rsvp_options;
global $wpdb;
if(!isset($post->post_type) || ($post->post_type != 'rsvpmaker') )
	return;
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $post->post_name.'.ics');
$sql = "SELECT *, meta_value as datetime FROM ".$wpdb->postmeta." WHERE meta_key='_rsvp_dates' AND post_id=".$post->ID.' ORDER BY meta_value';
$daterow = $wpdb->get_row($sql);
$end_time = get_post_meta($post->ID,'_end'.$daterow->datetime, true);
if(empty($end_time))
	$ical_end = get_utc_ical ( $daterow->datetime . ' +1 hour' );
else {
	$p = explode(' ',$daterow->datetime);
	$ical_end = get_utc_ical ( $p[0] . ' '.$end_time );
}
$start = get_utc_ical ($daterow->datetime);
$hangout = get_post_meta($post->ID, '_hangout',true);
$url = (!empty($hangout)) ? $hangout : get_permalink($post->ID);

$desc = '';
if(!empty($hangout))
	$desc = "Google Hangout: ".$hangout." ";
$desc .= "Event info: " . get_permalink($post->ID);
$desc = rsvp_ical_split("DESCRIPTION:", $desc);

printf('BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTEND:%s
UID:%s
DTSTAMP:%s
DESCRIPTION:%s
URL;VALUE=URI:%s
SUMMARY:%s
DTSTART:%s
ORGANIZER;CN=%s:MAILTO:%s
END:VEVENT
END:VCALENDAR
',$ical_end,$start.'-'.$post->ID.'@'.$_SERVER['SERVER_NAME'],date('Ymd\THis\Z'), $desc, $url, $post->post_title, $start, get_bloginfo('name'), $rsvp_options["rsvp_to"]);

exit;
}

function rsvpmaker_to_gcal($post,$datetime,$duration) {
return sprintf('http://www.google.com/calendar/event?action=TEMPLATE&text=%s&dates=%s/%s&details=%s&location=&trp=false&sprop=%s&sprop=name:%s',urlencode($post->post_title),get_utc_ical ($datetime),get_utc_ical ($duration), urlencode(get_bloginfo('name') ." ".get_permalink($post->ID)  ),get_permalink($post->ID), urlencode(get_bloginfo('name') ) );
}

function get_utc_ical ($timestamp) {
return gmdate('Ymd\THis\Z', rsvpmaker_strtotime($timestamp));
}

function rsvp_row_to_profile($row) {
if(empty($row["details"]) )
	$profile = array();
else
	$profile = unserialize($row["details"]);
if(is_array($row))
foreach($row as $field => $value)
	{
		if(isset($profile[$field]) || ($field == 'details') )
			continue;
		else
			$profile[$field] = $value;
	}
return $profile;
}

function rsvpmaker_type_dateorder ( $sql ) {
echo $sql;
return $sql;
}

function rsvpmaker_archive_pages ($query) {
	if(is_admin() || wp_is_json_request())
		return;
	if(is_archive() && isset($query->query["post_type"]) && ($query->query["post_type"] == 'rsvpmaker'))
	{
	add_filter('posts_join', 'rsvpmaker_join' );
	add_filter('posts_groupby', 'rsvpmaker_groupby' );
	add_filter('posts_distinct', 'rsvpmaker_distinct' );
	add_filter('posts_fields', 'rsvpmaker_select' );
	add_filter('posts_where', 'rsvpmaker_where' );
	add_filter('posts_orderby', 'rsvpmaker_orderby' );
	if(isset($_GET["debug_query"]))
		add_filter('posts_request','rsvpmaker_examine_query');
	}
	if(is_archive() && !empty($query->query["rsvpmaker-type"]))
	{
	add_filter('posts_join', 'rsvpmaker_join' );
	add_filter('posts_groupby', 'rsvpmaker_groupby' );
	add_filter('posts_distinct', 'rsvpmaker_distinct' );
	add_filter('posts_fields', 'rsvpmaker_select' );
	add_filter('posts_where', 'rsvpmaker_where' );
	add_filter('posts_orderby', 'rsvpmaker_orderby' );
	if(isset($_GET["debug_query"]))
		add_filter('posts_request','rsvpmaker_examine_query');
	}
}

function get_rsvpmaker_archive_link($page = 1) {
$link = get_post_type_archive_link('rsvpmaker');
$link .= (strpos($link,'?')) ? '&paged='.$page : '?paged='.$page;
return $link;
}

function rsvpmaker_examine_query ($request) {
$log = var_export($request,true);
mail('david@carrcommunications.com','query test',$log);
return $request;
}

function rsvpmaker_facebook_meta () {
global $post;
global $rsvp_options;
if(!isset($post->post_type) || ($post->post_type != 'rsvpmaker'))
	return; // don't mess with other post types

$tstring = get_rsvp_date($post->ID);
if(empty($tstring)) // might be a replay landing page or other non-calendar item
	return;
$ts = rsvpmaker_strtotime($tstring);
if(!strpos($rsvp_options["time_format"],'%Z') && get_post_meta($post->ID,'_add_timezone',true) )
	{
	$rsvp_options["time_format"] .= ' %Z';
	}
$date = rsvpmaker_strftime($rsvp_options["short_date"].' '.$rsvp_options["time_format"],$ts);
$title = get_the_title($post->ID);
$titlestr = $title . ' - '. $date. ' - '.get_bloginfo('name');
printf('<meta property="og:title" content="%s" /><meta property="twitter:title" content="%s" />',$titlestr,$titlestr);
}

function ylchat ($atts) {
global $post;

preg_match('/(https:\/\/www.youtube.com\/watch\?v=|https:\/\/youtu.be\/)([^\s]+)/',$post->post_content,$matches);

if(!isset($matches[2]))
	return;

$url = sprintf('https://www.youtube.com/live_chat?v=%s&amp;embed_domain=%s',$matches[2],$_SERVER['SERVER_NAME']);

$login_url = 'https://accounts.google.com/ServiceLogin?uilel=3&service=youtube&hl=en&continue=https%3A%2F%2Fwww.youtube.com%2Fsignin%3Ffeature%3Dcomments%26next%3D%252Flive_chat%253Fis_popout%253D1%2526v%253D'.$matches[2].'%26hl%3Den%26action_handle_signin%3Dtrue%26app%3Ddesktop&passive=true';

$test = file_get_contents($url);
if(strpos($test,'live-chat-unavailable'))
	return;

$note = (isset($atts["note"])) ? '<p>'.esc_html($atts["note"]).'</p>' : '';
$width = (isset($atts["width"])) ? esc_attr($atts["width"]) : '100%';
$height = (isset($atts["height"])) ? esc_attr($atts["height"]) : '200';
if(isset($_GET["height"]))
	$height = (int) $_GET["height"];

return $note . sprintf('<iframe src="%s" width="%s" height="%s"></iframe>',esc_url_raw($url),esc_attr($width),esc_attr($height)) . sprintf('<p>%s <a href="%s" target="_blank">%s</a>. %s</p>',__('If the chat prompt does not appear below,','rsvpmaker'), $login_url, __('please login to your YouTube/Google account','rsvpmaker'), __('Then refresh this window.','rsvpmaker'));
}

function rsvpmaker_next ($atts = array())
{
if(!empty($atts['rsvp_on']))
	$atts['post_id'] = 'nextrsvp';
else
	$atts["post_id"] = 'next';
return rsvpmaker_one($atts);
}

function rsvpmaker_one ($atts = array())
{
global $post;
global $wp_query;
global $wpdb;
global $showbutton;
global $startday;
global $rsvp_options;
$showbutton = (isset($atts["showbutton"])) ? $atts["showbutton"] : 0;
$content = '';

email_content_minfilters();

if(empty($atts['type'])) {
//event type lookup is more complicated, but here are simple cases
	if(isset($atts["one"]))
		$atts['post_id'] = $atts["one"];
	if(empty($atts['post_id']))
		$atts['post_id'] = 'next';

	if($atts['post_id'] == 'nextrsvp') 
	{
		$event = get_next_rsvp_on();
		if(empty($event))
			return;
		$atts['post_id'] = $event->ID;
	}
	elseif($atts['post_id'] == 'next')
	{
		if(isset($atts['one_format']) && ( ($atts['one_format'] == 'button_only') || ($atts['one_format'] == 'form') || !empty($atts["showbutton"]) ) )
			$event = get_next_rsvp_on();
		else
			$event = get_next_rsvpmaker();
		if(empty($event))
		{
		return;
		}
		$atts['post_id']=$event->ID;
	}

	if(isset($atts['one_format'])) {
		if($atts['one_format'] == 'button_only')
			{
			return get_rsvp_link($atts['post_id']);
			}
		if($atts['one_format'] == 'form')
		{
			return rsvpmaker_form($atts);
		}
	}
}

if(isset($atts["one_format"]) && (($atts["one_format"] == 'button') || ($atts["one_format"] == 'button_only')) )
	$showbutton = 1;

$post_id = $atts["post_id"];	

$backup_post = $post;
$backup_query = $wp_query;

if($atts["post_id"] == 'next')
{
add_filter('posts_join', 'rsvpmaker_join' );
add_filter('posts_groupby', 'rsvpmaker_groupby' );
add_filter('posts_distinct', 'rsvpmaker_distinct' );
add_filter('posts_fields', 'rsvpmaker_select' );
add_filter('posts_where', 'rsvpmaker_where' );
add_filter('posts_orderby', 'rsvpmaker_orderby' );
$querystring = "post_type=rsvpmaker&post_status=publish&posts_per_page=1";
if(isset($atts["type"]))
	$querystring .= "&rsvpmaker-type=".$atts["type"];
if(isset($atts["add_to_query"]))
	{
		if(!strpos($atts["add_to_query"],'&'))
			$atts["add_to_query"] = '&'.$atts["add_to_query"];
		$querystring .= $atts["add_to_query"];
	}
$wp_query = new WP_Query($querystring);

remove_filter('posts_join', 'rsvpmaker_join' );
remove_filter('posts_groupby', 'rsvpmaker_groupby' );
remove_filter('posts_distinct', 'rsvpmaker_distinct' );
remove_filter('posts_fields', 'rsvpmaker_select' );
remove_filter('posts_where', 'rsvpmaker_where' );
remove_filter('posts_orderby', 'rsvpmaker_orderby' );
}
else
{
$querystring = "post_type=rsvpmaker&post_status=publish&posts_per_page=1&p=". (int) $atts["post_id"];
$wp_query = new WP_Query($querystring);	
}
$wp_query->is_single = true;
	
if ( have_posts() ) {
global $events_displayed;
the_post();
$atts["post_id"] = $post_id = $post->ID;
if(!empty($atts["hide_past"]))
{
$offset = $atts["hide_past"];
if(!is_rsvpmaker_future($post_id, $offset))
	return;
}
//rsvpmaker_debug_log($atts,'rsvpmaker_one atts');
if(empty($atts["one_format"]) || ($atts["one_format"] == 'button'))
{//one post loop
//rsvpmaker_debug_log('loop for event plus button','rsvpmaker_one atts');
ob_start();
?>
<div class="rsvpmaker_embedded">
<div id="rsvpmaker-<?php the_ID();?>" <?php post_class();?> itemscope itemtype="http://schema.org/Event" >  

<?php 
if(!isset($atts["hide_title"]) || !$atts["hide_title"])
{
?>
<h2 class="rsvpmaker-entry-title" itemprop="url"><span itemprop="name"><?php the_title(); ?></span></h2>
<?php
}
?>
<div class="rsvpmaker-entry-content">

<?php
	
	if(isset($atts['one_format']) && ($atts["one_format"] == 'button_only')) {
		$content = embed_dateblock($atts);
	if(is_rsvpmaker_future($post_id)) 
		{
		$rsvp = get_rsvp_link($post_id);
		}
	else
		{
		$rsvp = __('Event date is past','rsvpmaker');
		}
	echo $content.'<div style="margin-top: 10px;">'.$rsvp.'</div>';
	}
	else
		the_content(); ?>
</div><!-- .entry-content -->

<?php
if(is_admin() )
	{
		echo '<p><a href="'.admin_url('post.php?action=edit&post='.$post->ID).'">Edit</a></p>';
	}
echo '</div></div><!-- end rsvpmaker_embedded -->';
$content = ob_get_clean();	
}
else
{
	if($atts["one_format"] == 'button_only')
	{
	rsvpmaker_debug_log('one_format att = button_only','rsvpmaker_one atts');
	if(is_rsvpmaker_future($post_id)) 
		{
		$content = get_rsvp_link($post_id);
		}
	else
		{
		$content = __('Event date is past','rsvpmaker');
		}
	}
	elseif($atts["one_format"] == 'embed_dateblock') {
		$content = embed_dateblock($atts);
	}
	elseif($atts["one_format"] == 'form') {
		if(is_rsvpmaker_future($post_id)) 
			$content = rsvpmaker_form($atts);
		else
			$content = __('Event date is past','rsvpmaker');
	}
	elseif($atts["one_format"] == 'compact') {
		$content = rsvpmaker_compact($atts);
	}
	elseif($atts["one_format"] == 'compact_form') {
		$atts["show_form"] = 1;
		$content = rsvpmaker_compact($atts);
	}
}
}

$wp_query = $backup_query;
$post = $backup_post;
wp_reset_postdata();

if(!empty($atts["style"]))
	$content = '<div style="'.$atts["style"].'">'.$content.'</div>';
if(strpos($content,'<!--') && function_exists('do_blocks'))
	$content = do_blocks($content);
return $content; //.$filterslist;
}

function rsvpmaker_compact ($atts = array())
{
global $post;
global $wp_query;
global $wpdb;
global $showbutton;
global $startday;
global $rsvp_options;
if(isset($atts["post_id"]))
{
	$post_id = (int) $atts["post_id"];	
}
elseif(isset($atts["one"]))
	$post_id = (int) $atts["one"];
else
	return;
	
$backup_post = $post;
$backup_query = $wp_query;

$querystring = "post_type=rsvpmaker&post_status=publish&posts_per_page=1&p=".$post_id;

$wp_query = new WP_Query($querystring);
$wp_query->is_single = false;

global $rsvp_options;
$time_format = $rsvp_options["time_format"];
if(!strpos($time_format,'%Z'))
	{
	if(get_post_meta($post_id,'_add_timezone',true))
		$time_format .= ' %Z';
	}

ob_start();

echo '<div class="rsvpmaker_compact">';

if ( have_posts() ) {
global $events_displayed;
while ( have_posts() ) : the_post();

	$datestamp = get_rsvp_date($post_id);
	$dur = get_post_meta($post_id,'_'.$datestamp, true);
	$t = rsvpmaker_strtotime($datestamp);
	$dateblock = ', '.utf8_encode(rsvpmaker_strftime($rsvp_options["short_date"],$t));
	if($dur != 'allday')
		{
		$dateblock .= rsvpmaker_strftime(', '.$time_format,$t);
		}
	// dchange
	$dateblock = str_replace(':00','',$dateblock);
	
?>
<div id="rsvpmaker-<?php the_ID();?>" <?php post_class();?> itemscope itemtype="http://schema.org/Event" >  
<p class="rsvpmaker-compact-title" itemprop="url"><span itemprop="name"><?php the_title(); echo $dateblock; ?></span></p>
<?php
if(is_rsvpmaker_future($post_id))
{
	if(isset($atts["show_form"]))
		echo rsvpmaker_form($atts);
	else{
	echo get_rsvp_link($post_id);
	}
}
	else
		_e('Event date is past','rsvpmaker');
endwhile;

}
echo '</div></div><!-- end rsvpmaker_upcoming -->';

$wp_query = $backup_query;
$post = $backup_post;
wp_reset_postdata();

return ob_get_clean();
}

function rsvpmaker_replay_form($event_id) {
	
if(is_rsvpmaker_future($event_id, 1)) 
	{
		$permalink = get_permalink($event_id);
		return sprintf('<a href="%s">%s</a>',$permalink,__('Please register'));
	}
; // if start time in the future (or within one hour)
	
global $post;
$permalink = get_permalink($post->ID);
$form = get_post_meta($event_id,'_rsvp_form',true);
$captcha = get_post_meta($event_id,'_rsvp_captcha',true);
$rsvp_instructions = get_post_meta($event_id,'_rsvp_instructions',true);
ob_start();
if(isset($_GET["err"]))
{
	echo '<div style="padding: 10px; margin: 10px; width: 100%; border: medium solid red;">'.htmlentities($_GET["err"]).'</div>';
}
;?>

<form id="rsvpform" action="<?php echo $permalink;?>" method="post">
<input type="hidden" name="replay_rsvp" value="<?php echo $permalink;?>" />
<h3 id="rsvpnow"><?php echo __('Please Register','rsvpmaker');?></h3> 

  <?php if($rsvp_instructions) echo '<p>'.nl2br($rsvp_instructions).'</p>';?>

<?php 
basic_form($form);

if($captcha)
{
?>
<p><img src="<?php echo plugins_url('/captcha/captcha_ttf.php',__FILE__);  ?>" alt="CAPTCHA image">
<br />
<?php _e('Type the hidden security message','rsvpmaker'); ?>:<br />                    
<input maxlength="10" size="10" name="captcha" type="text" />
</p>
<?php
do_action('rsvpmaker_after_captcha');
}
if(function_exists('rsvpmaker_recaptcha_output'))
	rsvpmaker_recaptcha_output();
global $rsvp_required_field;
$rsvp_required_field['email'] = 'email';//at a minimum
echo '<div id="jqerror"></div><input type="hidden" name="required" id="required" value="'.implode(",",$rsvp_required_field).'" />';
?>
        <p>
          <input type="submit" id="rsvpsubmit" name="Submit" value="<?php  _e('Submit','rsvpmaker');?>" /> 
        </p> 
<input type="hidden" name="rsvp_id" id="rsvp_id" value="" /><input type="hidden" id="event" name="event" value="<?php echo $event_id;?>" /><input type="hidden" name="landing_id" value="<?php echo $post->ID;?>" /><?php wp_nonce_field('rsvp_replay','rsvp_replay_nonce'); ?>
</form>	
<?php
return ob_get_clean();
}

function rsvpmaker_archive_loop_end () {
global $wp_query;
global $rsvpwidget;
global $dataloop;
if(!empty($dataloop))
	return;//don't do this for rsvpmaker_upcoming_data
if (!empty($rsvpwidget) || empty($wp_query->query["post_type"]) )
	return;
if(is_archive() && ($wp_query->query["post_type"] == 'rsvpmaker'))
	{
?>
<div class="navigation"><p><?php posts_nav_link(' | ','&laquo; '.__('Previous Events','rsvpmaker'),__('More Events','rsvpmaker').' &raquo;'); ?></p></div>
<?php
	}
}

//keep jetpack from messing up
function rsvpmaker_no_related_posts( $options ) {
    global $post;
	if(($post->post_type == 'rsvpmaker' ) || ($post->post_type == 'rsvpemail' ))
	{
        $options['enabled'] = $options['headline'] = false;
    }
    return $options;
}
add_filter( 'jetpack_relatedposts_filter_options', 'rsvpmaker_no_related_posts' );

function rsvp_report_this_post() {
global $wpdb;
global $rsvp_options;
global $post;
if(empty($post->ID))
	return;

	$eventid = $post->ID;
$o = "<h2>".__("RSVPs",'rsvpmaker')."</h2>\n";
	$sql = "SELECT * FROM ".$wpdb->prefix."rsvpmaker WHERE event=$eventid  ORDER BY yesno DESC, last, first";
	$wpdb->show_errors();
	$results = $wpdb->get_results($sql, ARRAY_A);
	if(empty($results))
		return $o . '<p>'.__('None','rsvpmaker').'</p>';
	ob_start();
	format_rsvp_details($results, false);
	$o .= ob_get_clean();
return $o;
}

function rsvpmaker_hide_menu ($menu)
{
	global $post;
	if(isset($post->post_type) && $post->post_type != 'page')
		return $menu;
	if(isset($post->ID) && get_post_meta($post->ID,'rsvpmaker_hide_menu',true))
		return '';
	return $menu;
}
//if(!is_admin())
add_filter('wp_nav_menu','rsvpmaker_hide_menu');

function rsvplanding_register_meta_boxes() {
    add_meta_box( 'rsvplanding-box-id', __( 'Hide The Menu on This Page', 'rsvpmaker' ), 'rsvplanding_my_display_callback', 'page', 'advanced', 'low' );
}

function rsvplanding_my_display_callback( $post ) {
$on = get_post_meta($post->ID,'rsvpmaker_hide_menu',true);
$checked = ($on) ? ' checked="checked" ' : '';
printf('<input type="checkbox" name="rsvpmaker_hide_menu" value="1" %s> Hide menu (<em>Turn a full-width page template into a landing page</em>)', $checked);
wp_nonce_field( 'rsvpmaker_hide_menu_action', 'rsvpmaker_hide_menu_nonce' );
	// Display code/markup goes here. Don't forget to include nonces!
}

function rsvplanding_save_meta_box( $post_id ) {
	if(isset($_POST['rsvpmaker_hide_menu_nonce']) && wp_verify_nonce( $_POST['rsvpmaker_hide_menu_nonce'], 'rsvpmaker_hide_menu_action' ))
		update_post_meta($post_id,'rsvpmaker_hide_menu',isset($_POST['rsvpmaker_hide_menu']));
}


function clear_rsvp_cookies () {
	if(isset($_GET['clear']))
	{
		if(isset($_COOKIE))
			foreach($_COOKIE as $name => $value)
			{
				if(strpos($name,'svp_for'))
				{
					setcookie($name,0);//set to no value
					echo ' clear '.$name;
				}
			}
	}
}

function sked_to_text ($sked) {
	global $rsvp_options;
	
	$s = '';
		$weeks = (empty($sked["week"])) ? array(0) : $sked["week"];
		$dows = (empty($sked["dayofweek"])) ? array() : $sked["dayofweek"];

		$dayarray = Array(__("Sunday",'rsvpmaker'),__("Monday",'rsvpmaker'),__("Tuesday",'rsvpmaker'),__("Wednesday",'rsvpmaker'),__("Thursday",'rsvpmaker'),__("Friday",'rsvpmaker'),__("Saturday",'rsvpmaker'));
		$weekarray = Array(__("Varies",'rsvpmaker'),__("First",'rsvpmaker'),__("Second",'rsvpmaker'),__("Third",'rsvpmaker'),__("Fourth",'rsvpmaker'),__("Last",'rsvpmaker'),__("Every",'rsvpmaker'));
		if((int)$weeks[0] == 0)
			$s = __('Schedule Varies','rsvpmaker');
		else
			{
			foreach($weeks as $week)
				{
				if(empty($s))
					$s = '';
				else
					$s .= '/ ';
				$s .= $weekarray[(int) $week].' ';
				}
			if($dows && is_array($dows))
			foreach($dows as $dow)
				$s .= $dayarray[(int) $dow] . ' ';	
			}
		$t = rsvpmaker_mktime($sked["hour"],$sked["minutes"],0,date('n'),date('j'),date('Y'));
		$dateblock = $s.' '.rsvpmaker_strftime($rsvp_options["time_format"],$t);
	
	return $dateblock;
}

function signed_up_ajax ($post_id) {
global $wpdb;
$sql = "SELECT count(*) FROM ".$wpdb->prefix."rsvpmaker WHERE event=$post_id AND yesno=1 ORDER BY id DESC";
$total = (int) $wpdb->get_var($sql);
$rsvp_max = get_post_meta($post_id,'_rsvp_max',true);
if($total)
{
$output = $total.' '.__('signed up so far.','rsvpmaker');
if($rsvp_max)
	$output .= ' '.__('Limit','rsvpmaker').': '.$rsvp_max;
return '<p class="signed_up">'.$output.'</p>';
}
}

function rsvpmaker_exclude_templates_special( $query ) {
    if( is_admin() || !$query->is_search())
        return;
$query->set('meta_query', array(
        array(
            'key'   => '_rsvpmaker_special',
            'compare' => 'NOT EXISTS'
        ),
        array(
            'key'   => '_sked_template',
            'compare' => 'NOT EXISTS'
        ),
        array(
            'key'   => '_sked',
            'compare' => 'NOT EXISTS'
        )
    ) );
}
add_action('pre_get_posts','rsvpmaker_exclude_templates_special');

function rsvpmaker_author_page ($query) {
	if(!is_admin() && !empty($query->is_author) && empty($query->query_vars['suppress_filters'])) {
		$query->set('post_type', array('post','rsvpmaker'));
		$query->set('post_parent', 0);
	}
	return $query;
}

add_filter('pre_get_posts','rsvpmaker_author_page');

function get_rsvp_link($post_id, $justlink = false) {
	global $rsvp_options;
	$rsvplink = get_permalink($post_id);
	$rsvplink = add_query_arg('e','*|EMAIL|*',$rsvplink).'#rsvpnow';
	if(!is_user_logged_in() && get_post_meta($post_id, '_rsvp_login_required', true))
		$rsvplink = wp_login_url( $rsvplink );
	if($justlink)
		return $rsvplink; // just the link, otherwise return button
	return sprintf($rsvp_options["rsvplink"],$rsvplink);
}

function rsvpdateblock ($atts = array()) {
	global $post;
	$custom_fields = get_rsvpmaker_custom($post->ID);
	$date_array = rsvp_date_block($post->ID, $custom_fields);
	$dateblock = $date_array["dateblock"];
	return '<div class="dateblock">'.$dateblock."\n</div>\n";
}

function rsvpmaker_hide_time_posted($time) {
	global $post;
	if($post->post_type == 'rsvpmaker')
		return '';
	return $time;
}

add_filter('the_time','rsvpmaker_hide_time_posted',20);

function rsvpmaker_get_the_archive_title($title) {
	global $post;
	if($post->post_type == 'rsvpmaker')
		{
		if(is_single())
			return ''; 
		else
			return __('Event Listings','rsvpmaker');
		}
	return $title;
}

add_filter( 'get_the_archive_title', 'rsvpmaker_get_the_archive_title',20 );

function rsvpmaker_form( $atts = array(), $form_content='' ) {
	global $post, $showbutton;
	$showbutton = false;
	$output = '';
	$backup = $post;
	if(!empty($atts['post_id'])) {
		$post = get_post($atts['post_id']);
		$output = event_content($form_content,true).rsvp_form_jquery();
	}
	$post = $backup;
	return $output;
}

function rsvpmaker_daily_schedule($atts) {
	global $rsvp_options;
	$output = '';
	$last = '';
	$start_limit = $end_limit = 0;
	if(isset($atts['start']))
		$start_limit = rsvpmaker_strtotime($atts['start']);
	if(isset($atts['end']))
		$end_limit = rsvpmaker_strtotime($atts['end']);

	$where = ($start_limit) ? "datetime > '".date('Y-m-d H:i:s',$start_limit)."'" : '';
	$future = get_future_events($where,50);
	foreach($future as $event) {
		$t = rsvpmaker_strtotime($event->datetime);
		if($start_limit && ($t < $start_limit))
			continue;
		if($end_limit && ($t > $end_limit))
			continue;
		$terms = get_the_terms($event->ID,'rsvpmaker-type');
		$wrapclass = '';
		$termslugs = array();
		$term_links = array();
		if($terms)
		foreach($terms as $term)
		{
			$wrapclass .= ' '.$term->slug;
			$termslugs[] = $term->slug;
			if(empty($atts['type']) || ($atts['type'] != $term->slug))
				$term_links[] = '<a href="' . esc_attr( get_term_link( $term->slug, 'rsvpmaker-type' ) ) . '">' . __( $term->name ) . '</a>';
		}
		$termline = '<p class="daily-schedule-event-types">'.implode(', ',$term_links).'</p>';
		if(isset($atts['type']) && !in_array($atts['type'],$termslugs))
			continue;
		$index = rsvpmaker_strftime($rsvp_options['long_date'],$event->datetime);
		$end = '';
		if(get_post_meta($event->ID,'_firsttime', true) == 'set')
			{
			$endtime = get_post_meta($event->ID,'_endfirsttime', true);
			$end = ' - '.rsvpmaker_strftime($rsvp_options['time_format'],$endtime);
			}
		$eventcontent = '<h3 class="rsvpmaker-schedule-headline"><span class="rsvpmaker_schedule_time">'.rsvpmaker_strftime($rsvp_options['time_format'],$t).$end.'</span>';
		$eventcontent .= ' <span class="rsvpmaker-schedule-title">'.$event->post_title.'</h3>';
		$parts = explode('<!--more-->',$event->post_content);
		$content = $parts[0];
		if(!empty($parts[1]))
		{
			$content .= '<p ><button id="rsvpmaker-schedule-button'.$event->ID.'" class="rsvpmaker-schedule-button">'.__('Read more').'</button></p>
			<div id="rsvpmaker-schedule-detail'.$event->ID.'" class="rsvpmaker-schedule-detail" >';
			$content .= $parts[1]."\n</div>";
			$content = str_replace('<!-- wp:more -->','',$content);
			$content = str_replace('<!-- /wp:more -->','',$content);
		}
		$eventcontent .= apply_filters('the_content',$content);
		//$eventcontent .= get_the_content(__('Read more','rsvpmaker'),false,$event);
		$eventcontent = '<div class="rsvpmaker-schedule-item'.$wrapclass.'">'."\n".$eventcontent.$termline."\n".'</div>';

		if(empty($schedule[$index]))
			$schedule[$index] = $eventcontent;
		else
			$schedule[$index] .= $eventcontent;
	}
	if(!empty($schedule))
	foreach($schedule as $day => $eventcontent) {
		if($day != $last)
			$output .= sprintf('<h2>%s</h2>',$day);
		$output .= $eventcontent;
		$last = $day;
	}
	$output = '<div class="rsvpmaker-schedule">'."\n".$output."\n</div>";
	$output .= "\n<script>
	jQuery(document).ready(function($) {
	$('.rsvpmaker-schedule-detail').hide();
	$( '.rsvpmaker-schedule-button' ).click(function( event ) {
		var button_id = $(this).attr('id');
		var more_id = button_id.replace('button','detail');
		$('#'+button_id).hide();
		$('#'+more_id).show();
	  });
	});
	</script>";
	return $output;
}

function embed_dateblock ($atts) {
	$d = rsvp_date_block($atts["post_id"],get_post_custom($atts["post_id"]));
	return $d["dateblock"];
}

function rsvp_date_block($post_id, $custom_fields = array(), $top = true) {
global $rsvp_options;
global $last_time;
global $post;
if(is_admin())
	return;
if(empty($post_id))
	$post_id = $post->ID;
if(empty($custom_fields))
	$custom_fields = get_post_custom($post_id);

if(empty($custom_fields["_rsvp_dates"][0]) && !rsvpmaker_is_template($post_id))
	return array('dateblock' => '','dur' => NULL, 'last_time' => NULL, 'firstrow' => array());	
$time_format = $rsvp_options["time_format"];
$dur = $tzbutton = '';
$firstrow = array();

if(!strpos($time_format,'%Z') && isset($custom_fields['_add_timezone'][0]) && $custom_fields['_add_timezone'][0] )
	{
	$time_format .= ' %Z';
	}
$permalink = get_permalink($post_id);
$results = get_rsvp_dates($post_id);
if($results)
{
$start = 2;
$dateblock = '';
global $last_time;
foreach($results as $index => $row)
	{
	if(empty($firstrow))
		$firstrow = $row;
	$last_time = $t = rsvpmaker_strtotime($row["datetime"]);
	$dateblock .= '<div id="startdate'.$post_id.'" itemprop="startDate" datetime="'.date('c',$t).'">';
	$dateblock .= utf8_encode(rsvpmaker_strftime($rsvp_options["long_date"],$t));
	$dur = $row["duration"];
	if($dur == 'set') {
		$end_time = $row['end_time'];
		$dur = rsvpmaker_strtotime($end_time);
		$tzcode = strpos($time_format,'%Z');
		if($tzcode) 
			$time_format = str_replace('%Z','',$time_format);
		$dateblock .= '<span class="time">'.rsvpmaker_strftime(' '.$time_format,$t);
		$dateblock .= ' <span class="end_time">'.__('to','rsvpmaker')." ".rsvpmaker_strftime($time_format,$dur).'</span>';
		if($tzcode)
			$dateblock .= ' '.rsvpmaker_strftime('%Z',$t);
		$dateblock .= '</span>';
	}
	elseif($dur != 'allday')
		{
		$dateblock .= '<span class="time">'.rsvpmaker_strftime(' '.$time_format,$t).'</span>';
		}
	$dateblock .= '<span class="timezone_hint" utc="'.gmdate('c',$t). '"  target="timezone_converted'.$post->ID.'">'."\n";
	if($top && isset($custom_fields['_convert_timezone'][0]) && $custom_fields['_convert_timezone'][0]) {
		if(is_email_context()) {
			$tzbutton = sprintf('<a href="%s">%s</a>',esc_url_raw(add_query_arg('tz',1,get_permalink($post_id))),__('Show in my timezone','rsvpmaker'));
		}
		else {
			$tzbutton = '<button class="timezone_on">'.__('Show in my timezone','rsvpmaker').'</button>';
		}
	}
	$dateblock .= '</span><span id="timezone_converted'.$post->ID.'"></span></div>';

	}
//gcal link
if( ( (!empty($rsvp_options["calendar_icons"]) && !isset($custom_fields["_calendar_icons"][0])) || !empty($custom_fields["_calendar_icons"][0]) ))// && !is_email_context ())
	{
	if(!empty($firstrow['end_time']))
	{
		$p = explode(' ',$firstrow['datetime']);
		$end_time = $p[0].' '.$firstrow['end_time'];
	}
	else 
		$end_time = $firstrow["datetime"] . ' +1 hour';
	$j = (strpos($permalink,'?')) ? '&' : '?';
	if(is_email_context())
	$dateblock .= sprintf('<div class="rsvpcalendar_buttons"> <a href="%s" target="_blank">Google Calendar</a> | <a href="%s">Outlook/iCal</a> | %s</div>',rsvpmaker_to_gcal($post,$firstrow["datetime"],$end_time),$permalink.$j.'ical=1',$tzbutton );
	else
	$dateblock .= sprintf('<div class="rsvpcalendar_buttons"><a href="%s" target="_blank" title="%s"><img src="%s" border="0" width="25" height="25" /></a>&nbsp;<a href="%s" title="%s"><img src="%s"  border="0" width="28" height="25" /></a> %s</div>',rsvpmaker_to_gcal($post,$firstrow["datetime"],$end_time), __('Add to Google Calendar','rsvpmaker'), plugins_url('rsvpmaker/button_gc.gif'),$permalink.$j.'ical=1', __('Add to Outlook/iCal','rsvpmaker'), plugins_url('rsvpmaker/button_ical.gif'), $tzbutton );
	}
}
elseif(rsvpmaker_is_template($post->ID))
	{
		$sked = get_template_sked($post->ID);
		//backward compatability
		if(is_array($sked["week"]))
			{
				$weeks = $sked["week"];
				$dows = $sked["dayofweek"];
			}
		else
			{
				$weeks = array();
				$dows = array();
				$weeks[0] = $sked["week"];
				$dows[0] = (empty($sked["dayofweek"])) ? 0 : $sked["dayofweek"];
			}

		$dayarray = Array(__("Sunday",'rsvpmaker'),__("Monday",'rsvpmaker'),__("Tuesday",'rsvpmaker'),__("Wednesday",'rsvpmaker'),__("Thursday",'rsvpmaker'),__("Friday",'rsvpmaker'),__("Saturday",'rsvpmaker'));
		$weekarray = Array(__("Varies",'rsvpmaker'),__("First",'rsvpmaker'),__("Second",'rsvpmaker'),__("Third",'rsvpmaker'),__("Fourth",'rsvpmaker'),__("Last",'rsvpmaker'),__("Every",'rsvpmaker'));
		if((int)$weeks[0] == 0)
			$s = __('Schedule Varies','rsvpmaker');
		else
			{
			foreach($weeks as $week)
				{
				if(empty($s))
					$s = '';
				else
					$s .= '/ ';
				$s .= $weekarray[(int) $week].' ';
				}
			foreach($dows as $dow)
				$s .= $dayarray[(int) $dow] . ' ';	
			}
		
		$t = rsvpmaker_mktime($sked["hour"],$sked["minutes"],0,date('n'),date('j'),date('Y'));
		$dateblock = $s.' '.rsvpmaker_strftime($rsvp_options["time_format"],$t);
	
		$dateblock .= '<div id="startdate'.$post_id.'" itemprop="startDate" datetime="'.date('c',$t).'"></div>';
		if(current_user_can('edit_rsvpmakers'))
			$dateblock .= sprintf('<br /><a href="%s">%s</a>',admin_url('edit.php?post_type=rsvpmaker&page=rsvpmaker_template_list&t='.$post_id),__('Create/update events from template','rsvpmaker'));
	}
	else // no dates, no sked, maybe this is an agenda or a landing page
	{
		return array('dateblock' => '','dur' => NULL, 'last_time' => NULL);			
	}

return array('dateblock' => $dateblock,'dur' => $dur, 'last_time' => $last_time, 'firstrow' => $firstrow);
}

function future_rsvp_links ($atts = array()) {
global $rsvp_options;
$output = '<ul>';
$limit = (empty($atts['limit'])) ? 5 : (int) $atts['limit'];
$events = get_events_rsvp_on($limit);
if(empty($events))
	return;
foreach($events as $index => $event)
	{
		if(($index == 0) && !empty($atts['skipfirst']))
			continue;
		$url = get_permalink($event->ID).'#rsvpnow';
		$t = rsvpmaker_strtotime($event->datetime);
		$datetime = rsvpmaker_strftime('',$t).' '.rsvpmaker_strftime($rsvp_options['time_format'],$t);
		$output .= sprintf('<li><a href="%s">%s</a></li>',esc_url_raw($url),esc_html($event->post_title.' '.$datetime));
		$event->post_content = '';
		//$output .= '<li>'.var_export($event,true).'</li>';
	}
	$output .= '</ul>';
return $output;
}

add_shortcode('future_rsvp_links','future_rsvp_links');

add_action('wp_footer','rsvpmaker_timezone_footer');

?>