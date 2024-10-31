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
 +===========================================================================+ 2024-09-30
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================
// Display the "Pro Features" page.
?>
<div class="wrap about-wrap full-width-layout">
	<br />
	<h1><?php esc_html_e('Need more security?', 'safercheckout-lite') ?></h1>
	<p class="about-text"><?php
		 esc_html_e('Take the time to explore SaferCheckout Pro. It offers several advanced and unique features to better protect your WooCommerce store.','safercheckout-lite');
	?></p>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Import & Export', 'safercheckout-lite') ?></h3>
			<p><?php esc_html_e('Effortlessly import and export your configuration and share it among multiple stores.', 'safercheckout-lite') ?>
		</div>
		<div class="column">
			<a href="<?php echo esc_url( plugins_url('/static/SaferCheckout_General_Settings.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url('/static/SaferCheckout_General_Settings.png', dirname( __FILE__ ) ) ) ?>" class="sco-pro" title="<?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo esc_url( plugins_url('/static/SaferCheckout_Risk_Score.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url('/static/SaferCheckout_Risk_Score.png', dirname( __FILE__ ) ) ) ?>" class="sco-pro" title="<?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Risk score action', 'safercheckout-lite') ?></h3>
			<p><?php esc_html_e('That option lets you define the action to perform when a high risk order is detected: You can set the order status, or move it to the trash or even permanently delete it.', 'safercheckout-lite') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('IP address rules', 'safercheckout-lite') ?></h3>
			<p><?php esc_html_e('SaferCheckout Pro can run a reverse DNS lookup on the customer IP address, then run a forward DNS lookup on the domain name returned to make sure that it matches the IP address.', 'safercheckout-lite') ?></p>
			<p><?php esc_html_e('You can permanently block the reverse DNS (domain name) of an IP address, or any part of it with the rDNS blacklist.', 'safercheckout-lite') ?></p>
			<p><?php esc_html_e('It can check if the IP address is blacklisted by DNSBL services such as Spamhaus and Spamcop. A DNSBL (Domain Name System Blacklist) is a service that contains IP addresses identified as sending spam, hosting malicious content, hijacking IP space, or acting like a bulletproof hosting company.', 'safercheckout-lite') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo esc_url( plugins_url('/static/SaferCheckout_IP_Address.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url('/static/SaferCheckout_IP_Address.png', dirname( __FILE__ ) ) ) ?>" class="sco-pro" title="<?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo esc_url( plugins_url('/static/SaferCheckout_Email_Address.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url('/static/SaferCheckout_Email_Address.png', dirname( __FILE__ ) ) ) ?>" class="sco-pro" title="<?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Email address rules', 'safercheckout-lite') ?></h3>
			<p><?php esc_html_e('SaferCheckout Pro can perform a strict verification of your customer\'s email address. It can connect to the SMTP server of the email address to verify whether the user exists or not. It can also verify if it has a proper MX record (mail exchanger). MX records are used to specify the mail server responsible for receiving emails on behalf of a domain. Without it, an email address can\'t receive messages.', 'safercheckout-lite') ?></p>
			<p><?php esc_html_e('Because some bad actors register domain names and immediately use them to create new email addresses in order to bypass blacklists and filters, SaferCheckout Pro can check when the domain name associated with the email address was registered and increase the order\'s risk score if it is younger than your selected choice.', 'safercheckout-lite') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Bots and user agents rules', 'safercheckout-lite') ?></h3>
			<p><?php esc_html_e('Those option are useful to detect bots, scanners, various malicious scripts, bad actors and their suspicious behaviour accessing the checkout page. You can configure the risk score to apply in case of a positive detection.', 'safercheckout-lite') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo esc_url( plugins_url('/static/SaferCheckout_Customer.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url('/static/SaferCheckout_Customer.png', dirname( __FILE__ ) ) ) ?>" class="sco-pro" title="<?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo esc_url( plugins_url('/static/SaferCheckout_Advanced_Settings.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url('/static/SaferCheckout_Advanced_Settings.png', dirname( __FILE__ ) ) ) ?>" class="sco-pro" title="<?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'safercheckout-lite') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Caching', 'safercheckout-lite') ?></h3>
			<p><?php esc_html_e('For faster processing, SaferCheckout Pro uses caching.', 'safercheckout-lite') ?></p>
		</div>
	</div>

	<hr />

	<h3><b><a href="https://nintechnet.com/safercheckout/" target="_blank" rel="noopener"><?php esc_html_e('Learn more about SaferCheckout Pro unique features.', 'safercheckout-lite') ?></a></b></h3>

</div>

<?php
// ---------------------------------------------------------------------
// EOF
