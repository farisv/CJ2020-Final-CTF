<?php
add_action('rsvpmaker_special_metabox','rsvpmaker_location');

function rsvpmaker_location_button() {
	global $post;
	if(isset($post->post_type) && $post->post_type == 'rsvpmaker')
    echo '<button type="button" id="rsvpmaker-add-location" class="button"><img src="'.plugins_url('rsvpmaker/images/if_map-marker_173052.png').'" width="25" height="25" style="margin-left: -12px;"> Location</button>';
}

add_action('media_buttons', 'rsvpmaker_location_button', 30);

function rsvpmaker_location_form() {
global $wpdb;
global $post;
if(!isset($post->post_type) || $post->post_type != 'rsvpmaker')
return;
	?>
<div id="location-dialog-form" title="Location">
 
  <form>
    <fieldset>
		<label>Saved Locations</label>
		<select id="locselect">
		<?php
	$options = '<option value="">Pick a Saved Location</option>';;
	$sql = "SELECT ID,post_title from $wpdb->posts JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE meta_key='_rsvpmaker_special' AND meta_value='Location' AND (post_status='publish' OR post_status='draft') ORDER BY post_title ";
	$results = $wpdb->get_results($sql);
	if(is_array($results)) {
		foreach($results as $row)
		{
			$options .= sprintf('<option value="%s">%s</option>',$row->ID,$row->post_title);
		}
	}
	else
		$options = '<option value="">No Saved Locations</option>';
	echo $options;
	?>
		</select>
		<br />
		<button id="chooseloc">Choose</button> <button id="editloc" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" baseurl="<?php echo admin_url('post.php?action=edit&post='); ?>">Edit</button> <span id="loceditlink"></span>
     
		<h3>New Location</h3>
     
      <label for="name">Location Name</label>
      <input type="text" name="name" id="name" value="" class="text ui-widget-content ui-corner-all">
      <label for="address">Street Address</label>
      <input type="text" name="address" id="address" value="" class="text ui-widget-content ui-corner-all">
      <label for="city">City</label>
      <input type="text" name="city" id="city" value="" class="text ui-widget-content ui-corner-all">
      <label for="state">State</label>
      <input type="text" name="state" id="state" value="<?php echo get_option('location_default_state'); ?>" class="text ui-widget-content ui-corner-all">
      <label for="postal">Postal Code</label>
      <input type="text" name="postal" id="postal" value="" class="text ui-widget-content ui-corner-all">
      <label for="map">Map Link</label>
      <input type="text" name="map" id="map" value="" class="text ui-widget-content ui-corner-all">

		<p class="validateTips">Leave <strong>Map Link</strong> blank to generate a Google Maps link based on the address you entered.</p>
      
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
    </fieldset>
  </form>
</div>
<script>
jQuery(document).ready(function( $ ) {

$( function() {
    var locdialog, locform;
	
	function addLocation () {
		//
		var name = $('#name').val();
		var address = $('#address').val();
		var city = $('#city').val();
		var state = $('#state').val();
		var postal = $('#postal').val();
		var map = $('#map').val();
		if(!map)
			{
				var query = address+' '+city+', '+state+' '+postal;
				query = encodeURIComponent(query);
				map = 'https://www.google.com/maps/search/?api=1&query='+query;
			}
		var output = '\n\n<span class="rsvplocation"><span class="locname">'+name+'</span><br />' +
'<span class="locaddress">'+address+'</span><br /><span class="citystatezip"><span class="loccity">'+city+'</span>'+', <span class="state">'+state+'</span> <span class="postal">'+postal+'</span>&nbsp;<a target="_blank" class="map" href="'+map+'">(Map)</a></span></span>\n\n';
		wp.media.editor.insert(output);
		
$.post(
   ajaxurl, 
    {
        'action': 'save_rsvpmaker_location',
		'post_title' : name,
		'post_content' : output,
		'state' : state
	});
 		
      locdialog.dialog( "close" );
	}
 
    locdialog = $( "#location-dialog-form" ).dialog({
      autoOpen: false,
      height: 650,
      width: 600,
      modal: true,
		open: function (event, ui) {
		 $('.ui-dialog').css('z-index',2000);
		 $('.ui-widget-overlay').css('z-index',1500);
		},
		buttons: {
        "Add": addLocation,
        Cancel: function() {
          locdialog.dialog( "close" );
        }
      },
      close: function() {
        locform[ 0 ].reset();
      }
    });
 
    locform = locdialog.find( "form" ).on( "submit", function( event ) {
      event.preventDefault();
      addLocation();
    });
 
    $( "#rsvpmaker-add-location" ).button().on( "click", function() {
      locdialog.dialog( "open" );
    });
    $( "#chooseloc" ).button().on( "click", function(event) {
      event.preventDefault();
	var id = $('#locselect option:selected').val();
		
$.get(
   ajaxurl, 
    {
        'action': 'get_rsvpmaker_location',
		'ID' : id
    }, 
    function(response){
	wp.media.editor.insert('\n\n'+response+'\n\n');
    }
);
    locdialog.dialog( "close" );
			
    });

  } );

    $( "#editloc" ).button().on( "click", function(event) {
      event.preventDefault();
	var id = $('#locselect option:selected').val();
	var link = $("#editloc").attr('baseurl') + id;
	$('#loceditlink').html('<a target="_blank" href="'+link+'">Edit in new window</a>');
    });

});
</script>
	<?php
}

add_action('admin_footer','rsvpmaker_location_form');

add_action('wp_ajax_save_rsvpmaker_location','save_rsvpmaker_location');
function save_rsvpmaker_location () {
	$post["post_title"] = sanitize_title($_POST['post_title']);
	$post["post_content"] = wp_kses_post($_POST['post_content']);
	$post["post_type"] = 'rsvpmaker';
	$post["post_status"] = 'draft';
	$id = wp_insert_post($post);
	add_post_meta($id,'_rsvpmaker_special','Location');
	$state = sanitize_text_field($_POST['state']);
	if(!empty($state) && (strlen($state) == 2))
		update_option('location_default_state',$state);
	die('added as post #'.$id);
}
add_action('wp_ajax_get_rsvpmaker_location','get_rsvpmaker_location');
function get_rsvpmaker_location () {
	$id = (int) $_GET['ID'];
	$post = get_post($id);
	echo wpautop($post->post_content);
	die();
}

?>