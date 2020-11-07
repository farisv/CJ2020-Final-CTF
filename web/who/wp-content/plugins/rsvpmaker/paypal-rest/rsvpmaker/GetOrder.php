<?php

//require_once '../vendor/autoload.php';
require_once 'PayPalClient.php';

use PayPalCheckoutSdk\Orders\OrdersGetRequest;
//use RSVPMaker\CaptureIntentExamples\CreateOrder;

class RSVPMakerGetOrder
{

    /**
     * This function can be used to retrieve an order by passing order Id as argument.
     */
    public static function getOrder($orderId)
    {        
        $client = RSVPMakerPayPalClient::client();
        $response = $client->execute(new OrdersGetRequest($orderId));
        {
            if(!empty($_GET['rsvp'])) {
                $rsvp_id = $_GET['rsvp'];
                $event = $_GET['event'];
                rsvpmaker_custom_payment('PayPal REST api',$response->result->purchase_units[0]->amount->value,$rsvp_id,$event, $response->result->id);
            $log = sprintf('%s %s %s %s',$response->result->purchase_units[0]->amount->value,$rsvp_id,$event, $response->result->id);
            rsvpmaker_debug_log($log,'PayPal test');
            $payment_message_id = get_post_meta($event,'payment_confirmation_message',true);
            /*if($payment_message_id) {
                $payment_message_post = get_post($payment_message_id);
            }
            */
            }
        $response->result->payment_confirmation_message = $payment_message_id;//(empty($payment_message_post) || empty($payment_message_post->post_content)) ? '' : do_blocks($payment_message_post->post_content);
        //print_r($_GET);
        echo json_encode($response); // also log this?
        }
    }
}
