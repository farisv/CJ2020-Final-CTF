<?php

function rsvpmaker_to_stripe ($rsvp) {

rsvpmaker_debug_log('rsvpmaker_to_stripe');

	global $post;

	$vars['description'] = $post->post_title;

	$vars['name'] = $rsvp['first'].' '.$rsvp['last'];

	if(isset($_GET['rsvp']))

		$vars['rsvp_id'] = (int) $_GET['rsvp'];

	else	

		$vars['rsvp_id'] = $rsvp['id'];

	$vars['rsvp_post_id'] = $post->ID;

	$include = array('amount','rsvp_id','email','event');

	foreach($rsvp as $index => $value)

		if(in_array($index,$include))

			$vars[$index] = $value;

	// transform

	return rsvpmaker_stripe_form($vars);

}



//called from Gutenberg init

function rsvpmaker_stripecharge ($atts) {

	rsvpmaker_debug_log('rsvpmaker_stripecharge');

if(is_admin() || wp_is_json_request())

	return;



global $current_user;



$vars['description'] =(!empty($atts['description'])) ? $atts['description'] : __('charge from','rsvpmaker').' '.get_bloginfo('name');

$vars['paymentType'] = $paymentType = (empty($atts['paymentType'])) ? 'once' : $atts['paymentType'];

$show =(!empty($atts['showdescription']) && ($atts['showdescription'] == 'yes')) ? true : false;



if($paymentType == 'schedule')

{

$months = array('january','february','march','april','may','june','july','august','september','october','november','december');

$index = date('n') - 1;

if(isset($_GET['next']))

{

	if($index == 11)

		$index = 0;

	else

		$index++;		

}

$month = $months[$index];

$vars['amount'] = $atts[$month];

$vars['description'] = $vars['description'].': '.ucfirst($month);

if(!empty($current_user->user_email))

$vars['email'] = $current_user->user_email;

return rsvpmaker_stripe_form($vars, $show);

}



$vars['amount'] = (!empty($atts['amount'])) ? $atts['amount'] : '';

if($paymentType != 'once')

	$vars['description'] .= ' '.$paymentType;

return rsvpmaker_stripe_form($vars, $show);

//return rsvpmaker_stripe_form($vars,$show);

}



//global variable to prevent loops

$rsvpmaker_stripe_form = '';



function rsvpmaker_stripe_form($vars, $show = false) {

rsvpmaker_debug_log('rsvpmaker_stripe_form');

global $post, $rsvp_options, $current_user, $button, $rsvpmaker_stripe_form, $wpdb;

//if(!empty($rsvpmaker_stripe_form))

	//return $rsvpmaker_stripe_form;

if(!$show)

	$show =(!empty($vars['showdescription']) && ($vars['showdescription'] == 'yes')) ? true : false;

$currency = (empty($rsvp_options['paypal_currency'])) ? 'usd' : strtolower($rsvp_options['paypal_currency']);

$vars['currency'] = $currency;





//$rsvpmaker_stripe_checkout_page_id = get_option('rsvpmaker_stripe_checkout_page_id');

$rsvpmaker_stripe_checkout_page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_status='publish' AND  post_content LIKE '%[rsvpmaker_stripe_checkout]%' ");

if(empty($rsvpmaker_stripe_checkout_page_id)) {// || isset($_GET['reset_stripe_checkout_page'])) {

	rsvpmaker_debug_log($rsvpmaker_stripe_checkout_page_id,'attempting rsvpmaker_stripe_checkout_page_id');

	$postvar['post_content'] = '<!-- wp:shortcode -->

	[rsvpmaker_stripe_checkout]

	<!-- /wp:shortcode -->



	<!-- wp:paragraph -->

<p>Secure payment processing by <a href="https://stripe.com/" target="_blank">Stripe</a>.</p>

<!-- /wp:paragraph -->

';

	$postvar['post_title'] = 'Payment';

	$postvar['post_status'] = 'publish';

	$postvar['post_author'] = 1;

	$postvar['post_type'] = 'rsvpmaker';

	$rsvpmaker_stripe_checkout_page_id = wp_insert_post($postvar);

	update_post_meta($rsvpmaker_stripe_checkout_page_id,'_rsvpmaker_special','Payment checkout page for Stripe');

	rsvpmaker_debug_log($rsvpmaker_stripe_checkout_page_id,'new checkout page');

	//update_option('rsvpmaker_stripe_checkout_page_id',$rsvpmaker_stripe_checkout_page_id);

}

$currency_symbol = '';

if(isset($vars['currency']))

{

	if($vars['currency'] == 'usd')

		$currency_symbol = '$';

	elseif($vars['currency'] == 'eur')

		$currency_symbol = '€';

}

$idempotency_key = 'stripe_'.time().'_'.rand(0,100000000000);

update_option($idempotency_key,$vars);

$url = get_permalink($rsvpmaker_stripe_checkout_page_id);

if(isset($vars['paymentType']) && ( $vars['paymentType'] == 'donation'))

	$output = sprintf('<form action="%s" method="get">%s (%s): <input type="text" name="amount" value="%s"><br /><input type="hidden" name="txid" value="%s"><button class="stripebutton">%s</button></form>',$url,__('Amount','rsvpmaker'),strtoupper($vars['currency']),$vars['amount'],$idempotency_key,__('Pay with Card'));

else

	$output = sprintf('<form action="%s" method="get"><input type="hidden" name="txid" value="%s"><button class="stripebutton">%s</button></form>',$url,$idempotency_key,__('Pay with Card'));

if($show)

	$output .= sprintf('<p>%s%s %s<br />%s</p>',$currency_symbol,$vars['amount'],$rsvp_options["paypal_currency"],$vars["description"]);

$rsvpmaker_stripe_form = $output;

return $output;

}



function rsvpmaker_stripe_checkout() {

rsvpmaker_debug_log('rsvpmaker_stripe_checkout');

global $post, $rsvp_options, $current_user;

if(empty($_GET['txid']))

	return;

ob_start();

$varkey = $idempotency_key = $_GET['txid'];

$vars = get_option($idempotency_key);

if(empty($vars))

	return '<p>'.__('No pending payment found for','rsvpmaker').' '.$idempotency_key.'</p>';

if($vars['paymentType'] == 'donation')

	{

	if(empty($_GET['amount']))

		return '<p>No amount given</p>';

	$vars['amount'] = $_GET['amount'];

	} 

require_once('stripe-php/init.php');

$keys = get_rsvpmaker_stripe_keys ();

if(!empty($vars['email']))

{

	$email = $vars['email'];

	$name = (empty($vars['name'])) ? '' : $vars['name']; 

}

else {

	$email = (empty($current_user->user_email)) ? '' : $current_user->user_email;	

	$wpname = '';
	if(!empty($current_user->ID))
	{
		$userdata = get_userdata($current_user->ID);
		if($userdata->first_name) 
			$wpname = $userdata->first_name.' '.$userdata->last_name;
		else
			$wpname = $userdata->display_name;
	}
	$name = (empty($wpname)) ? '' : $wpname;
}

$public = $keys['pk'];

$secret = $keys['sk'];

if(strpos($public,'test'))

	$vars['test'] = 'TEST TRANSACTION';



//$vars['currency'] = 'XYZ';

$currency_symbol = '';



if($vars['currency'] == 'usd')

	$currency_symbol = '$';

elseif($vars['currency'] == 'eur')

	$currency_symbol = '€';



$paylabel = __('Pay','rsvpmaker') .' '. $currency_symbol.$vars['amount'].' '.strtoupper($vars['currency']);



rsvpmaker_debug_log('stripe set apikey');

\Stripe\Stripe::setApiKey($secret);



rsvpmaker_debug_log('stripe set apikey');

\Stripe\Stripe::setAppInfo(

  "WordPress RSVPMaker events management plugin",

  get_rsvpversion(),

  "https://rsvpmaker.com"

);



rsvpmaker_debug_log('call to PaymentIntent');



$intent = \Stripe\PaymentIntent::create([

	'amount' => $vars['amount'] * 100,

	'currency' => $vars['currency'],

	'description' => $vars['description'],

	'payment_method_types' => ['card'],

	'statement_descriptor' => substr('Paid on '.$_SERVER['SERVER_NAME'],0,21),

	

], ["idempotency_key" => $idempotency_key,]

);



update_post_meta($post->ID,$varkey,$vars);

$price = $vars['amount'] * 100;

?>

<!-- Stripe library must be loaded stripe.com per https://stripe.com/docs/js/including -->

<script src="https://js.stripe.com/v3/"></script>

<!-- We'll put the success / error messages in this element -->

<div id="card-result" role="alert"></div>

<div id="stripe-checkout-form">

<form id="payee-form">

<div><input id="stripe-checkout-name" name="name" placeholder="<?php _e('Your Name Here','rsvpmaker');?>" value="<?php echo $name; ?>"></div>

<div><input id="stripe-checkout-email" name="email" placeholder="email@example.com" value="<?php echo $email; ?>"></div>

<div id="card-element">

  <!-- Elements will create input elements here -->

</div>



<p><button id="card-button" class="stripebutton" data-secret="<?php echo $intent->client_secret; ?>">

    <?php echo $paylabel; ?>

</button></p>

</form>

<?php

if(strpos($public,'test') && !isset($_GET['hidetest']))

	printf('<p>%s</p>',__('Stripe is in TEST mode. To simulate a transaction, use:<br />Credit card 4111 1111 1111 1111<br />Any future date<br />Any three digit CVC code<br />Any 5-digit postal code','rsvpmaker'));

?>

</div>

<script>

var stripe = Stripe('<?php echo $public; ?>');

var elements = stripe.elements();

var style = {

  base: {

	iconColor: '#111111',

    color: "#111111",

	fontWeight: 400,

	fontSize: '16px',

	'::placeholder': {

	color: '#333333',

	},

	'::-ms-clear': {

	backgroundColor: '#fff',

	},

  	},

	empty: {

	backgroundColor: '#fff',

  	},

	completed: {

	backgroundColor: '#eee',

  	},

};



var card = elements.create("card", { style: style });

card.mount("#card-element");



card.addEventListener('change', ({error}) => {

  const displayError = document.getElementById('card-result');

  if (error) {

    displayError.textContent = error.message;

  } else {

    displayError.textContent = '';

  }

});



var cardFields = document.getElementById('stripe-checkout-form');

var submitButton = document.getElementById('card-button');

var cardResult = document.getElementById('card-result');

var clientSecret = document.getElementById('card-button').getAttribute('data-secret');



submitButton.addEventListener('click', function(ev) {

ev.preventDefault();

var name = document.getElementById('stripe-checkout-name').value;

var email = document.getElementById('stripe-checkout-email').value;

var successurl = '<?php echo site_url('/wp-json/rsvpmaker/v1/stripesuccess/'.$idempotency_key) ?>';//rsvpmaker_json_url+'stripesuccess/< ?php echo $idempotency_key; ? >';

if((name == '') || (email == '')){

	cardResult.innerHTML = 'Name and email are both required';

	return;

}

cardResult.innerHTML = '<?php _e('Please wait','rsvpmaker');?>';

cardResult.style.cssText = 'background-color: #fff; padding: 10px;';

  stripe.confirmCardPayment(clientSecret, {

    payment_method: {

      card: card,

      billing_details: {

        name: name,

		email: email,

      }

    }

  }).then(function(result) {

    if (result.error) {

		cardResult.innerHTML = result.error.message;

      // Show error to your customer (e.g., insufficient funds)

      console.log(result.error.message);

	  console.log(result);

    } else {

      // The payment has been processed!

	submitButton.style = 'display: none';

	cardFields.style = 'display: none';

      if (result.paymentIntent.status === 'succeeded') {

		  console.log(result);

		cardResult.innerHTML = '<?php _e('Recording payment','rsvpmaker');?> ...';

		const form = new FormData(document.getElementById('payee-form'));

		fetch(successurl, {

  method: 'POST', // or 'PUT'

  body: form,

})

		.then((response) => {

			return response.json();

		})

		.then((myJson) => {

			console.log(myJson);

			if(!myJson.name)			

				cardResult.innerHTML = '<?php _e('Payment processed, but may not have been recorded correctly','rsvpmaker');?>';

			else

				cardResult.innerHTML = '<?php _e('Payment processed for','rsvpmaker');?> '+myJson.name+', '+myJson.description+' <?php echo $currency_symbol?>'+myJson.amount+' '+myJson.currency.toUpperCase();//+myJson.payment_confirmation_message;

		});

      }

    }

  });

});

</script>

<?php

return ob_get_clean();

}



function stripe_log_by_email ($email, $months = 0) {

	global $wpdb;

	if(empty($email))

		return '';

	$log = '';

	$sql = "SELECT * FROM $wpdb->postmeta WHERE meta_key='rsvpmaker_stripe_payment' AND meta_value LIKE '%".$email."%' ORDER BY meta_id DESC";

	$results = $wpdb->get_results($sql);

	if(empty($results))

		return '';

	if($months)

		$start = strtotime('-'.$months.' months');

	foreach($results as $row) {

		$vars = unserialize($row->meta_value);

		$timestamp = strtotime($vars['timestamp']);

		if($months && ($timestamp < $start))

			{

			//$log .= 'stamp: '.date('Y-m-d',$timestamp)." is less than ";

			//$log .= 'start: '.date('Y-m-d',$start)."\n";

			break;

			}

			//$log .= 'stamp: '.date('Y-m-d',$timestamp)." is greater than ";

			//$log .= 'start: '.date('Y-m-d',$start)." \n";

		foreach($vars as $name => $value)

			{

				$log .= $name.': '.$value."\n";

			}

			$log .= "\n";

	}

	return wpautop($log);

}

	

function rsvpmaker_stripe_payment_log($vars,$confkey) {

global $post, $current_user, $wpdb;

$vars['timestamp'] = rsvpmaker_date('r');

if(!empty($vars['email']))

	rsvpmaker_stripe_notify($vars);

$rsvpmaker_stripe_checkout_page_id = get_option('rsvpmaker_stripe_checkout_page_id');

add_post_meta($rsvpmaker_stripe_checkout_page_id,'rsvpmaker_stripe_payment',$vars);

do_action('rsvpmaker_stripe_payment',$vars);

}



function rsvpmaker_stripe_notify($vars) {

	if(!empty($vars['rsvp_id']))

	{

		//if connected to rsvp, send payment confirmation

		rsvp_confirmation_after_payment($vars['rsvp_id']);

		return;

	}

	$keys = get_rsvpmaker_stripe_keys ();

	$public = $keys['pk'];

	$secret = $keys['sk'];

	$to = $keys['notify'];

	if(empty($to))

		return;

	$mail['to'] = $to;

	$mail['from'] = get_option('admin_email');

	$mail['fromname'] = get_option('blogname');

	$mail['html'] = '';

	foreach ($vars as $index => $value)

	{

		$mail['html'] .= sprintf('<div>%s: %s</div>',$index, $value);

	}

	$mail['subject'] = 'Stripe payment from '.$vars['name'];

	rsvpmailer($mail);

}



function rsvpmaker_stripe_report () {

	global $wpdb;

	echo '<h1>Stripe Charges</h1>';

	

	if(isset($_GET['history'])) {

		stripe_balance_history((int) $_GET['history']);

	}



	printf('<div style="padding: 5px; border: thin dotted #000;"><h3>Retrieve Transactions from Stripe Service</h3>

	<p>Includes fees, refunds, and payouts</p>

	<form method="get" action="%s"><input type="hidden" name="post_type" value="rsvpmaker" /><input type="hidden" name="page" value="rsvpmaker_stripe_report" />

	Up to <select name="history">

	<option value="10">10</option>

	<option value="20" selected="selected">20</option>

	<option value="50">50</option> 

	<option value="100">100</option> 

	</select>transactions<br />starting <input type="text" name="date" placeholder="YYYY-mm-dd"> (optional) <br /><input type="checkbox" name="payouts" value="1"> Show payouts to bank<br />

	<button>Get</button></form></div>',admin_url('edit.php'));



	echo '<p>The log below shows transactions as recorded on the website.</p>';



	$sql = "SELECT * FROM $wpdb->postmeta WHERE meta_key='rsvpmaker_stripe_payment' ORDER BY meta_id DESC";

	$results = $wpdb->get_results($sql);

	if(is_array($results))

	foreach($results as $row) {

		echo '<p>';

		$payment = unserialize($row->meta_value);

		foreach($payment as $index => $value)

			printf('<div>%s: %s</div>',$index,$value);

		echo '</p>';

	}

}



add_action('admin_menu','rsvpmaker_stripe_report_menu',99);

function rsvpmaker_stripe_report_menu () {

if(empty(get_option('rsvpmaker_stripe_keys')))

	return;

add_submenu_page('edit.php?post_type=rsvpmaker', __("RSVPMaker Stripe Report",'rsvpmaker'), __("RSVPMaker Stripe Report",'rsvpmaker'), 'edit_rsvpmakers', "rsvpmaker_stripe_report", "rsvpmaker_stripe_report" );

}



function stripe_balance_history ($limit = 20) {

	rsvpmaker_debug_log('call to stripe_balance_history');

	require_once('stripe-php/init.php');



	$keys = get_rsvpmaker_stripe_keys ();

	$public = $keys['pk'];

	$secret = $keys['sk'];

	\Stripe\Stripe::setApiKey($secret);

	

	\Stripe\Stripe::setAppInfo(

		"WordPress RSVPMaker events management plugin",

		get_rsvpversion(),

		"https://rsvpmaker.com"

	);

//use https://stripe.com/docs/api/balance/balance_history

$stripe = new \Stripe\StripeClient($secret);

$history = $stripe->balanceTransactions->all(['limit' => $limit]);



$charges = $stripe->charges->all(['limit' => $limit * 5]);

foreach($charges->data as $charge) {

	$names[$charge->balance_transaction] = $charge->billing_details->name.' '.$charge->billing_details->email;

	if(isset($_GET['charge_detail']))

		printf('<pre>%s</pre><br />',var_export($charge,true));

}



if(isset($_GET['date']))

	$startdate = strtotime($_GET['date']);

$table = '';

foreach($history->data as $index => $data) {

	if(($data->reporting_category == 'payout') && empty($_GET['payouts']))

		continue;

	if(isset($startdate) && ($data->created < $startdate))

		continue;

	$amount = number_format(($data->amount / 100),2);

	$fee = number_format(($data->fee / 100),2);

	$yield = $amount - $fee;

	$date = date('Y-m-d H:i',$data->created);

	$name = (empty($names[$data->id])) ? '' : $names[$data->id];

	$tablerow[$date.$data->id] = "$date,$name,$amount,$fee,$yield\n";

    if($data->fee)

        $fees[$data->id] = $fee;

    if($data->reporting_category == 'refund')

        $refunds[$data->id] = array('amount' => $amount, 'date' => $date);

    printf('<p>%s %s<br />%s<br />Fee: %s %s<br />%s</p>',$name, $date, number_format(($data->amount / 100),2),number_format(($data->fee / 100),2),$data->reporting_category, $data->id);

}

if(!empty($tablerow)) {
	ksort($tablerow);
	$table = implode("",$tablerow);
	echo  '<h3>Export Format</h3><pre>Date,Name,Amount,Fee,Yield'."\n".$table.'</pre>';
}

if(!empty($fees))

{

    echo '<h2>Fees</h2>';

    $feetotal = 0;

    foreach($fees as $index => $fee)

    {

        $feetotal += $fee;

        printf('<p>%s %s</p>',$index,$fee);

    }

    printf('<p>Total Fees: %s</p>',$feetotal);

}

if(!empty($refunds))

{

    echo '<h2>Refunds</h2>';

    $rtotal = 0;

    foreach($refunds as $index => $refund)

    {

        $rtotal += $refund['amount'];

        printf('<p>%s %s %s</p>',$index,$refund["amount"],$refund['date']);

    }

    printf('<p>Total Refunds: %s</p>',$rtotal);

}



}



?>