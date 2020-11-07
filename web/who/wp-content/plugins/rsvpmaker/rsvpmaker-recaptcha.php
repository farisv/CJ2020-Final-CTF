<?php
function rsvpmaker_recaptcha_output() {
global $rsvp_options;
if(!empty($rsvp_options["rsvp_recaptcha_site_key"]) && !empty($rsvp_options["rsvp_recaptcha_secret"]))
	{
?>
<div class="g-recaptcha" data-sitekey="<?php echo $rsvp_options["rsvp_recaptcha_site_key"]; ?>"></div>
<script type="text/javascript"
		src="https://www.google.com/recaptcha/api.js?hl=<?php echo get_locale(); ?>">
</script>
<?php	
	}

}

function rsvpmaker_recaptcha_check ($siteKey,$secret) {
require_once 'recaptcha-master/src/autoload.php';
if (!isset($_POST['g-recaptcha-response']))
	return false;
	$recaptcha = new \ReCaptcha\ReCaptcha($secret);
    $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
	if ($resp->isSuccess())
		return true;
	else
		return false;
}
?>