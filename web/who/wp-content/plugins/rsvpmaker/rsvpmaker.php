<?php
/*
Plugin Name: RSVPMaker
Plugin URI: http://www.rsvpmaker.com
Description: Schedule events, send invitations to your mailing list and track RSVPs. You get all your familiar WordPress editing tools with extra options for setting dates and RSVP options. Online payments with PayPal or Stripe can be added with a little extra configuration. Email invitations can be sent through MailChimp or to members of your website community who have user accounts. Recurring events can be tracked according to a schedule such as "First Monday" or "Every Friday" at a specified time, and the software will calculate future dates according to that schedule and let you track them together. <a href="options-general.php?page=rsvpmaker-admin.php">Options</a>
Author: David F. Carr
Author URI: http://www.carrcommunications.com
Text Domain: rsvpmaker
Domain Path: /translations
Version: 8.0.5
*/

function get_rsvpversion(){
return '8.0.5';
}

global $wp_version;
global $default_tz;
$default_tz = date_default_timezone_get();

if (version_compare($wp_version,"3.0","<"))
	exit( __("RSVPmaker plugin requires WordPress 3.0 or greater",'rsvpmaker') );

function rsvpmaker_load_plugin_textdomain() {
    load_plugin_textdomain( 'rsvpmaker', FALSE, basename( dirname( __FILE__ ) ) . '/translations/' );
}

global $rsvp_options;
$rsvp_options = get_option('RSVPMAKER_Options');

$locale = get_locale();
setlocale(LC_ALL,$locale);

function rsvp_options_defaults() {
global $rsvp_options;

if(empty($rsvp_options)) $rsvp_options = array();
//defaults

$rsvp_defaults = array("menu_security" => 'manage_options',
"rsvpmaker_template" =>  'publish_rsvpmakers', 
"recurring_event" => 'publish_rsvpmakers', 
"multiple_events" => 'publish_rsvpmakers', 
"documentation" => 'edit_rsvpmakers', 
"calendar_icons" => 1,
"social_title_date" => 1, 
"default_content" => '', 
"rsvp_to" => get_bloginfo('admin_email'),
"confirmation_include_event" => 0,
"rsvpmaker_send_confirmation_email" => 1,
"rsvp_instructions" => '',
"rsvp_count" => 1, 
"rsvp_count_party" => 1, 
"rsvp_yesno" => 1,
'send_payment_reminders' => 1,
"rsvp_on" => 0,
"rsvp_max" => 0,
"login_required" => 0,
"rsvp_captcha" => 0,
"show_attendees" => 0,
'convert_timezone' => 0,
'add_timezone' => 0,
'rsvplink' => '<p><a style="width: 8em; display: block; border: medium inset #FF0000; text-align: center; padding: 3px; background-color: #0000FF; color: #FFFFFF; font-weight: bolder; text-decoration: none;" class="rsvplink" href="%s">'. __('RSVP Now!','rsvpmaker').'</a></p>',
"rsvp_form_title" => __('RSVP Now!','rsvpmaker'),
'defaulthour' => 19,
'defaultmin' => 0,
"long_date" => '%A %B %e, %Y',
"short_date" => '%B %e',
"time_format" => '%l:%M %p',
"smtp" => '',
"paypal_currency" => 'USD',
"currency_decimal" => '.',
"currency_thousands" => ',',
"paypal_invoiceno" => 1,
"stripe" => 0,
"show_screen_recurring" => 0,
"show_screen_multiple" => 0,
"dashboard_message" => '',
"update_rsvp" => __('Update RSVP','rsvpmaker')
);

$rsvp_defaults = apply_filters('rsvpmaker_defaults',$rsvp_defaults);

foreach($rsvp_defaults as $index => $value)
	{
		if(!isset($rsvp_options[$index]))
			$rsvp_options[$index] = $rsvp_defaults[$index];
	}

if(empty($rsvp_options["long_date"]) || (strpos($rsvp_options["long_date"],'%')  === false))
	{
	$rsvp_options["long_date"] = '%A %B %e, %Y';
	$rsvp_options["short_date"] = '%B %e';
	$rsvp_options["time_format"] = '%l:%M %p';
	update_option('RSVPMAKER_Options',$rsvp_options);
	}

if(isset($rsvp_options["rsvp_to_current"]) && $rsvp_options["rsvp_to_current"] && is_user_logged_in() ) 
	{
	global $current_user;
	$rsvp_options["rsvp_to"] = $current_user->user_email;
	}

if(empty($rsvp_options['rsvp_form']) || isset($_GET["reset_form"])) {
	if(function_exists('do_blocks') && !class_exists('Classic_Editor')) {
		$form = '<!-- wp:rsvpmaker/formfield {"label":"First Name","slug":"first","guestform":true,"sluglocked":true,"required":"required"} /-->
<!-- wp:rsvpmaker/formfield {"label":"Last Name","slug":"last","guestform":true,"sluglocked":true,"required":"required"} /-->
<!-- wp:rsvpmaker/formfield {"label":"Email","slug":"email","sluglocked":true,"required":"required"} /-->
<!-- wp:rsvpmaker/formfield {"label":"Phone","slug":"phone"} /-->
<!-- wp:rsvpmaker/formselect {"label":"Phone Type","slug":"phone_type","choicearray":["Mobile Phone","Home Phone","Work Phone"]} /-->
<!-- wp:rsvpmaker/guests -->
<div class="wp-block-rsvpmaker-guests"><!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph --></div>
<!-- /wp:rsvpmaker/guests -->
<!-- wp:rsvpmaker/formnote /-->';
	}
	else {
		$form = '<p><label>'.__('Email','rsvpmaker').':</label> [rsvpfield textfield="email" required="1"]</p>
		<p><label>'.__('First Name','rsvpmaker').':</label> [rsvpfield textfield="first" required="1"]</p>
		<p><label>'.__('Last Name','rsvpmaker').':</label> [rsvpfield textfield="last" required="1"]</p>
		[rsvpprofiletable show_if_empty="phone"]
		<p><label>'.__('Phone','rsvpmaker').':</label> [rsvpfield textfield="phone" size="20"]</p>
		<p><label>'.__('Phone Type','rsvpmaker').':</label> [rsvpfield selectfield="phone_type" options="Work Phone,Mobile Phone,Home Phone"]</p>
		[/rsvpprofiletable]
		[rsvpguests]
		<p>'.__('Note','rsvpmaker').':<br />
		<textarea name="note" cols="60" rows="2" id="note">[rsvpnote]</textarea></p>';
	}
	$data['post_title'] = 'Form:Default';
	$data['post_content'] = $form;
	$data['post_status'] = 'publish';
	$data['post_author'] = 1;
	$data['post_type'] = 'rsvpmaker';
	$rsvp_options['rsvp_form'] = wp_insert_post($data);
	update_post_meta($rsvp_options['rsvp_form'],'_rsvpmaker_special','RSVP Form');
	update_option('RSVPMAKER_Options',$rsvp_options);
}
elseif(!is_numeric($rsvp_options['rsvp_form']))
	{
		$data['post_title'] = 'Form:Default';
		$data['post_content'] = $rsvp_options['rsvp_form'];
		$data['post_status'] = 'publish';
		$data['post_type'] = 'rsvpmaker';
		$data['post_author'] = 1;
		$rsvp_options['rsvp_form'] = wp_insert_post($data);
		update_option('RSVPMAKER_Options',$rsvp_options);	
	}

$rsvp_defaults['rsvp_form'] = $rsvp_options['rsvp_form'];

if(strpos($rsvp_options['rsvplink'],'*|EMAIL|*')) {
	$rsvp_options['rsvplink'] = str_replace('?e=*|EMAIL|*#rsvpnow','',$rsvp_options['rsvplink']);
	update_option('RSVPMAKER_Options',$rsvp_options);	
	}
//if html removed (recover from error with sanitization on settings screen)
if(!strpos($rsvp_options['rsvplink'],'</a>')) {
	$rsvp_options['rsvplink'] = '<p><a style="width: 8em; display: block; border: medium inset #FF0000; text-align: center; padding: 3px; background-color: #0000FF; color: #FFFFFF; font-weight: bolder; text-decoration: none;" class="rsvplink" href="%s">'. __('RSVP Now!','rsvpmaker').'</a></p>';
	update_option('RSVPMAKER_Options',$rsvp_options);	
	}

if(empty($rsvp_options['rsvp_confirm']))
	{
	$message = '<!-- wp:paragraph -->
<p>'.__('Thank you!','rsvpmaker').'</p>
<!-- /wp:paragraph -->';
	$rsvp_options['rsvp_confirm'] = wp_insert_post(array('post_title'=>'Confirmation:Default','post_content'=>$message, 'post_status'=>'publish','post_type'=>'rsvpemail','post_parent' => 0));
	update_option('RSVPMAKER_Options',$rsvp_options);	
	}
elseif(!is_numeric($rsvp_options['rsvp_confirm']))
	{
	$rsvp_options['rsvp_confirm'] = wp_insert_post(array('post_title'=>'Confirmation:Default',
	'post_content'=> rsvpautog($rsvp_options['rsvp_confirm']),
	'post_status'=>'publish','post_type'=>'rsvpemail', 'post_parent' => 0));
	update_option('RSVPMAKER_Options',$rsvp_options);		
	}
}

function rsvpmaker_defaults_for_post($post_id) {
global $rsvp_options;
$defaults = array( 
	"calendar_icons" => "_calendar_icons",
	"rsvp_to" => "_rsvp_to",
	"rsvp_confirm" => "_rsvp_confirm", 
	"rsvpmaker_send_confirmation_email" => "_rsvp_rsvpmaker_send_confirmation_email",
	"confirmation_include_event" => "_rsvp_confirmation_include_event",
	"rsvp_instructions" => "_rsvp_instructions",
	"rsvp_count" => "_rsvp_count", 
	"rsvp_count_party" => "_rsvp_count_party", 
	"rsvp_yesno" => "_rsvp_yesno", 
	"rsvp_max" => "_rsvp_max",
	"login_required" => "_rsvp_login_required",
	"rsvp_captcha" => "_rsvp_captcha",
	"show_attendees" => "_rsvp_show_attendees",
	'convert_timezone' => '_convert_timezone',
	'add_timezone' => '_add_timezone',
	"rsvp_form" => "_rsvp_form"
	);
foreach($defaults as $index => $label)
	update_post_meta($post_id,$label,$rsvp_options[$index]);
}

function get_rsvpmaker_custom($post_id) {
global $rsvp_options;

$defaults = array( 
"calendar_icons" => "_calendar_icons",
"rsvp_to" => "_rsvp_to",
"rsvp_confirm" => "_rsvp_confirm", 
"rsvpmaker_send_confirmation_email" => "_rsvp_rsvpmaker_send_confirmation_email",
"confirmation_include_event" => "_rsvp_confirmation_include_event",
"rsvp_instructions" => "_rsvp_instructions",
"rsvp_count" => "_rsvp_count", 
"rsvp_count_party" => "_rsvp_count_party", 
"rsvp_yesno" => "_rsvp_yesno", 
"rsvp_max" => "_rsvp_max",
"login_required" => "_rsvp_login_required",
"rsvp_captcha" => "_rsvp_captcha",
"show_attendees" => "_rsvp_show_attendees",
'convert_timezone' => '_convert_timezone',
'add_timezone' => '_add_timezone',
"rsvp_form" => "_rsvp_form"
);


if(strpos($_SERVER['REQUEST_URI'],'post-new.php') && !isset($_GET['clone']))
	{
	$custom['_rsvp_on'][0] = $rsvp_options['rsvp_on'];
	foreach($defaults as $default_key => $custom_key)
		$custom[$custom_key][0] = $rsvp_options[$default_key];	
	return $custom;
	}
else
	{
	$custom = get_post_custom($post_id);
	$custom['_rsvp_on'][0] = (isset($custom['_rsvp_on'][0]) && $custom['_rsvp_on'][0]) ? 1 : 0;
	foreach($defaults as $default_key => $custom_key)
		if(!isset($custom[$custom_key][0]))
			$custom[$custom_key][0] = $rsvp_options[$default_key];	
	return $custom;
	}
}

if(file_exists(WP_PLUGIN_DIR."/rsvpmaker-custom.php") )
	include_once WP_PLUGIN_DIR."/rsvpmaker-custom.php";

include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-util.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-admin.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-api-endpoints.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-display.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-plugabble.php";
include WP_PLUGIN_DIR."/rsvpmaker/mailchimp-api-master/src/MailChimp.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-email.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-privacy.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-actions.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-form.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-widgets.php";
include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-group-email.php";
include WP_PLUGIN_DIR."/rsvpmaker/script.php";

if(!function_exists('do_blocks'))
	include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-classic.php";

$gateways = get_rsvpmaker_payment_options ();

if(in_array('Stripe',$gateways))
	include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-stripe.php";
if(in_array('PayPal REST API',$gateways))
	include WP_PLUGIN_DIR."/rsvpmaker/paypal-rest/paypal-rest.php";

function rsvpmaker_gutenberg_check () {
global $carr_gut_test;
if(function_exists('register_block_type') && !isset($carr_gut_test))
	require_once plugin_dir_path( __FILE__ ) . 'gutenberg/src/init.php';
}

if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    include WP_PLUGIN_DIR."/rsvpmaker/rsvpmaker-recaptcha.php";
}

function rsvpmaker_create_post_type() {
global $rsvp_options;
$menu_label = (isset($rsvp_options["menu_label"])) ? $rsvp_options["menu_label"] : __("RSVP Events",'rsvpmaker');
$supports = array('title','editor','author','excerpt','custom-fields','thumbnail','revisions');

  register_post_type( 'rsvpmaker',
    array(
      'labels' => array(
        'name' => $menu_label,
        'add_new_item' => __( 'Add New RSVP Event','rsvpmaker' ),
        'edit_item' => __( 'Edit RSVP Event','rsvpmaker' ),
        'new_item' => __( 'RSVP Events','rsvpmaker' ),
        'singular_name' => __( 'RSVP Event','rsvpmaker' )
      ),
    'menu_icon' => 'dashicons-calendar-alt',
	'public' => true,
	'can_export' => false,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => 'rsvpmaker','with_front' => FALSE), 
    'capability_type' => 'rsvpmaker',
    'map_meta_cap' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => 5,
    'supports' => $supports,
	'show_in_rest' => true,
	'taxonomies' => array('rsvpmaker-type','post_tag')
    )
  );

  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Event Types', 'taxonomy general name', 'rsvpmaker' ),
    'singular_name' => _x( 'Event Type', 'taxonomy singular name', 'rsvpmaker' ),
    'search_items' =>  __( 'Search Event Types','rsvpmaker' ),
    'all_items' => __( 'All Event Types','rsvpmaker' ),
    'parent_item' => __( 'Parent Event Type','rsvpmaker' ),
    'parent_item_colon' => __( 'Parent Event Type:','rsvpmaker' ),
    'edit_item' => __( 'Edit Event Type','rsvpmaker' ), 
    'update_item' => __( 'Update Event Type','rsvpmaker' ),
    'add_new_item' => __( 'Add New Event Type','rsvpmaker' ),
    'new_item_name' => __( 'New Event Type','rsvpmaker' ),
    'menu_name' => __( 'Event Type','rsvpmaker' ),
  ); 	

  register_taxonomy('rsvpmaker-type',array('rsvpmaker'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
	'show_in_rest' => true,
    'query_var' => true,
  ));

/*     'rewrite' => array( 'slug' => 'rsvpmaker-type' ), */

//tweak for users who report "page not found" errors - flush rules on every init
global $rsvp_options;
if(isset($rsvp_options["flush"]) && $rsvp_options["flush"])
	flush_rewrite_rules();

// if there is a logged in user, set editing roles
global $current_user;
if( isset($current_user) )
	rsvpmaker_roles();

}

//make sure new rules will be generated for custom post type - flush for admin but not for regular site visitors

function cpevent_activate() {
global $wpdb;
global $rsvp_options;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

$sql = "CREATE TABLE `".$wpdb->prefix."rsvpmaker` (
  `id` int(11) NOT NULL auto_increment,
  `email` varchar(255)   CHARACTER SET utf8 COLLATE utf8_general_ci  default NULL,
  `yesno` tinyint(4) NOT NULL default '0',
  `first` varchar(255)  CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL default '',
  `last` varchar(255)  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL default '',
  `details` text  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `event` int(11) NOT NULL default '0',
  `owed` float(6,2) NOT NULL default '0.00',
  `amountpaid` float(6,2) NOT NULL default '0.00',
  `master_rsvp` int(11) NOT NULL default '0',
  `guestof` varchar(255)   CHARACTER SET utf8 COLLATE utf8_general_ci  default NULL,
  `note` text   CHARACTER SET  utf8 COLLATE utf8_general_ci NOT NULL,
  `participants` INT NOT NULL DEFAULT '0',
  `user_id` INT NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
dbDelta($sql);

$sql = "CREATE TABLE `".$wpdb->prefix."rsvpmaker_event` (
  `event` int(11) NOT NULL default '0',
  `post_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL,
  `date` datetime,
  PRIMARY KEY  (`event`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
dbDelta($sql);

$sql = "SELECT post_title, event, meta_value FROM `".$wpdb->prefix."rsvpmaker` join $wpdb->posts ON ".$wpdb->prefix."rsvpmaker.event=wp_posts.ID join $wpdb->postmeta ON $wpdb->posts.ID = wp_postmeta.post_id WHERE meta_key='_rsvp_dates' group by event";
$results = $wpdb->get_results($sql);
if($results)
foreach($results as $row) {
	$sql = $wpdb->prepare("REPLACE INTO `".$wpdb->prefix."rsvpmaker_event` SET event=%d, post_title=%s, date=%s ",$row->event,$row->post_title,$row->meta_value);
	$wpdb->query($sql);
}
	
$sql = "CREATE TABLE `".$wpdb->prefix."rsvp_volunteer_time` (
  `id` int(11) NOT NULL auto_increment,
  `event` int(11) NOT NULL default '0',
  `rsvp` int(11) NOT NULL default '0',
  `time` int(11) default '0',
  `participants` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
dbDelta($sql);

$sql = "SELECT slug FROM ".$wpdb->prefix."terms JOIN `".$wpdb->prefix."term_taxonomy` on ".$wpdb->prefix."term_taxonomy.term_id= ".$wpdb->prefix."terms.term_id WHERE taxonomy='rsvpmaker-type' AND slug='featured'";

if(! $wpdb->get_var($sql) )
	{
	wp_insert_term(
  'Featured', // the term 
  'rsvpmaker-type', // the taxonomy
  array(
    'description'=> 'Featured event. Can be used to put selected events in a listing, for example on the home page',
    'slug' => 'featured'
  ) 
);
	}

$sql = "UPDATE $wpdb->posts SET post_type='rsvpemail' WHERE post_type='rsvpmaker' AND post_parent != 0 ";
$wpdb->query($sql);

$rsvp_options["dbversion"] = 14;
update_option('RSVPMAKER_Options',$rsvp_options);
}

register_activation_hook( __FILE__, 'cpevent_activate' );

//upgrade database if necessary
if(isset($rsvp_options["dbversion"]) && ($rsvp_options["dbversion"] < 4))
	{
	//correct character encoding error in early releases
	global $wpdb;
	$wpdb->query("ALTER TABLE `wp_rsvpmaker` CHANGE `first` `first` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");
	$wpdb->query("ALTER TABLE `wp_rsvpmaker` CHANGE `last` `last` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");
	$wpdb->query("ALTER TABLE `wp_rsvpmaker` CHANGE `email` `email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");	
	$wpdb->query("ALTER TABLE `wp_rsvpmaker` CHANGE `guestof` `guestof` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");	
	$wpdb->query("ALTER TABLE `wp_rsvpmaker` CHANGE `details` `details` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ");	
	$wpdb->query("ALTER TABLE `wp_rsvpmaker` CHANGE `note` `note` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ");
	}
if(!empty($rsvp_options["dbversion"]) && ($rsvp_options["dbversion"] < 6))
	{
	convert_date_meta();
	}
if(!empty($rsvp_options["dbversion"]) && ($rsvp_options["dbversion"] < 7))
	{
	global $wpdb;
	$sql = "SELECT * FROM $wpdb->postmeta WHERE meta_key='_rsvpmaker_parent'";
	$results = $wpdb->get_results($sql);
	if($results)
	{
		foreach($results as $row)
		{
			wp_update_post(array('ID' => $row->post_id,'post_parent' => $row->meta_value));
		}
	}
	}
if(isset($rsvp_options["dbversion"]) && ($rsvp_options["dbversion"] < 10))
	cpevent_activate();
global $wpdb;
$test = $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."rsvpmaker_event'");
if(!$test)
{
	rsvpmaker_debug_log('cpevent_activate fired');
	cpevent_activate();
}

if(isset($rsvp_options["dbversion"]) && ($rsvp_options["dbversion"] < 14))
{
	rsvpmaker_upgrade_templates();
	$rsvp_options["dbversion"] = 14;
	update_option('RSVPMAKER_Options',$rsvp_options);
}

function rsvpmaker_deactivate() {
    // Unregister the post type, so the rules are no longer in memory.
    unregister_post_type( 'rsvpmaker' );
    unregister_post_type( 'rsvpemail' );
    // Clear the permalinks to remove our post type's rules from the database.
	flush_rewrite_rules();
	wp_unschedule_hook('rsvp_cleanup_hook');
	wp_unschedule_hook('rsvpmaker_relay_init_hook');
	wp_unschedule_hook('rsvpmaker_cron_email');
	wp_unschedule_hook('rsvpmaker_cron_email_preview');
	wp_unschedule_hook('rsvp_daily_reminder_event');
}
register_deactivation_hook( __FILE__, 'rsvpmaker_deactivate' );

add_action('init','rsvp_firsttime',1);
function rsvp_firsttime () {
	global $rsvp_options;
	if($rsvp_options["dbversion"] > 12)
		return;
	$rsvp_options["dbversion"] = 13;
	update_option('RSVPMAKER_Options',$rsvp_options);
	$future = get_future_events();
	if(is_array($future))
	foreach($future as $event) {
		$post_id = $event->ID;
		$datetime = get_rsvp_date($post_id);
		$end = get_post_meta($post_id, '_end'.$datetime,true);
		if(empty($end))
			{
				$t = rsvpmaker_strtotime($datetime . ' +1 hour');
				$end = rsvpmaker_date('H:i',$t);
			}
		$duration = get_post_meta($post_id, '_'.$datetime,true);
		update_post_meta($post_id,'_firsttime',$duration);
		update_post_meta($post_id,'_endfirsttime',$end);
	}
}

function convert_date_meta() {
global $wpdb;
$date_table = $wpdb->prefix.rsvp_dates;
$sql = "SELECT * FROM $date_table";
$results = $wpdb->get_results($sql);
if(!$results)
	return;

foreach($results as $row)
{
	add_post_meta($row->postID,'_rsvp_dates',$row->datetime);
	if($row->duration)
		add_post_meta($row->postID,'_'.$row->datetime,$row->duration);	
}
// fix for duplicate dates
//rsvpmaker_duplicate_dates();
}

function rsvpmaker_template_order( $templates='' )
{
global $post;
if($post->post_type != 'rsvpmaker')
    return $templates;
global $rsvp_options;

if(!is_array($templates) && strpos($templates, 'rsvpmaker' ) )
   	 return $templates;	 

if(empty($rsvp_options['rsvp_template']))
	$choices = array("single-rsvpmaker.php");
else
	$choices = array("single-rsvpmaker.php",$rsvp_options['rsvp_template']);

if(!in_array('page.php',$choices))
	$choices[] = 'page.php';
$choices[] = 'single.php';
$choices[] = 'index.php';

$templates = locate_template($choices,false);
return $templates;
}
add_filter( 'single_template', 'rsvpmaker_template_order',99 );

function log_paypal($message) {
global $post;
$ts = rsvpmaker_date('r');
$invoice = $_SESSION["invoice"];
$message .= "\n<br /><br />Post ID: ".$post->ID;
$message .= "\n<br /><br />Invoice: ".$invoice;
$message .= "\n<br />Email: ".$_SESSION["payer_email"];
$message .= "\n<br />Time: ".$ts;
add_post_meta($post->ID, '_paypal_log', $message);
}


if(!function_exists('rsvpmaker_permalink_query') )
{
function rsvpmaker_permalink_query ($id, $query = '') {

$key = "pquery_".$id;
$p = wp_cache_get($key);
if(!$p)
	{
		$p = get_permalink($id);
		$p .= strpos($p,'?') ? '&' : '?';
		wp_cache_set($key,$p);
	}

if(is_array($query) )
	{
		foreach($query as $name => $value)
			$qstring .= $name.'='.$value.'&';
	}
else
	{
		$qstring = $query;
	}
	
	return $p.$qstring;
	
}
} // end function exists

function format_cddate($year,$month,$day,$hours,$minutes )
{
	$month = (int) $month;
	if($month < 10)
		$month = '0'.$month;
	$day = (int) $day;
	if($day < 10)
		$day = '0'.$day;
	return $year.'-'.$month.'-'.$day.' '.$hours.':'.$minutes.':00';	
}

function update_rsvpmaker_dates($postID, $dates_array,$durations_array, $end_array = array()) {
$current_dates = get_post_meta($postID,'_rsvp_dates',false);

foreach($dates_array as $index => $cddate)
	{
		$duration = $durations_array[$index];
		$end_time = (empty($end_array[$index])) ? '' : $end_array[$index];
		if(empty( $current_dates ) )
			 add_rsvpmaker_date($postID,$cddate,$duration,$end_time,$index);
		elseif( is_array($current_dates) )
			{
				if(empty($current_dates[$index]))
					{
						add_rsvpmaker_date($postID,$cddate,$duration,$end_time,$index);
						//rsvpmaker_debug_log("$postID,$cddate,$duration,$end_time",'add date parameters');	
					}
				else {
					update_rsvpmaker_date($postID,$cddate,$duration,$end_time,$index);
					//rsvpmaker_debug_log("$postID,$cddate,$duration,$end_time,$index".$current_dates[$index],'update date parameters');	
				}
			}
		else
			 add_rsvpmaker_date($postID,$cddate,$duration,$end_time,$index);
		$current_dates[] = $cddate;
	}

$missing = array_diff($current_dates,$dates_array);
if(!empty($missing) )
	{
	foreach($missing as $cddate)
		delete_rsvpmaker_date($postID,$cddate);
	}
}

function delete_rsvpmaker_date($postID,$cddate) {
delete_post_meta($postID,'_rsvp_dates',$cddate);
delete_post_meta($postID,'_'.$cddate);
delete_transient('rsvpmakerdates');
}

function add_rsvpmaker_date($postID,$cddate,$duration='',$end_time = '', $index = 0) {
$slug = ($index == 0) ? 'firsttime' : $cddate;
add_post_meta($postID,'_rsvp_dates',$cddate);
add_post_meta($postID,'_'.$slug,$duration);
if(empty($end_time))
{
	$et = rsvpmaker_strtotime($cddate.' +1 hour');
	$end_time = rsvpmaker_date('H:i',$et);
}
add_post_meta($postID,'_end'.$slug,$end_time);
}

function update_rsvpmaker_date($postID,$cddate,$duration='',$end_time = '', $index = 0) {
$slug = ($index == 0) ? 'firsttime' : $cddate;
update_post_meta($postID,'_rsvp_dates',$cddate);
update_post_meta($postID,'_'.$slug,$duration);
if(!empty($end_time))
	update_post_meta($postID,'_end'.$slug,$end_time);
delete_transient('rsvpmakerdates');
}

function rsvpmaker_upcoming_data ($atts)
{
global $post;
global $wp_query;
global $dataloop;
$dataloop = true; // prevent ui output of More Events link

$backup = $wp_query;
$limit = isset($atts["limit"]) ? $atts["limit"] : 10;
if(isset($atts["posts_per_page"]))
	$limit = $atts["posts_per_page"];
if(isset($atts["days"]))
		$datelimit = $atts["days"].' DAY';
else
		$datelimit = '365 DAY';

add_filter('posts_select', 'rsvpmaker_select' );
add_filter('posts_join', 'rsvpmaker_join' );
add_filter('posts_groupby', 'rsvpmaker_groupby' );
add_filter('posts_distinct', 'rsvpmaker_distinct' );
add_filter('posts_fields', 'rsvpmaker_select' );
add_filter('posts_where', 'rsvpmaker_where' );
add_filter('posts_orderby', 'rsvpmaker_orderby' );

$querystring = "post_type=rsvpmaker&post_status=publish";
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
$wp_query = new WP_Query($querystring);

// clean up so this doesn't interfere with other operations
remove_filter('posts_select', 'rsvpmaker_select' );
remove_filter('posts_join', 'rsvpmaker_join' );
remove_filter('posts_groupby', 'rsvpmaker_groupby' );
remove_filter('posts_distinct', 'rsvpmaker_distinct' );
remove_filter('posts_fields', 'rsvpmaker_select' );
remove_filter('posts_where', 'rsvpmaker_where' );
remove_filter('posts_orderby', 'rsvpmaker_orderby' );

$events = array();

if ( have_posts() ) {
while ( have_posts() ) : the_post();
$event['title'] = $post->post_title;
$event['ID'] = $post->ID; 
$event['permalink'] = get_permalink($post->ID);
$event['dates'] = get_rsvp_dates($post->ID);
$events[] = $event;
endwhile;
}
$wp_query = $backup;
wp_reset_postdata();
return $events;
}

function rsvpmaker_duplicate_dates() {
global $wpdb;
	$sql = "SELECT $wpdb->posts.ID as postID, $wpdb->posts.*, a1.meta_value as datetime, meta_id
	 FROM ".$wpdb->posts."
	 JOIN ".$wpdb->postmeta." a1 ON ".$wpdb->posts.".ID =a1.post_id AND a1.meta_key='_rsvp_dates'
	 ORDER BY postID, a1.meta_value";
$results = $wpdb->get_results($sql);
if($results)
foreach($results as $row)
	{
	$slug = $row->datetime.$row->postID;
	$dup = (empty($count[$slug])) ? false : true;
	if($dup)
		{
		$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_id=".$row->meta_id);
		}
	if(isset($_GET["clean_duplicate_dates"]) && isset($_GET["debug"]))
		printf('<p>%s<br />%s %s</p>',$row->post_title,$row->datetime,$row->meta_id);
	$count[$slug]++;
	}
exit();
}

function rsvpmaker_menu_order($menu_ord) {
    if (!$menu_ord || !is_array($menu_ord)) return true;
 
 	foreach($menu_ord as $menu_item)
		{
			if($menu_item == 'edit.php?post_type=page')
				{
				$neworder[] = 'edit.php?post_type=page';
				$neworder[] = 'edit.php?post_type=rsvpmaker';
				$neworder[] = 'edit.php?post_type=rsvpemail';
				}
			elseif(($menu_item == 'edit.php?post_type=rsvpmaker') || ($menu_item == 'edit.php?post_type=rsvpemail'))
				;
			else
				$neworder[] = $menu_item;			
		}
     
    return $neworder;
}
add_filter('custom_menu_order', 'rsvpmaker_menu_order'); // Activate custom_menu_order
add_filter('menu_order', 'rsvpmaker_menu_order');

function rsvpmaker_sc_after_charge ( $charge_response ) {
global $post;
if($post->post_type != 'rsvpmaker')
	return;
$tx_id = $charge_response->id;
$charge = $paid = $charge_response->amount / 100;
if(!isset($_COOKIE['rsvp_for_'.$post->ID]) )
	{
	echo '<p style="color:red;">Error logging payment to RSVP record</p>';
	}
$rsvp_id = $_COOKIE['rsvp_for_'.$post->ID];
	global $wpdb;
	global $post;
	$event = $post->ID;
	if(get_post_meta($event,'_stripe_'.$tx_id,true))
		{
		echo '<p style="color:red;">Payment already recorded</p>';
		return; // if transaction ID recorded, do not duplicate payment
		}

	$paid_amounts = get_post_meta($event,'_paid_'.$rsvp_id);
	if(!empty($paid_amounts))
	foreach($paid_amounts as $payment)
		$paid += $payment;
	$wpdb->query("UPDATE ".$wpdb->prefix."rsvpmaker SET amountpaid='$paid' WHERE id=$rsvp_id ");
	
	add_post_meta($event,'_stripe_'.$tx_id,$charge);
	add_post_meta($event,'_paid_'.$rsvp_id,$charge);
	delete_post_meta($event,'_open_invoice_'.$rsvp_id);
	delete_post_meta($event,'_invoice_'.$rsvp_id);
	
	$row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."rsvpmaker WHERE id=$rsvp_id ",ARRAY_A);
	
	$message = sprintf('<p>%s '.__('payment for','rsvpmaker').' %s %s '.__(' c/o Stripe transaction','rsvpmaker').' %s<br />'.__('Post ID','rsvpmaker').': %s<br />'.__('Time','rsvpmaker').': %s</p>',$charge,$row["first"],$row["last"],$tx_id,$event,date('r'));
add_post_meta($event, '_paypal_log', $message);

}

function rsvpmaker_custom_payment($method,$paid,$rsvp_id,$event,$tx_id=0) {
global $wpdb;
	$charge = $paid;
	$paid_amounts = get_post_meta($event,'_paid_'.$rsvp_id);
	if(!empty($paid_amounts))
	foreach($paid_amounts as $payment)
		$paid += $payment;
	$wpdb->query("UPDATE ".$wpdb->prefix."rsvpmaker SET amountpaid='$paid' WHERE id=$rsvp_id ");
	add_post_meta($event,'_'.$method.'_'.$tx_id,$charge);
	add_post_meta($event,'_paid_'.$rsvp_id,$charge);
	delete_post_meta($event,'_open_invoice_'.$rsvp_id);
	delete_post_meta($event,'_invoice_'.$rsvp_id);
	$log = sprintf('%s amount: %s rsvp_id: %s event: %s, tx: %s',$method,$paid,$rsvp_id,$event,$tx_id=0);
	rsvpmaker_debug_log($log);
}

function rsvpmaker_before_post_display_action (){
	global $post;
	if(isset($post->post_type) && ($post->post_type == 'rsvpmaker'))
	do_action('rsvpmaker_before_display');
}

function add_rsvpmaker_roles() {
$rsvpmakereditor = get_role('rsvpmakereditor');
if(!$rsvpmakereditor)

add_role( 'rsvpmakereditor', 'RSVPMaker Editor', array( 
'read' => true,
'upload_files' => true,
'delete_posts' => true,
'delete_private_posts' => true,
'delete_published_posts' => true,
'edit_posts' => true,
'edit_private_posts' => true,
'edit_published_posts' => true,
'publish_posts' => true,
'delete_others_rsvpmakers' => true,
'delete_rsvpmakers' => true,
'edit_rsvpmakers' => true,
'edit_others_rsvpmakers' => true,
'edit_published_rsvpmakers' => true,
'publish_rsvpmakers' => true,
'read_private_rsvpmakers' => true
 ) );
}

function get_rsvpmaker_meta($post_id,$label,$prefix='_')
{
	global $rsvp_options;
	$meta = get_post_meta($post_id,$prefix.$label,true);
	if(($meta == '') && isset($rsvp_options[$label]))
		$meta = $rsvp_options[$label];
	return $meta;
}

function rsvpmaker_wp_editor($content, $editor_id, $settings = array())
{
if(function_exists('do_blocks')) //gutenberg world
	printf('<p><textarea rows="10" cols="80" id="%s" name=%s>%s</textarea></p>',$editor_id,$editor_id,$content);
else
	wp_editor($content, $editor_id, $settings);
}

function rsvpmaker_dequeue_script() {
   wp_dequeue_script( 'tiny_mce' );
}

function rsvpautog($content) {
	if(strpos($content,'<!-- /wp:paragraph -->'))
		return $content; //already coded for gutenberg
	$content = wpautop($content);
	$content = str_replace('</p>',"</p>\n<!-- /wp:paragraph -->\n",$content);
	$content = str_replace('<p>',"<!-- wp:paragraph -->\n<p>",$content);
	return $content;
}

?>