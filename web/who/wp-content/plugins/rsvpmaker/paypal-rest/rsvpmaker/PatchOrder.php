<?php

//require_once '../vendor/autoload.php';
use PayPalCheckoutSdk\Orders\OrdersPatchRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
require_once 'CreateOrder.php';
require_once 'PayPalClient.php';

class RSVPMakerPatchOrder
{
    private static function buildRequestBody()
    {
        return array (
            0 =>
                array (
                    'op' => 'replace',
                    'path' => '/intent',
                    'value' => 'CAPTURE',
                ),
            1 =>
                array (
                    'op' => 'replace',
                    'path' => '/purchase_units/@reference_id==\'PUHF\'/amount',
                    'value' =>
                        array (
                            'currency_code' => 'USD',
                            'value' => '200.00',
                            'breakdown' =>
                                array (
                                    'item_total' =>
                                        array (
                                            'currency_code' => 'USD',
                                            'value' => '180.00',
                                        ),
                                    'tax_total' =>
                                        array (
                                            'currency_code' => 'USD',
                                            'value' => '20.00',
                                        ),
                                ),
                        ),
                ),
        );
    }

    public static function patchOrder($orderId)
    {

        $client = RSVPMakerPayPalClient::client();

        $request = new OrdersPatchRequest($orderId);
        $request->body = PatchOrder::buildRequestBody();
        $client->execute($request);

        $response = $client->execute(new OrdersGetRequest($orderId));

        print "Status Code: {$response->statusCode}\n";
        print "Status: {$response->result->status}\n";
        print "Order ID: {$response->result->id}\n";
        print "Intent: {$response->result->intent}\n";
        print "Links:\n";
        foreach($response->result->links as $link)
        {
            print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
        }

        print "Gross Amount: {$response->result->purchase_units[0]->amount->currency_code} {$response->result->purchase_units[0]->amount->value}\n";

        // To toggle printing the whole response body comment/uncomment below line
        echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
    }
}
