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
 +===========================================================================+ 2024-08-01
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================

class SaferCheckoutLite_helpers {

	public static $status	= 0;
	public static $date		= '';

	/**
	 * Hooks and constants.
	 */
	public static function init() {

		define('SAFERCHECKOUT_LITE_LOGNAME', 'safercheckout-order');
		define('SAFERCHECKOUT_LITE_SIMULATION',
			'🔴🔴 '. esc_html__('Simulation mode', 'safercheckout-lite') .' 🔴🔴');

		/**
		 * Actions & filters.
		 */
		add_action('admin_head', [ __CLASS__, 'safercheckout_hide_admin_notices'], 999);
	}

	/**
	 * Display an admin notice if the simulation mode is enabled.
	 */
	public static function safercheckout_is_simulation( $options ) {

		if (! empty( $options['simulation_mode'] ) ) {
			?>
			<div id="safercheckout-error-notice" class="notice notice-warning">
				<p><?php
				esc_html_e('SaferCheckout simulation mode is enabled. Your orders will not be filtered until you deactivate it.','safercheckout-lite');
				?></p>
			</div>
			<?php
		}
	}


	/**
	 * Return the total number of SaferCheckout's logs.
	 */
	public static function safercheckout_find_logs() {

		$count = 0;
		foreach ( new DirectoryIterator( WC_LOG_DIR ) as $finfo ) {
			if ( str_starts_with( $finfo->getFilename(), SAFERCHECKOUT_LITE_LOGNAME ) ) {
				$count++;
			}
		}
		return $count;
	}


	/**
	 * Return all available IP addresses for a visitor.
	 */
	public static function safercheckout_get_ip_list() {

		$ip_addresses = [];

		$checklist = [
			'REMOTE_ADDR',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CF_CONNECTING_IP',
			'HTTP_INCAP_CLIENT_IP',
			'HTTP_X_REAL_IP'
		];

		foreach ( $checklist as $var ) {

			if (! empty( $_SERVER[ $var ] ) ) {
				$ip = self::safercheckout_validate_ip(
					sanitize_text_field( wp_unslash( $_SERVER[ $var ] ) )
				);
				if ( $ip ) {
					$ip_addresses[ $var ] = $ip;
				}
			}
		}

		if ( empty( $ip_addresses ) ) {
			// PHP CLI
			$ip_addresses['REMOTE_ADDR'] = '127.0.0.1';
		}
		return $ip_addresses;
	}


	/**
	 * Validate one or multiple IP addresses.
	 */
	private static function safercheckout_validate_ip( $ip ) {

		if ( strpos( $ip, ',') !== false ) {
			$match = array_map('trim', explode(',', $ip ) );
			foreach( $match as $m ) {
				if ( filter_var( $m, FILTER_VALIDATE_IP ) )  {
					return $m;
				}
			}
		} else {
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) )  {
				return $ip;
			}
		}
	}


	/**
	 * Check if an IPv4/IPv6 address is private or reserved.
	 */
	public static function is_private( $ip ) {

		if ( filter_var(
			$ip, FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE )
		) {

			return false;
		}
		return true;
	}


	/**
	 * Fetch all ISO 3166 code and return them in an array.
	 */
	public static function safercheckout_get_iso31666() {

		return json_decode( file_get_contents( __DIR__ .'/share/iso3166.json'), true );
	}


	/**
	 * Populate the plugin's default options and save them to the database.
	 */
	public static function safercheckout_default_options() {

		$default_options = [
			'version_lite'								=> SAFERCHECKOUT_LITE_VERSION,
			// General Settings
			'default_message'							=> esc_html__(
																'Sorry, your request cannot be processed. Please check all fields and try again, or contact the Shop Manager.', 'safercheckout-lite'),
			'simulation_mode'							=> 0,
			// Risk Score
			'score_low'									=> 30,
			'score_medium'								=> 75,
			'status_high'								=> 'failed',
			// Payment Methods
			'payment_methods'							=> [],
			// IP Address
			'source_ip'									=> 'REMOTE_ADDR',
			'ip_whitelist'								=> [],
			'ip_blacklist'								=> [],
			'rdns'										=> 0,
			'rdns_score'								=> 20,
			'rdns_blacklist'							=> [],
			'dnsbl'										=> ['spamcop' => 0, 'spamhaus' => 1 ],
			'dnsbl_score'								=> 50,
			// Email Address
			'email_whitelist'							=> [],
			'email_blacklist'							=> [],
			'email_name'								=> 0,
			'email_name_score'						=> 70,
			'email_dns'									=> DNS_MX, // Options: DNS_MX + DNS_A + DNS_AAAA
			'email_dns_score'							=> 50,
			'email_reg'									=> 0,
			'email_reg_days'							=> 864000, // time in seconds
			'email_reg_score'							=> 50,
			// Location
			'countries'									=> [],
			'address_blacklist_ip'					=> 0,
			'address_blacklist_ship'				=> 0,
			'address_blacklist_bill'				=> 0,
			'location_user_billing'					=> 0,
			'location_user_billing_score'			=> 40,
			'location_shipping_billing'			=> 0,
			'location_shipping_billing_score'	=> 40,
			'order_blacklist_address'				=> [],
			'order_blacklist_bill'					=> 0,
			'order_blacklist_ship'					=> 0,
			// Customer
			'order_price_threshold'					=> '0:0',  // minimum:maximum
			'order_quantity_threshold'				=> '0:0',  // minimum:maximum
			'ua_suspicious_bot'						=> 1,
			'ua_suspicious_bot_score'				=> 35,
			'ua_suspicious_sig'						=> 1,
			'ua_suspicious_sig_score'				=> 35,
			'customer_blacklist_name'				=> [],
			'customer_blacklist_name_billing'	=> 0,
			'customer_blacklist_name_shipping'	=> 0,
			// Order
			'customer_whitelist'						=> '0', // 0: disabled, auth: authenticated, all: all
			'customer_whitelist_order'				=> '1:60', // orders_completed:days
			// Advanced Settings
			'hook_priority'							=> 1,
			'wc_logger'									=> 2 // 0: off, 1: errors, 2: errors + warnings, 3: all
		];

		return $default_options;
	}


	/**
	 * We don't want to be bothered by other themes/plugins' admin notices.
	 */
	public static function safercheckout_hide_admin_notices() {
		if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'safercheckout-lite') {
			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices');
		}
	}

}

SaferCheckoutLite_helpers::init();

// ===========================================================================
// EOF
