<?php

/*

RSVPMaker API Endpoints

*/



class RSVPMaker_Listing_Controller extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'future';



	 register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),



        ]);     

    }



  public function get_items_permissions_check($request) {

    return true;

  }



public function get_items($request) {



    $events = get_future_events();



    if (empty($events)) {



            return new WP_Error( 'empty_category', 'no future events listed', array( 'status' => 404 ) );

    }

    return new WP_REST_Response($events, 200);

  }



}



class RSVPMaker_Types_Controller extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'types';



    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),



        ]);     

    }



  public function get_items_permissions_check($request) {

    return true;

  }



public function get_items($request) {



    $types = get_terms('rsvpmaker-type');

    return new WP_REST_Response($types, 200);

  }



	//other functions to override

	//create_item(), update_item(), delete_item() and get_item()



}



class RSVPMaker_Authors_Controller extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'authors';



    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),



        ]);     

    }



  public function get_items_permissions_check($request) {

    return true;

  }



public function get_items($request) {



    $authors = get_rsvpmaker_authors();

    return new WP_REST_Response($authors, 200);

  }



}



class RSVPMaker_By_Type_Controller extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'type/(?P<type>[A-Za-z_\-]+)';	  

	  

    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

          ),

        ]);     

    }



  public function get_items_permissions_check($request) {

    return true;

  }



public function get_items($request) {



add_filter('posts_join', 'rsvpmaker_join' );

add_filter('posts_groupby', 'rsvpmaker_groupby' );

add_filter('posts_distinct', 'rsvpmaker_distinct' );

add_filter('posts_fields', 'rsvpmaker_select' );

add_filter('posts_where', 'rsvpmaker_where' );

add_filter('posts_orderby', 'rsvpmaker_orderby',99 );

	$querystring = "post_type=rsvpmaker&post_status=publish&rsvpmaker-type=".$request['type'];

	$wp_query = new WP_Query($querystring);

  $posts = $wp_query->get_posts();

	

  remove_filter('posts_join', 'rsvpmaker_join' );

  remove_filter('posts_groupby', 'rsvpmaker_groupby' );

  remove_filter('posts_distinct', 'rsvpmaker_distinct' );

  remove_filter('posts_fields', 'rsvpmaker_select' );

  remove_filter('posts_where', 'rsvpmaker_where' );

  remove_filter('posts_orderby', 'rsvpmaker_orderby',99 );  



    if (empty($posts)) {

            return new WP_Error( 'empty_category', 'there is no post in this category '.$querystring, array( 'status' => 404 ) );

    }

    return new WP_REST_Response($posts, 200);

  }



	//other functions to override

	//create_item(), update_item(), delete_item() and get_item()



}



class RSVPMaker_GuestList_Controller extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'guestlist/(?P<post_id>[0-9]+)';///(?P<nonce>.+)



    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),

        ]);     

    }



  public function get_items_permissions_check($request) {

    global $rsvp_options;

    $meta = get_post_meta($request['post_id'],"_rsvp_show_attendees",true);

    if($meta)

      return true;

    elseif(($meta == '') && $rsvp_options['show_attendees'])

      return true; //if not explicitly set for event, default is positive value 

    return false;

  }



public function get_items($request) {

    global $wpdb;

    $event = $request['post_id'];

    $sql = "SELECT first,last,note FROM ".$wpdb->prefix."rsvpmaker WHERE event=$event AND yesno=1 ORDER BY id DESC";

    $attendees = $wpdb->get_results($sql); 

    return new WP_REST_Response($attendees, 200);

  }

}



class RSVPMaker_ClearDateCache extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'clearcache/(?P<post_id>[0-9]+)';



    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),

        ]);     

    }



  public function get_items_permissions_check($request) {

    return true;

  }



public function get_items($request) {

  delete_transient('rsvpmakerdates');

  return new WP_REST_Response((object) 'deleted rsvpmakerdates transient', 200);

  }

}



class RSVPMaker_Sked_Controller extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'sked/(?P<post_id>[0-9]+)';///(?P<nonce>.+)



    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),

        ]);     

    }



  public function get_items_permissions_check($request) {

    return true;

  }



public function get_items($request) {

    $sked = get_template_sked($request['post_id']);

    return new WP_REST_Response($sked, 200);

  }

}

class RSVPMaker_StripeSuccess_Controller extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'stripesuccess/(?P<txkey>.+)';



    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'POST',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),

        ]);     

    }



  public function get_items_permissions_check($request) {

    return true;

  }



public function get_items($request) {

    global $wpdb;

    $base = get_option($request['txkey']);

    $key = 'conf:'.time();

    foreach($_POST as $name => $value)

      $vars[$name] = $value;

    if(is_array($base))

    foreach($base as $name => $value) {

      if(empty($vars[$name]))

      $vars[$name] = $value;

    }

    //$vars['charge_id'] = $charge->id;

    if(!empty($vars['rsvp_id']))

    {

      $rsvp_id = $vars['rsvp_id'];

      $rsvp_post_id = $vars['rsvp_post_id'];

      $paid = $vars['amount'];

      $invoice_id = get_post_meta($rsvp_post_id,'_open_invoice_'.$rsvp_id, true);

      //if($invoice_id)

      //{

      $charge = get_post_meta($rsvp_post_id,'_invoice_'.$rsvp_id, true);

      $paid_amounts = get_post_meta($rsvp_post_id,'_paid_'.$rsvp_id);

      if(is_array($paid_amounts))

      foreach($paid_amounts as $payment)

        $paid += $payment;

      $wpdb->query("UPDATE ".$wpdb->prefix."rsvpmaker SET amountpaid='$paid' WHERE id=$rsvp_id ");

      add_post_meta($rsvp_post_id,'_paid_'.$rsvp_id,$vars['amount']);

      $vars['payment_confirmation_message'] = '';

      $message_id = get_post_meta($rsvp_post_id,'payment_confirmation_message',true);

      if($message_id)

      {

        $message_post = get_post($message_id);

        $vars['payment_confirmation_message'] = do_blocks($message_post->post_content);

      }

      delete_post_meta($rsvp_post_id,'_open_invoice_'.$rsvp_id);

      delete_post_meta($rsvp_post_id,'_invoice_'.$rsvp_id);

      //}	

    }

    rsvpmaker_stripe_payment_log($vars,$key);

    delete_option($request['txkey']);

    return new WP_REST_Response($vars, 200);

  }

}



class RSVPMaker_PaypalSuccess_Controller extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'paypalsuccess/(?P<post_id>.+)/(?P<rsvp_id>.+)';



    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),

        ]);     

    }



  public function get_items_permissions_check($request) {

    return true;

  }



public function get_items($request) {

  $vars = array();

  $message_id = get_post_meta($request['post_id'],'payment_confirmation_message',true);

      if($message_id)

      {

        $message_post = get_post($message_id);

        if(empty($message_post->post_content))

          $message_post->post_content = '<p>'.__('Thank you for your payment','rsvpmaker').'</p>';

        $vars['payment_confirmation_message'] = do_blocks($message_post->post_content);

        rsvp_confirmation_after_payment ($request['rsvp_id']);

      }

    return new WP_REST_Response($vars, 200);

  }

}



class RSVP_Export extends WP_REST_Controller {

	public function register_routes() {

	  $namespace = 'rsvpmaker/v1';

	  $path = 'import/(?P<code>.+)/(?P<start>.+)';

  

	  register_rest_route( $namespace, '/' . $path, [

		array(

		  'methods'             => 'GET',

		  'callback'            => array( $this, 'handle' ),

		  'permission_callback' => array( $this, 'get_items_permissions_check' )

		),

		  ]);     

	  }

  

	public function get_items_permissions_check($request) {

    $code = get_option('rsvptm_export_lock');

    if(empty($code))

      return $false;

		$parts = explode(':',$code);

		$t = (int) $parts[1];

		if($t < time())

			return false;

		return ($code == $request["code"]);

	}

  

  public function handle($request) {

	  global $wpdb;

	  $start = $request['start'];

	  $sql = "SELECT * FROM $wpdb->posts WHERE ID > $start AND post_type='rsvpmaker' AND post_status='publish' ORDER BY ID LIMIT 0,50";

	  $future = $wpdb->get_results($sql);

	  foreach($future as $index => $row) {

		  $sql = "select * from $wpdb->postmeta WHERE post_id=".$row->ID;

		  $metaresults = $wpdb->get_results($sql);

		  foreach($metaresults as $metarow) {

			$future[$index]->meta[] = $metarow;

		  }

	  }

	  return new WP_REST_Response($future,200);

	}

}



class RSVP_RunImport extends WP_REST_Controller {

	public function register_routes() {

	  $namespace = 'rsvpmaker/v1';

	  $path = 'importnow';

  

	  register_rest_route( $namespace, '/' . $path, [

		array(

		  'methods'             => 'POST',

		  'callback'            => array( $this, 'handle' ),

		  'permission_callback' => array( $this, 'get_items_permissions_check' )

		),

		  ]);     

	  }

  

	public function get_items_permissions_check($request) {

    //nonce check here

    return current_user_can('manage_options');//

	}

  

  public function handle($request) {

	  global $wpdb;

    $error = '';

    $imported = 0;

    $top = 0;

    if(isset($_POST['importrsvp'])) {

      $url = $_POST['importrsvp'];

      $url .= '/'.$_POST['start'];

      $siteurl = get_option( 'siteurl' );

      $parts = explode(':',$siteurl);

      if(strpos($url,$parts[1]))

        $error = 'You cannot import into the same site you are exporting from';

      else {

        $remote = wp_remote_get($url);

        if(is_wp_error($remote))

          $error = $remote->get_error_message();

        else {

          $remote_events = $remote['body'];

          if(strpos($remote_events,'rest_forbidden'))

            $error = 'forbidden';  

        }

      }

      if (empty($error)) {

          //echo $remote_events;

          $events = json_decode($remote_events);

          if(!empty($events))

          foreach($events as $event) {

            $top = $event->ID;

            $newpost['post_title'] = $event->post_title;

            $newpost['post_content'] = $event->post_content;

            $newpost['post_status'] = 'publish';

            $newpost['post_type'] = 'rsvpmaker';

            $post_id = wp_insert_post($newpost);

            if($post_id) {

              $imported++;

              if(!empty($event->meta)) {

                foreach($event->meta as $metarow) {

                  $sql = $wpdb->prepare("INSERT INTO $wpdb->postmeta SET post_id=%s, meta_key=%s, meta_value=%s",$post_id,$metarow->meta_key,$metarow->meta_value);

                  $wpdb->query($sql);

                }

              }//meta array	

            }//post_id

          }//end for event loop

	} //end empty error

}//end post value

return new WP_REST_Response(array('error' => $error, 'imported' => $imported, 'top' => $top),200);

}//end handle

}//end class



class RSVPMaker_Email_Lookup extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'email_lookup/(?P<nonce>.+)/(?P<event>[0-9]+)';///(?P<nonce>.+)



    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),

        ]);     

    }



  public function get_items_permissions_check($request) {

    return wp_verify_nonce($request["nonce"],'rsvp_email_lookup');

  }



public function get_items($request) {

    global $wpdb;

    $event = $request['event'];

    $email = $_GET['email_search'];

    $output = ajax_rsvp_email_lookup ($email, $event);

    return new WP_REST_Response($output, 200);

  }

}



class RSVPMaker_Signed_Up extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'signed_up';



    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),

        ]);     

    }



  public function get_items_permissions_check($request) {

    return true;

  }



public function get_items($request) {

    global $wpdb;

    $event = (int) $_GET['event'];

    $output = signed_up_ajax ($event);

    return new WP_REST_Response($output, 200);

  }

}

class RSVPMaker_Shared_Template extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';

    $path = 'shared_template/(?P<post_id>[0-9]+)';

    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'GET',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),

        ]);     

    }



  public function get_items_permissions_check($request) {
    return true;
  }

public function get_items($request) {
    $post_id = $request['post_id'];
    $template = get_post($post_id);
    $shared = get_post_meta($post_id,'rsvpmaker_shared_template',true);
    if(empty($template) || empty($shared))
      return new WP_REST_Response(false, 200);
    $export['post_title'] = $template->post_title;
    $export['post_content'] = $template->post_content;
    return new WP_REST_Response($export, 200);
  }
}

class RSVPMaker_Setup extends WP_REST_Controller {

  public function register_routes() {

    $namespace = 'rsvpmaker/v1';
    $path = 'setup';

    register_rest_route( $namespace, '/' . $path, [

      array(

        'methods'             => 'POST',

        'callback'            => array( $this, 'get_items' ),

        'permission_callback' => array( $this, 'get_items_permissions_check' )

            ),

        ]);     

    }

  public function get_items_permissions_check($request) {
    return current_user_can('edit_rsvpmakers');
  }

public function get_items($request) {
    $editurl = rsvpmaker_setup_post(true);
    return new WP_REST_Response($editurl, 200);
  }
}

add_action('rest_api_init', function () {

  $rsvpmaker_sked_controller = new RSVPMaker_Sked_Controller();

  $rsvpmaker_sked_controller->register_routes();

  $rsvpmaker_by_type_controller = new RSVPMaker_By_Type_Controller();

  $rsvpmaker_by_type_controller->register_routes();

  $rsvpmaker_listing_controller = new RSVPMaker_Listing_Controller();

  $rsvpmaker_listing_controller->register_routes();

  $rsvpmaker_types_controller = new RSVPMaker_Types_Controller();

  $rsvpmaker_types_controller->register_routes();

  $rsvpmaker_authors_controller = new RSVPMaker_Authors_Controller();

  $rsvpmaker_authors_controller->register_routes();

  $rsvpmaker_guestlist_controller = new RSVPMaker_GuestList_Controller();

  $rsvpmaker_guestlist_controller->register_routes();

  $rsvpmaker_meta_controller = new RSVPMaker_ClearDateCache();

  $rsvpmaker_meta_controller->register_routes();

  $stripesuccess = new RSVPMaker_StripeSuccess_Controller();

  $stripesuccess->register_routes();

  $ppsuccess = new RSVPMaker_PaypalSuccess_Controller();

  $ppsuccess->register_routes();

  $rsvpexp = new RSVP_Export();

  $rsvpexp->register_routes();

  $rsvpimp = new RSVP_RunImport();

  $rsvpimp->register_routes();

  $signed_up = new RSVPMaker_Signed_Up();

  $signed_up->register_routes();

  $email_lookup = new RSVPMaker_Email_Lookup();

  $email_lookup->register_routes();

  $sharedt = new RSVPMaker_Shared_Template();

  $sharedt->register_routes();
  $setup = new RSVPMaker_Setup();
  $setup->register_routes();

});

?>