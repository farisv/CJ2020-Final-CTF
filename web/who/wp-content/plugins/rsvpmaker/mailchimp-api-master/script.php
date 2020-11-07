<?php
$scriptversion = '202008151';

function rsvpmaker_rest_array () {
    global $post;
    $post_id = isset($post->ID) ? $post->ID : 0;
    return array('post_id' => $post_id,'nonce' => wp_create_nonce('wp_rest'),'rest_url' => rest_url());
}

function rsvpmaker_admin_enqueue($hook) {
global $post, $scriptversion;
$post_id = isset($post->ID) ? $post->ID : 0;
	if((!function_exists('do_blocks') && isset($_GET['action'])) || (isset($_GET['post_type']) && ($_GET['post_type'] == 'rsvpmaker') ) || ((isset($_GET['page']) && 
	((strpos($_GET['page'],'rsvp') !== false ) || (strpos($_GET['page'],'toast') !== false ) )  ) ) )
	{
	wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_style( 'rsvpmaker_jquery_ui', plugin_dir_url( __FILE__ ) . 'jquery-ui.css',array(),'4.1' );
	wp_enqueue_script( 'rsvpmaker_admin_script', plugin_dir_url( __FILE__ ) . 'admin.js',array('jquery'), $scriptversion );
	wp_enqueue_style( 'rsvpmaker_admin_style', plugin_dir_url( __FILE__ ) . 'admin.css',array(),$scriptversion);
	wp_localize_script( 'rsvpmaker_admin_script', 'rsvpmaker_rest', rsvpmaker_rest_array() );
    }
    wp_enqueue_script('rsvpmaker_timezone',plugins_url('rsvpmaker/jstz.min.js'),array(),$scriptversion);
}

function rsvpmaker_event_scripts() {
global $post, $scriptversion;
$post_id = isset($post->ID) ? $post->ID : 0;
global $rsvp_options;
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-tooltip');
	$myStyleUrl = (isset($rsvp_options["custom_css"]) && $rsvp_options["custom_css"]) ? $rsvp_options["custom_css"] : plugins_url('/rsvpmaker/style.css');
	wp_register_style('rsvp_style', $myStyleUrl, array(), $scriptversion);
	wp_enqueue_style( 'rsvp_style');
	wp_localize_script( 'rsvpmaker_ajaxurl', 'ajaxurl', admin_url('admin-ajax.php') );
	wp_enqueue_script('rsvpmaker_js',plugins_url('rsvpmaker/rsvpmaker.js'), array(), $scriptversion);
	wp_localize_script( 'rsvpmaker_js', 'rsvpmaker_json_url', site_url('/wp-json/rsvpmaker/v1/'));
    wp_localize_script( 'rsvpmaker_js', 'rsvpmaker_rest', rsvpmaker_rest_array () );
    if(is_single())
    {
        if(strpos($post->post_content,'wp:rsvpmaker/submission'))
            wp_enqueue_script('wp-tinymce');
    }
} // end event scripts

function rsvpmaker_jquery_inline ($routine, $atts = array()) {
global $post, $current_user, $wpdb;
?>
<script>
jQuery(document).ready(function($) {
$.ajaxSetup({
    headers: {
        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>',
    }
});
<?php
if($routine == 'import')
{
?>
var totalImported = 0;
function importRSVP(url, data) {
    $.post(url, data, function(response) {
    console.log(response);
    if(response.error) {
        $('#import-result').html(response.error);
        $('#import-result').css({borderColor: 'red'});
    }
    else
    {
        $('#import-result').css({borderColor: 'green'});
        $('#importform').hide();
        if(response.imported && response.top) {
            $('#import-result').html('Imported '+response.imported+' events, ending with #'+response.top+', fetching more');
            data.start = response.top;
            totalImported += parseInt(response.imported);
            importRSVP(url, data);
        } else {
            totalImported += parseInt(response.imported);
            $('#import-result').html('Total imported '+totalImported+', done');
        }
    } 

    });
} 

$('#import-button').click(function(e) {
e.preventDefault();
var remoteurl = $('#importrsvp').val();
$('#importrsvp').val('');//clear the field
$('#import-result').css({padding: '10px',borderWidth: 'thick',borderStyle: 'solid',borderColor: 'gray'});
$('#import-result').html('Trying '+remoteurl+' please wait ...');

var data = {
    'importrsvp': remoteurl,
    'start': 0,
};
var importnowurl = $('#importnowurl').val();
importRSVP(importnowurl,data);
});
<?php
}//end import
?>
});

</script>
<?php    
}

function rsvp_form_jquery() {
    global $rsvp_required_field;
    global $post;
    ob_start();
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
    
    <?php
    $hide = get_post_meta($post->ID,'_hiddenrsvpfields',true);
    if(!empty($hide))
        {
        printf('var hide = %s;',json_encode($hide));
        echo "\n";
    ?>
    
    $('#guest_count_pricing select').change(function() {
      //reset hidden fields
      $('#rsvpform input').prop( "disabled", false );
      $('#rsvpform select').prop( "disabled", false );
      $('#rsvpform div').show();
      $('#rsvpform p').show();
      var pricechoice = $(this).val();
      //alert( "Price choice" + hide[pricechoice] );
      var hideit = hide[pricechoice];
      $.each(hideit, function( index, value ) {
      //alert( index + ": " + value );
      $('div.'+value).hide();
      $('p.'+value).hide();
      $('.'+value).prop( "disabled", true );
    });
      
    });
    
    <?php
        }
    ?>
    var max_guests = $('#max_guests').val();
    var blank = $('#first_blank').html();
    if(blank)
        {
        $('#first_blank').html(blank.replace(/\[\]/g,'['+guestcount+']').replace('###',guestcount) );
    guestcount++;
        }
    $('#add_guests').click(function(event){
        event.preventDefault();
    if(guestcount >= max_guests)
        {
        $('#first_blank').append('<p><em><?php _e('Guest limit reached','rsvpmaker'); ?></em></p>');
        return;
        }
    var guestline = '<' + 'div class="guest_blank">' +
        blank.replace(/\[\]/g,'['+guestcount+']').replace('###',guestcount) +
        '<' + '/div>';
    guestcount++;
    $('#first_blank').append(guestline);
    
    if(hide)
    {
      var pricechoice = $("#guest_count_pricing select").val();
      var hideit = hide[pricechoice];
      $.each(hideit, function( index, value ) {
      //alert( index + ": " + value );
      $('div.'+value).hide();
      $('p.'+value).hide();
      $('.'+value).prop( "disabled", true );
    });
    }
    
    });
    
        jQuery("#rsvpform").submit(function() {
        var leftblank = '';
        var required = jQuery("#required").val();
        var required_fields = required.split(',');
        $.each(required_fields, function( index, value ) {
            if(value == 'privacy_consent')
                {
                if(!jQuery('#privacy_consent:checked').val())
                leftblank = leftblank + '<' + 'div class="rsvp_missing">privacy policy consent checkbox<' +'/div>';				
                }
            else if(jQuery("#"+value).val() === '') leftblank = leftblank + '<' + 'div class="rsvp_missing">'+value+'<' +'/div>';
        });
        if(leftblank != '')
            {
            jQuery("#jqerror").html('<' +'div class="rsvp_validation_error">' + "Required fields left blank:\n" + leftblank + ''+'<' +'/div>');
            return false;
            }
        else
            return true;
    });
    
    //search for previous rsvps
    var searchRequest = null;
    
    $(function () {
        var minlength = 3;
    
        $("#email").keyup(function () {
            var that = this;
            value = $(this).val();
            var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
            var post_id = $('#event').val();
            if ((value.length >= minlength ) && (value.match(mailformat)) ) {
                if (searchRequest != null) 
                    searchRequest.abort();
                var data = {
                    'email_search': value,
                };
                jQuery.get('<?php echo rest_url('rsvpmaker/v1/email_lookup/'.wp_create_nonce('rsvp_email_lookup')); ?>/'+post_id, data, function(response) {
                $('#rsvp_email_lookup').html(response);
                });
            }
        });
    });	
    
    });
    </script>
    <?php
    return ob_get_clean();
}

function rsvpmaker_timezone_footer() {
	if(isset($_GET['tz'])) {
?>
<script>
jQuery(document).ready(function($) {
$('.timezone_hint').each( function () {
var utc = $(this).attr('utc');
var target = $(this).attr('target');
var localdate = new Date(utc);
localstring = localdate.toString();
$('#'+target).html('<div>'+localstring+'<div>');
var data = {
	'action': 'rsvpmaker_localstring',
	'localstring': localstring
};
jQuery.post(ajaxurl, data, function(response) {
$('#'+target).html('<div>'+response+'</div>');
});
});
});
</script>
<?php
	}
}

?>