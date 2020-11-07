<?php
add_action('init','rsvpmaker_init_router');
add_action('init','rsvp_options_defaults',1);
add_action( 'add_meta_boxes', 'rsvplanding_register_meta_boxes' );
add_action( 'admin_bar_menu', 'toolbar_rsvpmaker', 99 );

add_action( 'admin_enqueue_scripts', 'rsvpmaker_admin_enqueue' );

add_action('admin_head','rsvpmaker_upcoming_admin_js');
add_action('admin_head','rsvpmaker_template_admin_title');

add_action( 'admin_init', 'rsvpmaker_plugin_add_privacy_policy_content' );
add_action('admin_init','rsvpmaker_template_checkbox_post');

add_action('admin_init','rsvpmaker_add_one');
add_action('admin_init','rsvpmaker_editors');
add_action( 'admin_init', 'add_rsvpemail_caps');
add_action('admin_init','rsvp_csv');
add_action('admin_init','additional_editors_setup');
add_action('admin_init','rsvpmaker_setup_post');
add_action('admin_init','rsvpevent_to_email');	
add_action( 'admin_init', 'add_rsvpemail_caps');
add_action('admin_init','customize_rsvp_form');

add_action('admin_menu', 'my_events_menu');
add_action('admin_menu', 'my_rsvpemails_menu');
add_action('admin_menu', 'my_rsvpemail_menu');
add_action('admin_menu', 'rsvpmaker_admin_menu');

add_action('admin_notices', 'rsvpmaker_admin_notice');
add_action('current_screen','rsvp_print',999);
add_action('export_wp','export_rsvpmaker');
add_action('import_end','import_rsvpmaker');
add_action('log_paypal','log_paypal');


add_action('loop_end','rsvpmaker_archive_loop_end');
add_action('manage_posts_extra_tablenav','rsvpmaker_sort_message');
add_action('manage_posts_custom_column', 'rsvpmaker_custom_column', 10, 2);
add_action( 'pre_get_posts', 'rsvpmaker_archive_pages' );
add_action( 'plugins_loaded', 'rsvpmaker_load_plugin_textdomain' );
add_action('plugins_loaded','rsvpmaker_gutenberg_check');
add_action( 'rest_api_init','rest_api_init_rsvpmaker');

add_action('rsvp_daily_reminder_event', 'rsvp_daily_reminder');
add_action('rsvpmaker_cron_email_preview','rsvpmaker_cron_email_preview');
add_action('rsvpmaker_cron_email','rsvpmaker_cron_email_send');

add_action('rsvpmaker_email_list_okay','rsvpmaker_email_list_okay',10,1);
add_action('rsvpmaker_replay_email','rsvpmaker_replay_email',10,3);
add_action('rsvpmaker_send_reminder_email','rsvpmaker_send_reminder_email',10,2);


add_action( 'save_post', 'rsvplanding_save_meta_box' );
add_action('save_post','');
//stripe
add_action('sc_after_charge','rsvpmaker_sc_after_charge');

add_action("template_redirect", 'rsvpemail_template_redirect');

add_action('user_register','RSVPMaker_register_chimpmail');
add_action('widgets_init', function() {return register_widget("CPEventsWidget");});
add_action('widgets_init', function() {return register_widget("RSVPTypeWidget");});
add_action('widgets_init', function() {return register_widget("RSVPMakerByJSON");});
add_action('wp','clear_rsvp_cookies');
add_action('wp', 'rsvp_reminder_activation');
add_action('wp','rsvpmaker_before_post_display_action');

add_action('wp_enqueue_scripts','rsvpmaker_event_scripts',10000);

//make sure new rules will be generated for custom post type - flush for admin but not for regular site visitors
if(!isset($rsvp_options["flush"]))
	add_action('admin_init','flush_rewrite_rules');
if(!isset($rsvp_options["flush"]))
	add_action('admin_init','flush_rewrite_rules');

if(isset($_GET["clean_duplicate_dates"]))
	add_action('init','rsvpmaker_duplicate_dates');

if(isset($_GET["ical"])) 
	add_action('wp','rsvpmaker_to_ical');
if(isset($rsvp_options["social_title_date"]) && $rsvp_options["social_title_date"])
	add_action( 'wp_head', 'rsvpmaker_facebook_meta', 999 );
if(isset($_GET['rsvp_reminders']))
	add_action( 'wp_print_scripts', 'rsvpmaker_dequeue_script', 100 );
if(isset($rsvp_options["dashboard"]) && !empty($rsvp_options["dashboard"]) )
	add_action('wp_dashboard_setup', 'rsvpmaker_add_dashboard_widgets' );

add_action( 'wp_ajax_rsvpmaker_date', 'ajax_rsvpmaker_date_handler' );
add_action( 'wp_ajax_rsvpmaker_meta', 'ajax_rsvpmaker_meta_handler' );
add_action( 'wp_ajax_rsvpmaker_dateformat', 'ajax_rsvpmaker_dateformat_handler' );
add_action('wp_ajax_rsvpmaker_paypal_config','rsvpmaker_paypal_config_ajax');
add_action('wp_ajax_rsvpmaker_dismissed_notice_handler', 'rsvpmaker_ajax_notice_handler' );
add_action( 'wp_ajax_rsvpmaker_template', 'ajax_rsvpmaker_template_handler' );

add_action('wp_login','rsvpmaker_data_check');

function rsvpmaker_init_router () {
add_rsvpmaker_roles();
create_rsvpemail_post_type();
if(isset($_REQUEST['paymentAmount']))
	paypal_start();
if(isset($_GET['rsvpmaker_cron_email_preview']))
previewtest();//email preview
rsvp_options_defaults();
rsvpmaker_create_post_type();
rsvpmaker_localdate();
if(isset($_GET["rsvpmaker_placeholder"]))
	rsvpmaker_placeholder_image();
if(isset($_POST["replay_rsvp"]))
	save_replay_rsvp();
if(isset($_POST["yesno"]) || isset($_POST["withdraw"]))
	save_rsvp();
if(isset($_GET['show_rsvpmaker_included_styles']))
	show_rsvpmaker_included_styles();
}

?>