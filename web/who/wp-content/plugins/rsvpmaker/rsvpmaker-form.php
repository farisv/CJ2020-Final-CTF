<?php

function upgrade_rsvpform ($future = true, $rsvp_form_post=0) {

global $rsvp_options;

$newform = true;



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



if($rsvp_form_post)

	{

	$post = get_post($rsvp_form_post);

	if(!empty($post) && ($post->post_status == 'publish'))

	{

		$rsvp_options['rsvp_form'] = $rsvp_form_post;

		wp_update_post(array('ID' => $rsvp_form_post,'post_title'=>'RSVP Form:Default','post_content'=>$form ));

		$newform = false;	

	}

	}



if($newform) {

	$rsvp_options['rsvp_form'] = wp_insert_post(array('post_title'=>'RSVP Form:Default','post_content'=>$form,'post_status'=>'publish','post_type'=>'rsvpmaker','post_parent' => 0));

	update_option('RSVPMAKER_Options',$rsvp_options);

	update_post_meta($rsvp_options['rsvp_form'],'_rsvpmaker_special','RSVP Form');

	}

if($future)

{

	$results = get_future_events();

	if($results)

	foreach($results as $post)

	{

		update_post_meta($post->ID,'_rsvp_form',$rsvp_options['rsvp_form']);

	}

}

return $rsvp_options['rsvp_form'];

}



function customize_rsvp_form () {

global $current_user, $wpdb, $rsvp_options;

if(current_user_can('manage_options') && isset($_GET['upgrade_rsvpform'])) {

	$id = upgrade_rsvpform();

}	

if(isset($_GET['rsvpcz']) && isset($_GET['post_id'])) {

	$meta_key = $_GET['rsvpcz'];

	$parent = (int) $_GET['post_id'];

	$title = $_GET['title'].':'.$parent;

	$content = '';

	if(isset($_GET['source'])) {

		$source = (int) $_GET['source'];

		if($source) {

			$old = get_post($source);

			$content = (empty($old->post_content)) ? '' : $old->post_content;

		}

	}

	$new["post_title"] = $title;

	$new["post_parent"] = $parent;

	$new["post_status"] = 'publish';

	$new["post_type"] = ($meta_key == '_rsvp_form') ? 'rsvpmaker' : 'rsvpemail';

	$new["post_author"] = $current_user->ID;

	$new["post_content"] = $content;

	rsvpmaker_debug_log($new,'customize document');

	$id = wp_insert_post($new);
	rsvpmaker_debug_log($id,'customize document ID');

	if(!$id)

		return;

	if($source)

		rsvpmaker_copy_metadata($source, $id);

	update_post_meta($parent,$meta_key,$id);

	if($meta_key == '_rsvp_form')

		update_post_meta($id,'_rsvpmaker_special','RSVP Form');//important to make form blocks available

	else

		update_post_meta($id,'_rsvpmaker_special',$title);

}

if(isset($_GET['customize_rsvpconfirm'])) {

	$parent = (int) $_GET['post_id'];

	$source = (int) get_post_meta($parent,'_rsvp_confirm',true);

	$old = get_post($source);

	if($old->post_parent) //false for default message

		$id = $old->ID; //if link called after custom post already created

	elseif($old)

	{

	$new["post_title"] = "Confirmation:".$parent;

	$new["post_parent"] = $parent;

	$new["post_status"] = 'publish';

	$new["post_type"] = 'rsvpemail';

	$new["post_author"] = $current_user->ID;

	$new["post_content"] = $old->post_content;

	$id = wp_insert_post($new);

	if($id)

		update_post_meta($parent,'_rsvp_confirm',$id);		

		update_post_meta($id,'_rsvpmaker_special','Confirmation Message');

	}

}

if(isset($_POST['create_reminder_for'])) {

	$parent = $post_id = (int) $_POST['create_reminder_for'];
	$event = get_post($post_id);
	$subject = $event->post_title;

	$hours = (int) $_REQUEST["hours"];

	$key = '_rsvp_reminder_msg_'.$hours;

	$copy_from = (int) $_POST['copy_from'];

	$content = '';

	if($copy_from)

	{

		$copy = get_post($copy_from);

		$content = $copy->post_content;

	}

	$id = get_post_meta($parent,$key,true);

	if(!$id) {

		$label = ($hours > 0) ? __('Follow Up','rsvpmaker') : __('Reminder','rsvpmaker');

		$title = $label.': '.get_the_title($post_id).' [datetime]';

		$new["post_title"] = $title;

		$new["post_parent"] = $post_id;

		$new["post_status"] = 'publish';

		$new["post_type"] = 'rsvpemail';

		$new["post_author"] = $current_user->ID;

		$new["post_content"] = $content;

		$id = wp_insert_post($new);	

	}

	if($id) {

		update_post_meta($parent,$key,$id);

		update_post_meta($id,'_rsvpmaker_special','Reminder ('.$hours.' hours) '.$subject);

		if(isset($_POST['paid_only']))

			update_post_meta($id,'paid_only_confirmation',1);

	if(rsvpmaker_is_template($post_id))

	{

		rsvpmaker_template_reminder_add($hours,$post_id);

		rsvpautorenew_test (); // will add to the next scheduled event associated with template

		//header('Location: '.admin_url('edit.php?page=rsvp_reminders&post_type=rsvpmaker&template_reminder=1&post_id=').$post_id);

		//exit();

	}

	else

	{

		$start_time = rsvpmaker_strtotime( get_rsvp_date($post_id) );

		rsvpmaker_reminder_cron($hours, $start_time, $post_id);

	}



	}

}

if(isset($_GET['payment_confirmation'])) {

	$parent = (int) $_GET['post_id'];

	$id = get_post_meta($parent,'payment_confirmation_message',true);

	$source = (isset($_GET['source'])) ? (int) $_GET['source'] : 0;

	if(empty($id) || $source)

	{

	$new["post_title"] = "Payment Confirmation:".$parent;

	$new["post_parent"] = $parent;

	$new["post_status"] = 'draft';

	$new["post_type"] = 'rsvpemail';

	$new["post_author"] = $current_user->ID;

	if($source) {

		$source_post = get_post($source);

		$new["post_content"] = $source_post->post_content;

	}

	else

		$new["post_content"] = '';

	$id = wp_insert_post($new);

	if($id)

		update_post_meta($parent,'payment_confirmation_message',$id);		

		update_post_meta($id,'_rsvpmaker_special','Payment Confirmation Message');

	}

}



if(isset($_GET['customize_form'])) {

	$parent = (int) $_GET['post_id'];

	$source = (int) get_post_meta($parent,'_rsvp_form',true);

	$old = get_post($source);

	if($old->post_parent) //false for default form

		$id = $old->ID; //if link called after custom post already created

	elseif($old)

	{

	$new["post_title"] = "RSVP Form:".$parent;

	$new["post_parent"] = $parent;

	$new["post_status"] = 'publish';

	$new["post_type"] = 'rsvpmaker';

	$new["post_author"] = $current_user->ID;

	$new["post_content"] = $old->post_content;

	//print_r($new);

	remove_all_filters("content_save_pre"); //don't allow form fields to be filtered out

	$id = wp_insert_post($new);

	//printf('<p>Insert post returned %s',$id);

	if($id)

		{

			update_post_meta($parent,'_rsvp_form',$id);

			update_post_meta($id,'_rsvpmaker_special','RSVP Form');

		}

	}

}



if(!empty($id)) {

	$destination = admin_url('post.php?action=edit&post=').$id;

	if(!empty($post_id))

		$destination .= '&back='.$post_id;

	header('Location: '.$destination);

	exit();

}

	

}



function rsvp_field_apply_default($content,$slug,$default) {

	if(strpos($content,'type="text"') || strpos($content,'type="email"'))

		$content = str_replace('value=""','value="'.$default.'"',$content);

	elseif(strpos($content,'</textarea>'))

		$content = str_replace('</textarea>',$default.'</textarea>',$content);

	$find = 'value="'.$default.'"';

	if(strpos($content,'</select>'))

		$content = str_replace($find,$find.' selected="selected"',$content);

	elseif(strpos($content,'type="radio"'))

		$content = str_replace($find,$find.' checked="checked"',$content);

	return $content;

}



function rsvp_form_text($atts, $content) {

	global $post;

	global $rsvp_required_field;

	if(empty($atts["slug"]) || empty($atts["label"]))

		return;

	$slug = $atts["slug"];

	$label = $atts["label"];

	$required = '';

	if(isset($atts["required"]) || isset($atts["require"]))

	{

		$rsvp_required_field[$slug] = $slug;

		$required = 'required';

	}

	$content = sprintf('<div class="wp-block-rsvpmaker-formfield %srsvpblock"><p><label>%s:</label> <span class="%s"><input class="%s" type="text" name="profile[%s]" id="%s" value=""/></span></p></div>',$required,$label,$required,$slug,$slug,$slug);

	if($slug == 'email')

		$content .= '<div id="rsvp_email_lookup"></div>';

	return rsvp_form_field($atts,$content);

}



function rsvp_form_textarea($atts, $content = '') {

	global $post;

	global $rsvp_required_field;

	if(empty($atts["slug"]) || empty($atts["label"]))

		return;

	$slug = $atts["slug"];

	$label = $atts["label"];

	$rows = (empty($atts['rows'])) ? '3' : $atts['rows'];

	$required = '';

	$content = sprintf('<div class="wp-block-rsvpmaker-formtextarea %srsvpblock"><p><label>%s:</label></p><p><textarea rows="%d" class="%s" type="text" name="profile[%s]" id="%s"></textarea></p></div>',$required,$label,$required,$rows,$slug,$slug,$slug);

	return rsvp_form_field($atts,$content);

}



function rsvp_form_select($atts, $content = '') {

	global $post;

	global $rsvp_required_field;

	if(empty($atts["slug"]) || empty($atts["label"]))

		return;

	$slug = $atts["slug"];

	$label = $atts["label"];

	$required = '';

	$choices = '';

	if(isset($atts['choicearray']) && !empty($atts['choicearray']) && is_array($atts['choicearray']))

	foreach($atts['choicearray'] as $choice)

		$choices .= sprintf('<option value="%s">%s</option>',$choice,$choice);



	$content = sprintf('<div class="wp-block-rsvpmaker-formselect %srsvpblock"><p><label>%s:</label> <span><select class="%s" name="profile[%s]" id="%s">%s</select></span></p></div>',$required,$label,$slug,$slug,$slug,$choices);

	return rsvp_form_field($atts,$content);

}



function rsvp_form_radio($atts, $content = '') {

	global $post;

	global $rsvp_required_field;

	if(empty($atts["slug"]) || empty($atts["label"]))

		return;

	$slug = $atts["slug"];

	$label = $atts["label"];	$choices = '';

	if(isset($atts['choicearray']) && !empty($atts['choicearray']) && is_array($atts['choicearray']))

	foreach($atts['choicearray'] as $choice)

		$choices .= sprintf('<span><input type="radio" class="%s" name="profile[%s]" id="%s" value="%s"/> %s </span>',$slug,$slug,$slug,$choice,$choice);

	$required = '';

	$content = sprintf('<div class="wp-block-rsvpmaker-formradio %srsvpblock"><p><label>%s:</label> %s</p></div>',$required,$label,$choices);

	return rsvp_form_field($atts,$content);

}



function rsvp_form_field($atts, $content = '') {

	rsvpmaker_debug_log($atts,'form field render atts');

	rsvpmaker_debug_log($content,'form field render $content');

	//same for all field types

	global $post;

	global $rsvp_required_field;

	if(empty($atts["slug"]) || empty($atts["label"]))

		return;

	$slug = $atts["slug"];

	$label = $atts["label"];

	update_post_meta($post->ID,'rsvpform'.$slug,$label);

	global $profile;

	//$profile = array('first' => 'David','last' => 'Carr','meal'=>'Chicken','dessert'=>'pie','email'=>'david@carrcommunications.com');

	global $guestprofile;

	if(!empty($guestprofile))

		$profile = $guestprofile;

	if(!empty($atts['guestform'])) // if not set, default is true

		rsvp_add_guest_field($content,$slug);

	if(empty($profile[$slug]))

		return $content;//.$slug.': no default'.var_export($profile,true);

	$default = $profile[$slug];

	return rsvp_field_apply_default($content,$slug,$default);

}



function rsvp_form_note ($atts = array()) {

	$label = (empty($atts['label'])) ? 'Note' : esc_html($atts['label']);

	return sprintf('<p>%s:<br><textarea name="note"></textarea></p>',$label);

}



function rsvp_guest_content($content) {

	$content = str_replace(']"','][]"',$content);

	$content = str_replace('"profile','"guest',$content);

	$content = preg_replace('/id="[^"]+"/','',$content);//no ids on guest fields

	$content = str_replace('class="required"','',$content);//no required fields

	return $content;

}



function rsvp_add_guest_field($content,$slug) {

	global $guestfields;

	$guestfields[$slug] = rsvp_guest_content($content);

}



function rsvp_form_guests($atts, $content) {

if(is_admin())

	return $content;

$content = '';//ignore content

global $guestfields;

global $guestprofile;

$shared = '';

$label = (isset($atts['label'])) ? $atts['label'] : __('Guest','rsvpmaker');



if(is_array($guestfields))

	foreach($guestfields as $slug => $field)

		$shared .= $field;

$template = '<div class="guest_blank" id="first_blank"><p><strong>'.__('Guest','rsvpmaker').' ###</strong></p>'.$shared . $content.'</div>';//fields shared from master form, plus added fields

	

$addmore = (isset($atts['addmore'])) ? $atts['addmore'] : __('Add more guests','rsvpmaker');

global $wpdb;

global $blanks_allowed;

global $master_rsvp;

//$master_rsvp = 4;//test data

$wpdb->show_errors();

$output = '';

$count = 1; // reserve 0 for host

$max_party = (isset($atts["max_party"])) ? (int) $atts["max_party"] : 0;



if(isset($master_rsvp) && $master_rsvp)

{

$guestsql = "SELECT * FROM ".$wpdb->prefix."rsvpmaker WHERE master_rsvp=".$master_rsvp.' ORDER BY id';

if($results = $wpdb->get_results($guestsql, ARRAY_A) )

	{

	foreach($results as $row)

		{

			$output .= sprintf('<div class="guest_blank"><p><strong>%s %d</strong></p>',$label,$count)."\n";

			$guestprofile = rsvp_row_to_profile($row);

			$shared = '';

			if(is_array($guestfields))

				foreach($guestfields as $slug => $field)

				{

					if(!empty($guestprofile[$slug]))

						$shared .= rsvp_field_apply_default($field,$slug,$guestprofile[$slug]);

					else

						$shared .= $field;

				}

		

			$output .= $shared.do_blocks($content);

			$output = str_replace('[]','['.$count.']',$output);

			$output .= sprintf('<div><input type="checkbox" name="guestdelete[%s]" value="%s" /> '.__('Delete Guest','rsvpmaker').' %d</div><input type="hidden" name="guest[id][%s]" value="%s">',$row["id"],$row["id"], $count,$count,$row["id"]);

			$count++;

		}

	}

}



$output .= $template;

//$output .= '<script type="text/javascript"> var guestcount ='.$count.'; </script>';



$max_guests = $blanks_allowed + $count;



if($max_party)

	$max_guests = ($max_party > $max_guests) ? $max_guests : $max_party; // use the lower limit



// now the blank field

if($blanks_allowed < 1)

	return $output.'<p><em>'.esc_html(__('No room for additional guests','rsvpmaker')).'</em><p>'; // if event is full, no additional guests

elseif($count > $max_guests)

	return $output.'<p><em>'.esc_html(__('No room for additional guests','rsvpmaker')).'</em><p>'; // limit by # of guests per person

elseif($max_guests && ($count >= $max_guests))

	return $output.'<p><em>'.esc_html(__('No room for additional guests (max per party)','rsvpmaker')).'</em><p>'; // limit by # of guests per person



$output = '<div id="guest_section" tabindex="-1">'."\n".$output.'</div>'."<!-- end of guest section-->";

if($max_guests > ($count + 1))

	$output .= "<p><a href=\"#guest_section\" id=\"add_guests\" name=\"add_guests\">(+) ".$addmore."</a><!-- end of guest section--></p>\n";



$output .= '<script type="text/javascript"> var guestcount ='.$count.'; </script>';



return $output;

}



function stripe_form_wrapper($atts,$content) {

	global $post;

	$permalink = get_permalink($post->ID);

	$amount = (isset($atts['amount'])) ? $atts['amount'] : '';

	$vars['paymentType'] = (isset($atts['paymentType'])) ? $atts['paymentType'] : '';

	$vars['description'] = (isset($atts['description'])) ? $atts['description'] : 'Online Payment '.get_bloginfo('name');

	if(!empty($_POST))

	{

		$output = '';

		if(!empty($_POST['profile']))

		foreach($_POST['profile'] as $slug => $value)

			{

				$value = sanitize_text_field($value);

				$output .= sprintf('<p>%s: %s</p>'."\n",esc_html($slug),esc_html($value));

				$vars[$slug] = $value;

			}

		foreach($_POST as $slug => $value)

		{

			$value = sanitize_text_field($value);

			if($slug != 'profile')

			{

			$output .= sprintf('<p>%s: %s</p>'."\n",esc_html($slug),esc_html($value));

			$vars[$slug] = $value;

			}

		}

		preg_match_all('/<p.+\/p>/',$content,$matches);

		$content = $output;

		$paragraphs = '';

		if(!empty($matches))

			{

			foreach($matches[0] as $paragraph) {

				if(!strpos($paragraph,'<input') && !strpos($paragraph,'<textarea') && !strpos($paragraph,'<select') )

					$paragraphs .= $paragraph."\n";

			}

			}

		$content .= wp_kses_post($paragraphs);

		if(!empty($vars['paymentType'])) {

			$content .= sprintf('<p>Payment type: %s</p>',$vars['paymentType']);

		}

		$vars['contract'] = $paragraphs;

		$content .= rsvpmaker_stripe_form($vars);

		return $content;

	}

	$content = sprintf('<form method="post" action="%s">',$permalink).$content;

	$content .= sprintf('<input type="hidden" name="amount" value="%s" /><button>Submit</button></form>',$amount);

	return $content;

}



function remove_save_content_filters () {

	if(isset($_REQUEST['_locale']) && ($_REQUEST['_locale'] == 'user')) {

		$request_body = file_get_contents('php://input');

		if(strpos($request_body,'wp:rsvpmaker/formfield'))

		{

			//prevent html filtering on form for non-administrators

			remove_all_filters("content_save_pre"); //don't allow form fields to be filtered out

			remove_all_filters("content_filtered_save_pre");//'content_filtered_save_pre', 'wp_filter_post_kses'

		}

	}

}

add_action( 'init', 'remove_save_content_filters', 99 );

add_action( 'set_current_user', 'remove_save_content_filters', 99 );

?>