<?php
/**
 +===========================================================================+
 |    ____         __            ____ _               _               _      |
 |   / ___|  __ _ / _| ___ _ __ / ___| |__   ___  ___| | _____  _   _| |_    |
 |   \___ \ / _` | |_ / _ \ '__| |   | '_ \ / _ \/ __| |/ / _ \| | | | __|   |
 |    ___) | (_| |  _|  __/ |  | |___| | | |  __/ (__|   < (_) | |_| | |_    |
 |   |____/ \__,_|_|  \___|_|   \____|_| |_|\___|\___|_|\_\___/ \__,_|\__|   |
 |                                                                           |
 | (c) NinTechNet Limited ~ https://nintechnet.com/safercheckout/            |
 +===========================================================================+ 2024-06-29
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================
// Display the "General Settings" page.
?>
<div class="card">
	<p style="text-align:center;font-size: 1.6em; font-weight: bold">SaferCheckout (Lite) v<?php echo esc_html( SAFERCHECKOUT_LITE_VERSION ) ?></p>
	<p style="text-align:center"><img style="border-radius:8px" src="<?php echo esc_url( plugins_url('/static/logo_128x128.png', dirname( __FILE__ ) ) ) ?>" /></p>
	<p style="text-align:center;font-size: 1.1em;">&copy; <?php echo esc_html( gmdate('Y') ) ?> <a href="https://nintechnet.com/" target="_blank" rel="noreferrer noopener" title="The Ninja Technologies Network"><strong>NinTechNet Limited</strong></a><br />The Ninja Technologies Network</p>
	<font style="font-size: 1.1em;">
		<p><?php esc_html_e('Go Pro:', 'safercheckout-lite') ?> <a href="https://nintechnet.com/safercheckout/" target="_blank">https://nintechnet.com/safercheckout/</a></p>
		<p><?php esc_html_e('Our privacy policy:', 'safercheckout-lite') ?> <a href="https://nintechnet.com/about/privacy-policy/" target="_blank">https://nintechnet.com/about/privacy-policy/</a></p>
	</font>
</div>
<?php

// ===========================================================================
// EOF
