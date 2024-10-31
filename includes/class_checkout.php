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

class NinTechNet_SaferCheckout_Process {

	public $sc_options	= [];
	public $results		= [];

	public $order;
	public $order_id;
	private $logger;

	public $risk_score	= 0;
	public $risk_note		= [];

	private $simulation	= '';

	public $user_email	= '';
	public $email_name	= '';
	public $email_domain	= '';
	public $email_tld		= '';
	public $email_dnsmx	= '';
	public $email_dnsa	= '';

	public $cached = [
		'tld'		=> false,
		'email'	=> false,
		'domain'	=> false,
		'ip'		=> false
	];

	public $cache_instance;
	public $time;

	public $user_ip		= '';
	public $private_ip	= false;
	public $ipv6			= false;
	public $asn				= '';
	public $country		= '';

	private $steps = [
		/**
		 * Pre-filters.
		 */
		'pre_filter',
		/**
		 * Whitelists & blacklists.
		 */
		'filter_whitelist_ip',
		'filter_whitelist_email',
		'filter_whitelist_customer',
		'filter_blacklist_order',
		'filter_blacklist_ip',
		'filter_blacklist_email',
		'filter_blacklist_address',
		'filter_blacklist_customer',
		/**
		 * Other filters.
		 */
		'filter_location'
	];


	public function __construct( $options, $order_id ) {

		$this->sc_options	= $options;
		$this->order		= wc_get_order( $order_id );
		$this->logger		= wc_get_logger();
		$this->order_id	= $this->order->get_id();
		$this->includes();
		$this->run_safercheckout();
	}


	/*
	 * Load required file.
	 */
	private function includes() {

		foreach( $this->steps as $step ) {
			require_once "class_{$step}.php";
		}
	}


	/**
	 * Run all security checks on the order and customers.
	 */
	private function run_safercheckout() {

		$this->time = time();

		$this->log(
			'info',
			sprintf(
				/* Translators: order ID */
				__('Order #%s is being placed, starting SaferCheckout.', 'safercheckout-lite'),
				$this->order_id
			)
		);

		foreach( $this->steps as $step ) {

			$this->results = [];
			$this->results['action'] = 'continue';

			$class = "SaferCheckout_{$step}";
			new $class( $this );

			/**
			 * Make sure the risk score didn't reach 100 yet, or stop here.
			 */
			if ( $this->risk_score >= 100 ) {

				$this->results['action'] = 'block';
			}

			if ( in_array( $this->results['action'], ['allow', 'block'] ) ) {

				$this->check_score();
				return;
			}
		}
		/**
		 * All done, let's check the results.
		 */
		$this->check_score();
	}


	/**
	 * Check the risk score and take action accordingly.
	 */
	private function check_score() {

		$status = '';

		if ( $this->risk_score <= $this->sc_options['score_low'] ) {

			$this->results['action'] = 'allow';
			$message = '✅ ' . sprintf(
				/* Translators: risk score */
				__('Low risk order: %s. Accepting it.', 'safercheckout-lite'),
				$this->risk_score
			);

		} elseif ( $this->risk_score <= $this->sc_options['score_medium'] ) {

			$this->results['action'] = 'allow';
			$message = '✅ ' . sprintf(
				/* Translators: risk score */
				__('Medium risk order: %s. Accepting it.', 'safercheckout-lite'),
				$this->risk_score,
			);

		} else {
			$this->results['action'] = 'block';
			$status  = $this->sc_options['status_high'];
			$message = '⛔ ' . sprintf(
				/* Translators: 1= risk score, 2= order status */
				__('High risk order: %1$s. Rejecting it and setting its status to "%2$s".', 'safercheckout-lite'),
				$this->risk_score,
				$status
			);
		}

		$this->log('info', $message );

		if ( $this->sc_options['simulation_mode'] ) {
			array_unshift( $this->risk_note, SAFERCHECKOUT_LITE_SIMULATION );
		}

		/**
		 * Prepend risk score and append action message.
		 */
		array_unshift( $this->risk_note, $this->risk_score );
		$this->risk_note[] = $message;

		/**
		 * Accept the order.
		 */
		if ( $this->results['action'] == 'allow' || $this->sc_options['simulation_mode'] ) {

			$this->order->update_meta_data('_safercheckout', $this->risk_note );
			$this->order->save();
			return;
		}

		/**
		 * Set the order status, unless it must be permanently deleted.
		 */
		 if ( $status == 'delete-permanently') {
			$this->order->delete( true );

		 } else {

			$this->order->update_status( $status );
			$this->order->update_meta_data('_safercheckout', $this->risk_note );
			$this->order->save();
		}

		/**
		 * Block the order.
		 */
		throw new Exception( esc_html( $this->sc_options['default_message'] ) );
	}


	/**
	 * End filtering the order.
	 */
	public function end_filter( $msg, $action ) {

		$this->log('info', $msg );

		if ( $action == 'allow' ) {
			/**
			 * Stop processing and accept that order.
			 */
			$this->risk_score				= 0;
			$this->results['action']	= 'allow';
			$this->risk_note[]			= "✅ $msg";

		} else {
			/**
			 * Stop processing and block that order.
			 */
			$this->risk_score				= 100;
			$this->results['action']	= 'block';
			$this->risk_note[]			= "⛔ $msg";
		}
	}


	/**
	 * Perform various checks on the user IP address.
	 */
	public function parse_user_ip() {

		/**
		 * Make sure the user selection is available,
		 * or fall back on REMOTE_ADDR.
		 */
		if ( empty( $_SERVER[ $this->sc_options['source_ip'] ] ) ) {
			$this->log(
				'warning',
				sprintf(
					/* Translators: IP address */
					__('%s IP address is invalid, falling back on REMOTE_ADDR.', 'safercheckout-lite'),
					$this->sc_options['source_ip']
				)
			);
			$this->sc_options['source_ip'] = 'REMOTE_ADDR';
		}
		/**
		 * Some routers still use uppercase IPv6.
		 */
		$this->user_ip = strtolower(
			sanitize_text_field( wp_unslash( $_SERVER[ $this->sc_options['source_ip'] ] ) )
		);

		/**
		 * Multiple fields.
		 */
		if ( strpos( $this->user_ip, ',') !== false) {
			$match = array_map('trim', explode(',', $this->user_ip ) );
			foreach( $match as $m ) {
				if ( filter_var( $m, FILTER_VALIDATE_IP ) )  {
					$this->user_ip = $m;
					break;
				}
			}
		}

		/**
		 * Public or private IP address.
		 */
		 if ( false === SaferCheckoutLite_helpers::is_private( $this->user_ip ) ) {
			$this->private_ip = false;

		} else {

			$this->private_ip = true;
			$this->log(
				'warning',
				sprintf(
					/* Translators: IP address */
					__('Source IP is private, cancelling additional checks on it: %s.', 'safercheckout-lite'),
					$this->user_ip
				)
			);
			/**
			 * We stop here, checking the rest would be a waste of time.
			 */
			return;
		}

		/**
		 * Correct IP address if required.
		 */
		$wc_ip = $this->order->get_customer_ip_address();
		if ( $wc_ip != $this->user_ip ) {
			$this->order->set_customer_ip_address( $this->user_ip );
			$this->log(
				'info',
				sprintf(
					/* Translators: 1=IP address, 2=IP address */
					__('Correcting source IP address in WC order: %1$s => %2$s.', 'safercheckout-lite'),
					$wc_ip,
					$this->user_ip
				)
			);
		}

		include_once __DIR__ .'/share/geoip.inc';

		if ( filter_var( $this->user_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			/**
			 * IPv6.
			 */
			$this->ipv6 		= true;
			$geoip_dat			= __DIR__ . '/share/GeoIPv6.dat';
			$geoip_dat_asn		= __DIR__ . '/share/GeoIPv6ASN.dat';
			$geoip_func			= 'safercheckout_geoip_country_code_by_addr_v6';
			$geoip_func_asn	= 'safercheckout_geoip_name_by_addr_v6';

		} else {
			/**
			 * IPv4.
			 */
			$this->ipv6 		= false;
			$geoip_dat			= __DIR__ . '/share/GeoIPv4.dat';
			$geoip_dat_asn		= __DIR__ . '/share/GeoIPv4ASN.dat';
			$geoip_func			= 'safercheckout_geoip_country_code_by_addr';
			$geoip_func_asn	= 'safercheckout_geoip_name_by_addr';
		}

		/**
		 * Get its AS number.
		 */
		$gi = safercheckout_geoip_open( $geoip_dat_asn, SAFERCHECKOUT_GEOIP_STANDARD );
		$this->asn = $geoip_func_asn( $gi, $this->user_ip );
		if ( empty( $this->asn ) ) {
			$this->log(
				'warning',
				sprintf(
					/* Translators: IP address */
					__('Unable to retrieve AS number for IP %s.', 'safercheckout-lite'),
					$this->user_ip
				)
			);
		}

		/**
		 * Geolocation.
		 */
		$gi = safercheckout_geoip_open( $geoip_dat, SAFERCHECKOUT_GEOIP_STANDARD );
		$this->country = strtoupper( $geoip_func( $gi, $this->user_ip ) );
		if ( empty( $this->country ) ) {
			$this->log(
				'warning',
				sprintf(
					/* Translators: IP address */
					__('Unable to retrieve country code for IP %s.', 'safercheckout-lite'),
					$this->user_ip
				)
			);
		}
	}


	/**
	 * Check whether an IP address/ASN is in a list (whitelist or blacklist).
	 */
	public function check_list_ip( $list, $user_ip ) {

		foreach ( $list as $item => $null ) {

			/**
			 * AS number.
			 */
			if ( $this->asn && strpos( $item, 'AS') === 0 ) {
				if ( strpos( $this->asn, "$item " ) === 0 ) {
					return true;
				}
				continue;
			}

			/**
			 * CIDR.
			 */
			$ip_cidr = explode('/', $item );
			if ( isset( $ip_cidr[1] ) ) {
				/**
				 * Quick IPv6 check.
				 */
				if ( strpos( $ip_cidr[0], ':') !== false ) {
					/**
					 * Compare IPv6.
					 */
					$res = $this->ipv6_range( $ip_cidr, $user_ip );
				} else {
					/**
					 * Compare IPv4.
					 */
					$res = $this->ipv4_range( $ip_cidr, $user_ip );
				}
				if ( $res ) {
					return true;
				}
				continue;
			}

			/**
			 * Plain IP address.
			 */
			if ( $user_ip == $item ) {
				return true;
			}
		}
	}


	/**
	 * IPv4 range check.
	 */
	private function ipv4_range( $ip_cidr, $user_ip ) {

		$ip			= ip2long( $user_ip );
		$ip_cidr[0]	= ip2long( $ip_cidr[0] );
		$mask			= -1 << ( 32 - $ip_cidr[1] );
		$ip_cidr[0]	&= $mask;
		return ( $ip & $mask ) == $ip_cidr[0];
	}


	/**
	 * IPv6 range check.
	 */
	private function ipv6_range( $ip_cidr, $user_ip ) {

		$remote_addr	= inet_pton( $user_ip );
		$ip_cidr[0] 	= inet_pton( $ip_cidr[0] );
		$addr 			= str_repeat('f',  $ip_cidr[1] / 4 );
		switch ( $ip_cidr[1] % 4 ) {
			case 0:
			break;
			case 1:
			$addr .= '8';
			break;
			case 2:
			$addr .= 'c';
			break;
			case 3:
			$addr .= 'e';
			break;
		}
		$addr = str_pad( $addr, 32, '0');
		return ( $remote_addr & pack( "H*" , $addr ) ) == $ip_cidr[0];
	}


	/**
	 * Check if an item is in a list (generic).
	 */
	public function check_list( $list, $item, $type = '==') {

		foreach( $list as $key => $null ) {

			if ( $type = '==' && $key == $item ) {
				return true;

			} elseif ( $type = 'strpos' && strpos( $item, $key ) !== FALSE ) {
				return true;

			} elseif ( $type = 'stripos' && stripos( $item, $key ) !== FALSE ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Write to WC log, if requested.
	 */
	public function log( $level, $message ) {

		$levels = [
			'info'		=> 3,
			'warning'	=> 2,
			'error'		=> 1
		];

		/**
		 * Check if we must log the event.
		 */
		if ( empty( $this->sc_options['wc_logger'] ) ||
			empty( $levels[ $level ] ) ||
			$levels[ $level ] > $this->sc_options['wc_logger'] ) {

			return;
		}

		if (! empty( $this->sc_options['simulation_mode'] ) ) {
			$message = SAFERCHECKOUT_LITE_SIMULATION . " $message";
		}

		$this->logger->log(
			$level,
			$message,
			['source' => SAFERCHECKOUT_LITE_LOGNAME ."-{$this->order_id}-"]
		);
	}


}

// ===========================================================================
// EOF
