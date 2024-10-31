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
 +===========================================================================+ 2024-05-10
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================
// Parse and save the settings.

/**
 * Get the current options.
 */
$safercheckout_options = get_option('safercheckout');
if ( $safercheckout_options === false ) {
	$safercheckout_options = [];
}

/**
 * Get the the default ones.
 */
$default = SaferCheckoutLite_helpers::safercheckout_default_options();


// This code is just here to please the "Plugin check" as the nonce has already
// been checked in the main index.php...
if (! isset( $_POST['safercheckout-nonce'] ) ||
	! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['safercheckout-nonce'] ) ),
		'safercheckout-nonce'
	) ) {
	return;
}

/**
 * General settings.
 */
if ( isset( $_POST['safercheckout-settings'] ) &&
	$_POST['safercheckout-settings'] == 'settings-general' ) {

	// Default message
	if ( empty( $_POST['safercheckout_default_message'] ) ) {
		$safercheckout_options['default_message'] = $default['default_message'];
	} else {
		$safercheckout_options['default_message'] =
			sanitize_text_field( wp_unslash( $_POST['safercheckout_default_message'] ) );

		if (strlen( $safercheckout_options['default_message'] ) > 300 ) {

			$safercheckout_options['default_message'] =
				mb_substr( $safercheckout_options['default_message'], 0, 300, 'utf-8');
		}
	}

	// Simulation mode
	if (! empty( $_POST['safercheckout_simulation_mode'] ) ) {
		$safercheckout_options['simulation_mode'] = 1;
	} else {
		$safercheckout_options['simulation_mode'] = 0;
	}


/**
 * Risk scores and actions.
 */
} elseif ( isset( $_POST['safercheckout-settings'] ) &&
	$_POST['safercheckout-settings'] == 'settings-riskscore' ) {

	// Low: from 0 to 98
	if ( isset( $_POST['safercheckout_score_low'] ) ) {
		$value = (int) $_POST['safercheckout_score_low'];
	} else {
		$value = '';
	}
	if ( empty( $value ) ||	! preg_match('/^(0|[1-9]|[1-8][0-9]|9[0-8])$/D', $value ) ) {
		$safercheckout_options['score_low'] = $default['score_low'];

	} else {
		$safercheckout_options['score_low'] = $value;
	}

	// Medium: from 1 to 99
	if ( isset( $_POST['safercheckout_score_medium'] ) ) {
		$value = (int) $_POST['safercheckout_score_medium'];
	} else {
		$value = '';
	}
	if ( empty( $value ) ||	! preg_match('/^([1-9]|[1-8][0-9]|9[0-9])$/D', $value ) ) {
		$safercheckout_options['score_medium'] = $default['score_medium'];

	} else {
		$safercheckout_options['score_medium'] = $value;
	}

	// If medium is lower or equal to low, we decrement low
	if ( $safercheckout_options['score_medium'] <= $safercheckout_options['score_low'] ) {
		$safercheckout_options['score_low'] = $safercheckout_options['score_medium'] - 1;
	}

	if ( isset( $_POST['score_high_action'] ) ) {
		$value = sanitize_text_field( wp_unslash( $_POST['score_high_action'] ) );
	} else {
		$value = '';
	}
	if ( empty( $value ) ) {
		$safercheckout_options['status_high'] = $default['status_high'];
	} else {
		$safercheckout_options['status_high'] = $value;
	}


/**
 * Payments methods.
 */
} elseif (isset( $_POST['safercheckout-settings'] ) &&
	$_POST['safercheckout-settings'] == 'settings-methods' ) {

	// Payment methods
	$safercheckout_options['payment_methods'] = [];
	$wc_payment_methods = WC()->payment_gateways->payment_gateways();
	foreach( $wc_payment_methods as $method => $value ) {
		if ( empty( $_POST['safercheckout_payment_methods'][ $method ] ) ) {
			$safercheckout_options['payment_methods'][ $method ] = 1;
		}
	}


/**
 * IP address.
 */
} elseif ( isset( $_POST['safercheckout-settings'] ) &&
	$_POST['safercheckout-settings'] == 'settings-ip' ) {

	// User IP address
	if ( isset( $_POST['safercheckout_source_ip'] ) ) {
		$value = sanitize_text_field( wp_unslash( $_POST['safercheckout_source_ip'] ) );
	} else {
		$value = '';
	}
	if ( empty( $value ) || ! preg_match('/^[A-Z_]+$/D', $value ) ) {
		$safercheckout_options['source_ip'] = $default['source_ip'];

	} else {
		$safercheckout_options['source_ip'] = $value;
	}

	// IP address whitelist
	$safercheckout_options['ip_whitelist'] = [];
	if (! empty( $_POST['safercheckout_ip_whitelist'] ) ) {
		$items = explode(
			"\r\n",
			sanitize_textarea_field( wp_unslash( $_POST['safercheckout_ip_whitelist'] ) )
		);
		$items = array_unique( $items );
		sort( $items );
		foreach( $items as $item ) {
			if (preg_match('/^AS/i', $item ) ) {
				$item = strtoupper( trim( $item ) );
			} else {
				$item = strtolower( trim( $item ) );
			}
			if (! empty( $item ) ) {
				$safercheckout_options['ip_whitelist'][ $item ] = 1;
			}
		}
	}

	// IP address blacklist
	$safercheckout_options['ip_blacklist'] = [];
	if (! empty( $_POST['safercheckout_ip_blacklist'] ) ) {
		$items = explode(
			"\r\n",
			sanitize_textarea_field( wp_unslash( $_POST['safercheckout_ip_blacklist'] ) )
		);
		$items = array_unique( $items );
		sort( $items );
		foreach( $items as $item ) {
			if (preg_match('/^AS/i', $item ) ) {
				$item = strtoupper( trim( $item ) );
			} else {
				$item = strtolower( trim( $item ) );
			}
			if (! empty( $item ) ) {
				$safercheckout_options['ip_blacklist'][ $item ] = 1;
			}
		}
	}

	// Reverse DNS lookup
	if (! empty( $_POST['safercheckout_rdns'] ) ) {
		$safercheckout_options['rdns'] = 1;
	} else {
		$safercheckout_options['rdns'] = 0;
	}

	if ( isset( $_POST['safercheckout_rdns_score'] ) ) {
		$value = (int) $_POST['safercheckout_rdns_score'];
	} else {
		$value = '';
	}
	if ( empty( $value ) ||	! preg_match('/^([1-9]|[1-9][0-9]|100)$/D', $value ) ) {
		$safercheckout_options['rdns_score'] = $default['rdns_score'];
	} else {
		$safercheckout_options['rdns_score'] = $value;
	}

	// Reverse blacklist
	$safercheckout_options['rdns_blacklist'] = [];
	if (! empty( $_POST['safercheckout_rdns_blacklist'] ) ) {
		$items = explode(
			"\r\n",
			sanitize_textarea_field( wp_unslash( $_POST['safercheckout_rdns_blacklist'] ) )
		);
		$items = array_unique( $items );
		sort( $items );
		foreach( $items as $item ) {
			$item = strtolower( trim( $item ) );
			if (! empty( $item ) ) {
				$safercheckout_options['rdns_blacklist'][ $item ] = 1;
			}
		}
	}

	// DNSBL
	if (! empty( $_POST['safercheckout_dnsbl_spamhaus'] ) ) {
		$safercheckout_options['dnsbl']['spamhaus'] = 1;
	} else {
		$safercheckout_options['dnsbl']['spamhaus'] = 0;
	}
	if (! empty( $_POST['safercheckout_dnsbl_spamcop'] ) ) {
		$safercheckout_options['dnsbl']['spamcop'] = 1;
	} else {
		$safercheckout_options['dnsbl']['spamcop'] = 0;
	}

	if ( isset( $_POST['safercheckout_dnsbl_score'] ) ) {
		$value = (int) $_POST['safercheckout_dnsbl_score'];
	} else {
		$value = '';
	}
	if ( empty( $value ) || ! preg_match('/^([1-9]|[1-9][0-9]|100)$/D', $value ) ) {
		$safercheckout_options['dnsbl_score'] = $default['dnsbl_score'];
	} else {
		$safercheckout_options['dnsbl_score'] = $value;
	}


/**
 * Email address.
 */
} elseif ( isset( $_POST['safercheckout-settings'] ) &&
	$_POST['safercheckout-settings'] == 'settings-email' ) {

	// Customer email
	$safercheckout_options['email_whitelist'] = [];
	if (! empty( $_POST['safercheckout_email_whitelist'] ) ) {
		$items = explode(
			"\r\n",
			sanitize_textarea_field( wp_unslash( $_POST['safercheckout_email_whitelist'] ) )
		);
		$items = array_unique( $items );
		sort( $items );
		foreach( $items as $item ) {
			$item = strtolower( trim( $item ) );
			if (! empty( $item ) ) {
				$safercheckout_options['email_whitelist'][ $item ] = 1;
			}
		}
	}
	$safercheckout_options['email_blacklist'] = [];
	if (! empty( $_POST['safercheckout_email_blacklist'] ) ) {
		$items = explode(
			"\r\n",
			sanitize_textarea_field( wp_unslash( $_POST['safercheckout_email_blacklist'] ) )
		);
		$items = array_unique( $items );
		sort( $items );
		foreach( $items as $item ) {
			$item = strtolower( trim( $item ) );
			if (! empty( $item ) ) {
				$safercheckout_options['email_blacklist'][ $item ] = 1;
			}
		}
	}
	if (! empty( $_POST['safercheckout_email_name'] ) ) {
		$safercheckout_options['email_name'] = 1;
	} else {
		$safercheckout_options['email_name'] = 0;
	}

	if ( isset( $_POST['safercheckout_email_name_score'] ) ) {
		$value = (int) $_POST['safercheckout_email_name_score'];
	} else {
		$value = '';
	}
	if ( empty( $value ) ||	! preg_match('/^([1-9]|[1-9][0-9]|100)$/D', $value ) ) {
		$safercheckout_options['email_name_score'] = $default['email_name_score'];
	} else {
		$safercheckout_options['email_name_score'] = $value;
	}

	if ( isset( $_POST['safercheckout_email_dns'] ) ) {
		$value = (int) $_POST['safercheckout_email_dns'];
	} else {
		$value = '';
	}
	if (! isset( $value ) || ! preg_match('/^\d+$/D', $value ) ) {
		$safercheckout_options['email_dns'] = $default['email_dns'];
	} else {
		$safercheckout_options['email_dns'] = $value;
	}

	if ( isset( $_POST['safercheckout_email_dns_score'] ) ) {
		$value = (int) $_POST['safercheckout_email_dns_score'];
	} else {
		$value = '';
	}
	if ( empty( $value ) ||	! preg_match('/^([1-9]|[1-9][0-9]|100)$/D', $value ) ) {
		$safercheckout_options['email_dns_score'] = $default['email_dns_score'];
	} else {
		$safercheckout_options['email_dns_score'] = $value;
	}

	if (! empty( $_POST['safercheckout_email_reg'] ) ) {
		$safercheckout_options['email_reg'] = 1;
	} else {
		$safercheckout_options['email_reg'] = 0;
	}

	if ( isset( $_POST['safercheckout_email_reg_score'] ) ) {
		$value = (int) $_POST['safercheckout_email_reg_score'];
	} else {
		$value = '';
	}
	if ( empty( $value ) ||	! preg_match('/^([1-9]|[1-9][0-9]|100)$/D', $value ) ) {
		$safercheckout_options['email_reg_score'] = $default['email_reg_score'];
	} else {
		$safercheckout_options['email_reg_score'] = $value;
	}

	if ( isset( $_POST['safercheckout_email_reg_days'] ) ) {
		$value = (int) $_POST['safercheckout_email_reg_days'];
	} else {
		$value = '';
	}
	if ( empty( $value ) || ! preg_match('/^([1-9]|[1-9][0-9][0-9]?|1000?)$/D', $value ) ) {
		$safercheckout_options['email_reg_days'] = $default['email_reg_days'];
	} else {
		$safercheckout_options['email_reg_days'] = $value *86400;
	}


/**
 * Location.
 */
} elseif ( isset( $_POST['safercheckout-settings'] ) &&
	$_POST['safercheckout-settings'] == 'settings-location' ) {

	$safercheckout_options['countries'] = [];
	if ( isset( $_POST['safercheckout_countries'] ) ) {
		$array_value = array_map('sanitize_text_field', wp_unslash( $_POST['safercheckout_countries'] ) );
	} else {
		$array_value = [];
	}
	if (! empty( $array_value ) ) {
		foreach( $array_value as $isocode => $null ) {
			$safercheckout_options['countries'][ $isocode ] = 1;
		}
	}
	if (! empty( $_POST['safercheckout_address_blacklist_ip'] ) &&
		! empty( $safercheckout_options['countries'] )
	) {

		$safercheckout_options['address_blacklist_ip'] = 1;
	} else {
		$safercheckout_options['address_blacklist_ip'] = 0;
	}
	if (! empty( $_POST['safercheckout_address_blacklist_ship'] ) &&
		! empty( $safercheckout_options['countries'] )
	) {

		$safercheckout_options['address_blacklist_ship'] = 1;
	} else {
		$safercheckout_options['address_blacklist_ship'] = 0;
	}
	if (! empty( $_POST['safercheckout_address_blacklist_bill'] ) &&
		! empty( $safercheckout_options['countries'] )
	) {

		$safercheckout_options['address_blacklist_bill'] = 1;
	} else {
		$safercheckout_options['address_blacklist_bill'] = 0;
	}

	// Location matching
	if (! empty( $_POST['safercheckout_location_user_billing'] ) ) {

		$safercheckout_options['location_user_billing'] = 1;
	} else {
		$safercheckout_options['location_user_billing'] = 0;
	}

	if ( isset( $_POST['safercheckout_location_user_billing_score'] ) ) {
		$value = (int) $_POST['safercheckout_location_user_billing_score'];
	} else {
		$value = '';
	}
	if ( empty( $value ) || ! preg_match('/^([1-9]|[1-9][0-9]|100)$/D', $value	) ) {
		$safercheckout_options['location_user_billing_score'] = $default['location_user_billing_score'];
	} else {
		$safercheckout_options['location_user_billing_score'] = $value;
	}

	if (! empty( $_POST['safercheckout_location_shipping_billing'] ) ) {
		$safercheckout_options['location_shipping_billing'] = 1;
	} else {
		$safercheckout_options['location_shipping_billing'] = 0;
	}

	if ( isset( $_POST['safercheckout_location_shipping_billing_score'] ) ) {
		$value = (int) $_POST['safercheckout_location_shipping_billing_score'];
	} else {
		$value = '';
	}
	if ( empty( $value ) ||	! preg_match('/^([1-9]|[1-9][0-9]|100)$/D', $value ) ) {
		$safercheckout_options['location_shipping_billing_score'] =
			$default['location_shipping_billing_score'];
	} else {
		$safercheckout_options['location_shipping_billing_score'] = $value;
	}

	// Address blacklist
	$safercheckout_options['order_blacklist_address'] = [];
	if (! empty( $_POST['safercheckout_order_blacklist_address'] ) ) {
		$items = explode(
			"\r\n",
			sanitize_textarea_field( wp_unslash( $_POST['safercheckout_order_blacklist_address'] ) )
		);
		$items = array_unique( $items );
		sort( $items );
		foreach( $items as $item ) {
			$item = strtolower( trim( $item ) );
			if (! empty( $item ) ) {
				$safercheckout_options['order_blacklist_address'][ $item ] = 1;
			}
		}
	}

	if (! empty( $_POST['safercheckout_order_blacklist_ship'] ) &&
		! empty( $safercheckout_options['order_blacklist_address'] )
	) {

		$safercheckout_options['order_blacklist_ship'] = 1;
	} else {
		$safercheckout_options['order_blacklist_ship'] = 0;
	}
	if (! empty( $_POST['safercheckout_order_blacklist_bill'] ) &&
		! empty( $safercheckout_options['order_blacklist_address'] )
	) {

		$safercheckout_options['order_blacklist_bill'] = 1;
	} else {
		$safercheckout_options['order_blacklist_bill'] = 0;
	}


/**
 * Customer settings.
 */
} elseif ( isset( $_POST['safercheckout-settings'] ) &&
	$_POST['safercheckout-settings'] == 'settings-customer' ) {

	if (! empty( $_POST['safercheckout_ua_suspicious_bot'] ) ) {
		$safercheckout_options['ua_suspicious_bot'] = 1;
	} else {
		$safercheckout_options['ua_suspicious_bot'] = 0;
	}

	if ( isset( $_POST['safercheckout_ua_suspicious_bot_score'] ) ) {
		$value = (int) $_POST['safercheckout_ua_suspicious_bot_score'];
	} else {
		$value = '';
	}
	if ( empty( $value ) ||	! preg_match('/^(?:[0-9]|[1-9][0-9]|100)$/D', $value ) ) {
		$safercheckout_options['ua_suspicious_bot_score'] = $default['ua_suspicious_bot_score'];
	} else {
		$safercheckout_options['ua_suspicious_bot_score'] = $value;
	}
	if (! empty( $_POST['safercheckout_ua_suspicious_sig'] ) ) {
		$safercheckout_options['ua_suspicious_sig'] = 1;
	} else {
		$safercheckout_options['ua_suspicious_sig'] = 0;
	}

	if ( isset( $_POST['safercheckout_ua_suspicious_sig_score'] ) ) {
		$value = (int) $_POST['safercheckout_ua_suspicious_sig_score'];
	} else {
		$value = '';
	}
	if ( empty( $value ) || ! preg_match('/^(?:[0-9]|[1-9][0-9]|100)$/D', $value ) ) {
		$safercheckout_options['ua_suspicious_sig_score'] = $default['ua_suspicious_sig_score'];
	} else {
		$safercheckout_options['ua_suspicious_sig_score'] = $value;
	}

	if ( isset( $_POST['safercheckout_customer_whitelist'] ) ) {
		$value = sanitize_text_field( wp_unslash( $_POST['safercheckout_customer_whitelist'] ) );
	} else {
		$value = '';
	}
	if (! isset( $value ) || ! in_array( $value, [0, 'all', 'auth'] ) ) {
		$safercheckout_options['customer_whitelist'] = $default['customer_whitelist'];
	} else {
		$safercheckout_options['customer_whitelist'] = $value;
	}

	$tot_orders	= 0;
	$tot_days	= 0;
	if ( isset( $_POST['safercheckout_customer_whitelist_order_1'] ) ) {
		$tot_orders	= (int) $_POST['safercheckout_customer_whitelist_order_1'];
	}
	if ( isset( $_POST['safercheckout_customer_whitelist_order_2'] ) ) {
		$tot_days = (int) $_POST['safercheckout_customer_whitelist_order_2'];
	}
	if ( empty( $tot_orders ) || empty( $tot_days ) ) {
		$safercheckout_options['customer_whitelist']			= 0;
		$safercheckout_options['customer_whitelist_order']	= '';

	} else {
		$safercheckout_options['customer_whitelist_order'] = "$tot_orders:$tot_days";
	}

	// First/Last name blacklist
	$safercheckout_options['customer_blacklist_name'] = [];
	if (! empty( $_POST['safercheckout_customer_blacklist_name'] ) ) {
		$items = explode(
			"\r\n",
			sanitize_textarea_field( wp_unslash($_POST['safercheckout_customer_blacklist_name'] ) )
		);
		$items = array_unique( $items );
		sort( $items );
		foreach( $items as $item ) {
			$item = strtolower( trim( $item ) );
			if (! empty( $item ) ) {
				$safercheckout_options['customer_blacklist_name'][ $item ] = 1;
			}
		}
	}
	if (! empty( $_POST['safercheckout_customer_blacklist_name_shipping'] ) &&
		! empty( $safercheckout_options['customer_blacklist_name'] )
	) {

		$safercheckout_options['customer_blacklist_name_shipping'] = 1;
	} else {
		$safercheckout_options['customer_blacklist_name_shipping'] = 0;
	}
	if (! empty( $_POST['safercheckout_customer_blacklist_name_billing'] ) &&
		! empty( $safercheckout_options['customer_blacklist_name'] )
	) {

		$safercheckout_options['customer_blacklist_name_billing'] = 1;
	} else {
		$safercheckout_options['customer_blacklist_name_billing'] = 0;
	}


/**
 * Order settings.
 */
} elseif ( isset( $_POST['safercheckout-settings'] ) &&
	$_POST['safercheckout-settings'] == 'settings-order' ) {

	$price_min = 0;
	$price_max = 0;
	if ( isset( $_POST['safercheckout_order_price_threshold_1'] ) ) {
		$price_min	= (int) $_POST['safercheckout_order_price_threshold_1'];
	}
	if ( isset( $_POST['safercheckout_order_price_threshold_2'] ) ) {
		$price_max	= (int) $_POST['safercheckout_order_price_threshold_2'];
	}
	if ( $price_min > $price_max ) {
		$price_min = $price_max;
	}
	$safercheckout_options['order_price_threshold'] = "$price_min:$price_max";

	$quantity_min = 0;
	$quantity_max = 0;
	if ( isset( $_POST['safercheckout_order_quantity_threshold_1'] ) ) {
		$quantity_min	= (int) $_POST['safercheckout_order_quantity_threshold_1'];
	}
	if ( isset( $_POST['safercheckout_order_quantity_threshold_2'] ) ) {
		$quantity_max	= (int) $_POST['safercheckout_order_quantity_threshold_2'];
	}
	if ( $quantity_min > $quantity_max ) {
		$quantity_min = $quantity_max;
	}
	$safercheckout_options['order_quantity_threshold'] = "$quantity_min:$quantity_max";


/**
 * Avanced settings.
 */
} elseif ( isset( $_POST['safercheckout-settings'] ) &&
	$_POST['safercheckout-settings'] == 'settings-advanced' ) {

	// Hook priority (could be 0, or any positive/negative integer)
	if (! isset( $_POST['safercheckout_hook_priority'] ) ) {
		$safercheckout_options['hook_priority'] = $default['hook_priority'];
	} else {
		$safercheckout_options['hook_priority'] = (int) $_POST['safercheckout_hook_priority'];
	}

	// Debugging (0: disabled, 1: errors, 2: 1 + warnings, 3: 2 + notices)
	if ( isset( $_POST['safercheckout_wc_logger'] ) ) {
		$value = (int) $_POST['safercheckout_wc_logger'];
	} else {
		$value = '';
	}
	if (! isset( $value ) || ! preg_match('/^[0-3]$/D', $value ) ) {
		$safercheckout_options['wc_logger'] = $default['wc_logger'];
	} else {
		$safercheckout_options['wc_logger'] = $value;
	}
}

update_option('safercheckout', $safercheckout_options );

// ===========================================================================
// EOF
