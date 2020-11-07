<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * `wp-blocks`: includes block type registration and related functions.
 *
 * @since 1.0.0
 */

function rsvpmaker_server_block_render(){
	if(wp_is_json_request())
		return;
	register_block_type('rsvpmaker/event', ['render_callback' => 'rsvpmaker_one']);	
	register_block_type('rsvpmaker/upcoming', ['render_callback' => 'rsvpmaker_upcoming']);	
	register_block_type('rsvpmaker/stripecharge', ['render_callback' => 'rsvpmaker_stripecharge']);	
	register_block_type('rsvpmaker/limited', ['render_callback' => 'rsvpmaker_limited_time']);	
	register_block_type('rsvpmaker/formfield', ['render_callback' => 'rsvp_form_text']);	
	register_block_type('rsvpmaker/formtextarea', ['render_callback' => 'rsvp_form_textarea']);	
	register_block_type('rsvpmaker/formselect', ['render_callback' => 'rsvp_form_select']);	
	register_block_type('rsvpmaker/formradio', ['render_callback' => 'rsvp_form_radio']);	
	register_block_type('rsvpmaker/formnote', ['render_callback' => 'rsvp_form_note']);	
	register_block_type('rsvpmaker/guests', ['render_callback' => 'rsvp_form_guests']);
	register_block_type('rsvpmaker/stripe-form-wrapper', ['render_callback' => 'stripe_form_wrapper']);
	register_block_type('rsvpmaker/eventlisting', ['render_callback' => 'event_listing']);
	register_block_type('rsvpmaker/rsvpdateblock', ['render_callback' => 'rsvpdateblock']);
	register_block_type('rsvpmaker/upcoming-by-json', ['render_callback' => 'rsvpjsonlisting']);
	register_block_type('rsvpmaker/embedform', ['render_callback' => 'rsvpmaker_form']);	
	register_block_type('rsvpmaker/schedule', ['render_callback' => 'rsvpmaker_daily_schedule']);
	register_block_type('rsvpmaker/future-rsvp-links', ['render_callback' => 'future_rsvp_links']);
	register_block_type('rsvpmaker/submission', ['render_callback' => 'rsvpmaker_submission']);
}

function rsvpmaker_check_string($value) {
	if(is_string($value))
		return $value;
	return '';
}

add_action( 'init', function(){

$args = array(
		'object_subtype' => 'rsvpmaker',
 		'type'		=> 'string',
		 'single'	=> true,
		 'default' => '',
		 'show_in_rest'	=> true,
		 'sanitize_callback' => 'rsvpmaker_check_string',
		 'auth_callback' => function() {
			return current_user_can('edit_posts');
		}
	);
	register_meta( 'post', '_rsvp_dates', $args );
	register_meta( 'post', '_rsvp_to', $args );
	register_meta( 'post', '_rsvp_max', $args );
	register_meta( 'post', '_rsvp_show_attendees', $args );
	register_meta( 'post', '_rsvp_instructions', $args );
	register_meta( 'post', 'simple_price', $args );
	register_meta( 'post', 'simple_price_label', $args );
	register_meta( 'post', 'venue', $args );
	$date_fields = array('_firsttime','_template_start_hour','_template_start_minutes','_sked_hour','_sked_minutes','_sked_stop','_sked_duration','_sked_duration','_sked_end');
	$template_fields = array('_sked_Varies','_sked_First','_sked_Second','_sked_Third','_sked_Fourth','_sked_Last','_sked_Every','_sked_Sunday','_sked_Monday','_sked_Tuesday','_sked_Wednesday','_sked_Thursday','_sked_Friday','_sked_Saturday');
	foreach($date_fields as $field)
		register_meta( 'post', $field, $args );

	$args = array(
		'object_subtype' => 'rsvpmaker',
			'type'		=> 'string',
			'single'	=> true,
			'default' => '12:00',
			'show_in_rest'	=> true,
			'sanitize_callback' => 'rsvpmaker_check_string',
			'auth_callback' => function() {
			return current_user_can('edit_posts');
		}
	);
	register_meta( 'post', '_endfirsttime', $args );
	
	$args = array(
		'object_subtype' => 'rsvpmaker',
 		'type'		=> 'integer',
		 'single'	=> true,
		 'default' => 0,
		 'show_in_rest'	=> true,
		 'auth_callback' => function() {
			return current_user_can('edit_posts');
		}
	);
	register_meta( 'post', 'rsvp_tx_template', $args );
		
	$args = array(
		'object_subtype' => 'rsvpmaker',
 		'type'		=> 'boolean',
		 'single'	=> true,
		 'default' => false,
		 'show_in_rest'	=> true,
		 'auth_callback' => function() {
			return current_user_can('edit_posts');
		}
	);
	foreach($template_fields as $field)
		register_meta( 'post', $field, $args );
	register_meta( 'post', '_rsvp_on', $args );
	register_meta( 'post', '_add_timezone', $args );
	register_meta( 'post', '_convert_timezone', $args ); 
	register_meta( 'post', '_calendar_icons', $args );
    register_meta( 'post', '_rsvp_end_display', $args );
	register_meta( 'post', '_rsvp_rsvpmaker_send_confirmation_email', $args );
	register_meta( 'post', '_rsvp_confirmation_after_payment', $args );
	register_meta( 'post', '_rsvp_confirmation_include_event', $args );
	register_meta( 'post', '_rsvp_count', $args );
	register_meta( 'post', '_rsvp_yesno', $args );
	register_meta( 'post', '_rsvp_captcha', $args );
	//register_meta( 'post', '_rsvp_timezone_string', $args );
	register_meta( 'post', '_rsvp_login_required', $args );
	register_meta( 'post', '_rsvp_form_show_date', $args );
});

function rsvpjsonlisting ($atts) {
if(empty($atts['url']))
	return;
$url = $atts['url'];
$limit = (empty($atts['limit'])) ? 10: (int) $atts['limit'];
$morelink = (empty($atts['morelink'])) ? '' : $atts['morelink'];
$slug = rand(0,1000000);
ob_start();
?>
	<div id="rsvpjsonwidget-<?php echo $slug; ?>">Loading ...</div>
<script>
var jsonwidget<?php echo $slug; ?> = new RSVPJsonWidget('rsvpjsonwidget-<?php echo $slug; ?>','<?php echo $url; ?>',<?php echo $limit; ?>,'<?php echo $morelink; ?>');
</script>
<?php
return ob_get_clean();
}

add_action('init','rsvpmaker_server_block_render');

function rsvpmaker_block_cgb_block_assets() {
	// Styles.
	global $post;
	wp_enqueue_style(
		'rsvpmaker_block-cgb-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		array( 'wp-blocks' ), // Dependency to include the CSS after it.
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: filemtime — Gets file modification time.
	);
} // End function rsvpmaker_block_cgb_block_assets().

// Hook: Frontend assets.
add_action( 'enqueue_block_assets', 'rsvpmaker_block_cgb_block_assets' );

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * `wp-blocks`: includes block type registration and related functions.
 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
 * `wp-i18n`: To internationalize the block's text.
 *
 * @since 1.0.0
 */
function rsvpmaker_block_cgb_editor_assets() {
	// Scripts.
	global $post;
	wp_enqueue_script(
		'rsvpmaker_block-cgb-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element' ), // Dependencies, defined above.
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	wp_localize_script( 'rsvpmaker_block-cgb-block-js', 'rsvpmaker_type', $post->post_type);
	wp_localize_script( 'rsvpmaker_block-cgb-block-js', 'rsvpmaker_json_url', site_url('/wp-json/rsvpmaker/v1/'));
	if($post->post_type == 'rsvpemail')
	wp_localize_script( 'rsvpmaker_block-cgb-block-js', 'related_documents', get_related_documents ($post->ID,'rsvpemail'));

	global $post, $rsvp_options, $current_user;
	$template_id = 0;
	if(is_admin() && ($post->post_type == 'rsvpmaker') && isset($_GET['action']) && $_GET['action'] == 'edit')
		{
		$projected_label = '';
		$projected_url = '';
		$template_label = '';
		$template_url = '';
		$template_msg = '';
		$top_message = '';
		$bottom_message= '';
		$complex_pricing = rsvp_complex_price($post->ID);
		$complex_template = get_post_meta($post->ID,'complex_template',true);
		$chosen_gateway = get_rsvpmaker_payment_gateway ();
		$edit_payment_confirmation = admin_url('?payment_confirmation&post_id='.$post->ID);
		$sked = get_template_sked($post->ID);// get_post_meta($post->ID,'_sked',true);
		$rsvpmaker_special = get_post_meta($post->ID,'_rsvpmaker_special',true);
		if(!empty($rsvpmaker_special))
			$top_message = $rsvpmaker_special;
		$top_message = apply_filters('rsvpmaker_ajax_top_message',$top_message);
		$bottom_message = apply_filters('rsvpmaker_ajax_bottom_message',$bottom_message);
		$confirmation_options = get_confirmation_options();
		
		if($sked)
		{
			$projected_label = __('Create/update events from template','rsvpmaker');
			$projected_url = admin_url('edit.php?post_type=rsvpmaker&page=rsvpmaker_template_list&t='.$post->ID);
			$template_msg = sked_to_text($sked);
		}
		$template_id = (int) get_post_meta($post->ID,'_meet_recur',true);
		if($template_id && !$sked)
		{
		$template_label = __('Edit Template','rsvpmaker');
		$template_url = admin_url('post.php?action=edit&post='.$template_id);
		}
		
	$post_id = (empty($post->ID)) ? 0 : $post->ID;
	$date = get_rsvp_date($post_id);
	$datecount = sizeof(get_rsvp_dates($post_id));
	$end = get_post_meta($post_id,'_end'.$date,true);
	if(empty($end))
		$end = rsvpmaker_date('H:i',rsvpmaker_strtotime($date." +1 hour"));
	$duration = '';
	if(empty($date))
	{
	//$date = rsvpmaker_date("Y-m-d H:i:s",rsvpmaker_strtotime('7 pm'));
	$sked = get_template_sked($post_id);//get_post_meta($post_id,'_sked',true);
	if(empty($sked))
		$sked = array();
	}
	else
	{
		$sked = array();
		$duration = get_post_meta($post_id,'_'.$date,true);
		if(!empty($duration))
		{
			$diff = rsvpmaker_strtotime($duration) - rsvpmaker_strtotime($date);
			$duration = rsvpmaker_date('H:i',$diff);
		}
	}

	$confirm = rsvp_get_confirm($post->ID,true);
	$confirm_edit_post = (current_user_can('edit_post',$confirm->ID));
	$excerpt = strip_tags($confirm->post_content);
	$excerpt = (strlen($excerpt) > 100) ? substr($excerpt, 0, 100).' ...' : $excerpt;
	$confirmation_type = '';
	if($confirm->post_parent == 0)
		$confirmation_type =__('Message is default from settings','rsvpmaker');
	elseif($confirm->post_parent != $post->ID)
		$confirmation_type = __('Message inherited from template','rsvpmaker');

	$form_id = get_post_meta($post->ID,'_rsvp_form',true);
	if(empty($form_id))
		$form_id = (int) $rsvp_options['rsvp_form'];
	$fpost = get_post($form_id);
	//rsvpmaker_debug_log($form_id);
	$form_edit = admin_url('post.php?action=edit&post='.$fpost->ID.'&back='.$post->ID);
	$form_customize = admin_url('?post_id='. $post->ID. '&customize_form='.$fpost->ID);
	$guest = (strpos($fpost->post_content,'rsvpmaker-guests')) ? 'Yes' : 'No';
	$note = (strpos($fpost->post_content,'name="note"') || strpos($fpost->post_content,'formnote')) ? 'Yes' : 'No';
	preg_match_all('/\[([A-Za-z0_9_]+)/',$fpost->post_content,$matches);
	if(!empty($matches[1]))
	foreach($matches[1] as $match)
		$fields[$match] = $match;
	preg_match_all('/"slug":"([^"]+)/',$fpost->post_content,$matches);
	if(!empty($matches[1]))
	foreach($matches[1] as $match)
		$fields[$match] = $match;	
	$merged_fields = (empty($fields)) ? '' : implode(', ',$fields);
	$form_fields = sprintf('Fields: %s, Guests: %s, Note field: %s',$merged_fields,$guest,$note);
	$form_type = '';
	$form_edit_post = (current_user_can('edit_post',$fpost->ID));
	$form_edit_post = true;
	if($fpost->post_parent == 0)
		$form_type = __('Form is default from settings','rsvpmaker');//printf('<div id="editconfirmation"><a href="%s" target="_blank">Edit</a> (default from Settings)</div><div><a href="%s" target="_blank">Customize</a></div>',$edit,$customize);
	elseif($fpost->post_parent != $post->ID)
		$form_type = __('Form inherited from template','rsvpmaker');//printf('<div id="editconfirmation"><a href="%s" target="_blank">Edit</a> (default from Settings)</div><div><a href="%s" target="_blank">Customize</a></div>',$edit,$customize);
	$email_templates_array = get_rsvpmaker_email_template();
	if($email_templates_array)
	foreach($email_templates_array as $index => $template) {
		if($index > 0)
		$confirmation_email_templates[] = array('label' => $template['slug'], 'value' => $index);
	}

	if(isset($post->post_type) && ($post->post_type == 'rsvpmaker'))
	{
		$related_documents = get_related_documents ();
		//rsvpmaker_debug_log($related_documents,'related documents for gutenberg');
		wp_localize_script( 'rsvpmaker_block-cgb-block-js', 'rsvpmaker_ajax',
        array(
			'projected_label' => $projected_label,'projected_url' => $projected_url,
			'template_label' => $template_label,
			'template_url' => $template_url,            'ajax_nonce'    => wp_create_nonce('ajax_nonce'),
			'_rsvp_first_date' => $date,
			'_rsvp_count' => $datecount,
			//'_rsvp_end_display' => get_post_meta($post_id,'_'.$date,true),
			'_rsvp_end' => $end,
			'_rsvp_on' => (empty(get_post_meta($post->ID,'_rsvp_on',true)) ? 'No' : 'Yes' ),
			'template_msg' => $template_msg,
			'event_id' => $post_id,
			'template_id' => $template_id,
			'special' => $rsvpmaker_special,
			'rsvpmaker_details' => admin_url('edit.php?post_type=rsvpmaker&page=rsvpmaker_details&post_id='.$post_id),
			'top_message' => $top_message,
			'bottom_message' => $bottom_message,
			'confirmation_excerpt' => $excerpt,
			'confirmation_edit' => admin_url('post.php?action=edit&post='.$confirm->ID.'&back='.$post->ID),
			'confirmation_customize' => admin_url('?post_id='. $post->ID. '&customize_rsvpconfirm='.$confirm->ID.'#confirmation'),
			'reminders' => admin_url('edit.php?post_type=rsvpmaker&page=rsvp_reminders&message_type=confirmation&post_id='.$post->ID),
			'confirmation_type' => $confirmation_type,
			'confirm_edit_post' => $confirm_edit_post,
			'rsvp_tx_template_choices' => $confirmation_email_templates,
			'form_fields' => $form_fields,
			'form_edit' => $form_edit,
			'form_customize' => $form_customize,
			'form_type' => $form_type,
			'form_edit_post' => $form_edit_post,			
			'complex_pricing' => $complex_pricing,		
			'complex_template' => $complex_template,
			'edit_payment_confirmation' => $edit_payment_confirmation,
			'related_document_links' => $related_documents,
			'form_links' => get_form_links($post_id, $template_id, 'rsvp_options'),
			'confirmation_links' => get_conf_links($post_id, $template_id, 'rsvp_options'),
			)
	);

	}

	if(isset($post->post_type) && ($post->post_type == 'rsvpmaker')) {
		wp_localize_script( 'rsvpmaker_admin_script', 'rsvpemail_scheduling',admin_url('edit.php?post_type=rsvpemail&page=rsvpmaker_scheduled_email_list&post_id='.$post_id)
    );

	}
				
		}
	
	// Styles.
	wp_enqueue_style(
		'rsvpmaker_block-cgb-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' )
	);

} // End function rsvpmaker_block_cgb_editor_assets().

// Hook: Editor assets.
add_action( 'enqueue_block_editor_assets', 'rsvpmaker_block_cgb_editor_assets' );

//add_action( 'enqueue_block_editor_assets', 'rsvpmaker_block_hide_assets', 99 );

//if this is an rsvpmaker post, hide the rsvpmaker/upcoming and rsvpmaker/event blocks (no events within events)

function rsvpmaker_block_hide_assets () {
global $post;
if(empty($post->post_type))
	return;
if($post->post_type != 'rsvpmaker')
	return;
	wp_enqueue_script(
		'rsvpmaker-blacklist-blocks',
		plugins_url( 'dist/hide.js', dirname(__FILE__) ),
		array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post', 'rsvpmaker_block-cgb-block-js' )
	);
}

function rsvpmaker_limited_time ($atts, $content) {
	global $post;
	$debug = '';
	if(isset($_GET['debug']))
		$debug .= ' attributes: '. var_export($atts, true);
	if(empty($atts['start_on']) && empty($atts['end_on']))
		return $content.$debug; // no parameters set
	
	$now = time();
	if(!empty($atts['start_on']) && !empty($atts['start']))
	{
	//test to see if we're before the start time
	$start = rsvpmaker_strtotime($atts['start']);
	if(isset($_GET['debug']))
		$debug .= sprintf('<p>Start time %s = %s, now = %s</p>',$atts['start'],$start,$now);
	if($now < $start)
		{
		
		return $debug;
		}
	}
	if(!empty($atts['end_on']) && !empty($atts['end']))
	{
	//test to see if we're past the end time
	$end = rsvpmaker_strtotime($atts['end']);
	$pattern = '/<!-- wp:rsvpmaker\/limited.+"end":"'.$atts["end"].'".+(\/wp:rsvpmaker\/limited -->)/sU';
	if(isset($_GET['debug']))
	{
		$debug .= sprintf('<p>End time %s = %s, now = %s</p>',$atts['end'],$end,$now);
		preg_match($pattern,$post->post_content,$matches);
		if(empty($matches[0]))
			$debug .= 'Regex failed';
		else
			$debug .= htmlentities($matches[0]);
	}
	if($now > $end)
	{
		if(!empty($atts['delete_expired']))
		{
		$update['ID'] = $post->ID;
		$update['post_content'] = preg_replace($pattern,'',$post->post_content);
		if(!empty($update['post_content']))
			wp_update_post($update);
		else
			$debug .= 'Preg replace came back empty';
		}
		
		return $debug;
	}
		
	}

return $content.$debug;
}
