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
	register_block_type('rsvpmaker/event', ['render_callback' => 'rsvpmaker_one']);	
	register_block_type('rsvpmaker/upcoming', ['render_callback' => 'rsvpmaker_upcoming']);	
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
	
	global $post;
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

		$sked = get_post_meta($post->ID,'_sked',true);
		$rsvpmaker_special = get_post_meta($post->ID,'_rsvpmaker_special',true);
		if(!empty($rsvpmaker_special))
			$top_message = $rsvpmaker_special;
		$top_message = apply_filters('rsvpmaker_ajax_top_message',$top_message);
		$bottom_message = apply_filters('rsvpmaker_ajax_bottom_message',$bottom_message);
		
		if($sked)
		{
			$projected_label = __('Create/update events from template','rsvpmaker');
			$projected_url = admin_url('edit.php?post_type=rsvpmaker&page=rsvpmaker_template_list&t='.$post->ID);
			$template_msg = sked_to_text($sked);
		}
		$template_id = (int) get_post_meta($post->ID,'_meet_recur',true);
		if($template_id)
		{
		$template_label = __('Edit Template','rsvpmaker');
		$template_url = admin_url('post.php?action=edit&post='.$template_id);
		}

		$rsvpmaker_json = array('projected_label' => $projected_label,'projected_url' => $projected_url,'template_label' => $template_label,'template_url' => $template_url);
		
		wp_localize_script( 'rsvpmaker_block-cgb-block-js', 'rsvpmaker_json', $rsvpmaker_json );	
		
$post_id = (empty($post->ID)) ? 0 : $post->ID;
	$date = get_rsvp_date($post_id);
	$end_time = $duration = '';
	if(empty($date))
	{
	$date = date("Y-m-d H:i:s",strtotime('7 pm'));
	$sked = get_post_meta($post_id,'_sked',true);
	if(empty($sked))
		$sked = array();
	}
	else
	{
		$sked = array();
		$duration = get_post_meta($post_id,'_'.$date,true);
		if(!empty($duration))
		{
			if($duration == 'set')
				$end_time =  get_post_meta($post_id,'_end'.$date,true);
				//todo localize, use in gutenberg widget
		}
	}
	
	if(isset($post->post_type) && ($post->post_type == 'rsvpmaker'))
	wp_localize_script( 'rsvpmaker_block-cgb-block-js', 'rsvpmaker_ajax',
        array(
            'ajax_nonce'    => wp_create_nonce('ajax_nonce'),
			'_rsvp_first_date' => $date,
			'_rsvp_on' => (empty(get_post_meta($post->ID,'_rsvp_on',true)) ? 'No' : 'Yes' ),
			'template_msg' => $template_msg,
			'event_id' => $post_id,
			'template_id' => $template_id,
			'special' => $rsvpmaker_special,
			'rsvpmaker_details' => admin_url('edit.php?post_type=rsvpmaker&page=rsvpmaker_details&post_id='.$post_id),
			'top_message' => $top_message,
			'bottom_message' => $bottom_message,
        )
    );
	if(isset($post->post_type) && ($post->post_type == 'rsvpmaker'))
	wp_localize_script( 'rsvpmaker_admin_script', 'rsvpemail_scheduling',admin_url('edit.php?post_type=rsvpemail&page=rsvpmaker_scheduled_email_list&post_id='.$post_id)
    );
				
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