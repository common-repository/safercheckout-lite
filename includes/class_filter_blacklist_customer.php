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
 +===========================================================================+ 2024-06-14
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================

class SaferCheckout_filter_blacklist_customer {

	private $main;

	public function __construct( NinTechNet_SaferCheckout_Process $main ) {

		$this->main = $main;
		$this->run();
	}


	private function run() {

		/**
		 * Billing and shipping name/company blacklist.
		 */
		if (! empty( $this->main->sc_options['customer_blacklist_name'] ) &&
		(
			! empty( $this->main->sc_options['customer_blacklist_name_billing'] ) ||
			! empty( $this->main->sc_options['customer_blacklist_name_shipping'] )
		) ) {

			$this->main->log('info', __('Running name checks.', 'safercheckout-lite') );

			/**
			 * Billing name.
			 */
			$name = trim(
				$this->main->order->get_billing_first_name() .' '.
				$this->main->order->get_billing_last_name() .' '.
				$this->main->order->get_billing_company()
			);
			if ( ! empty( $this->main->sc_options['customer_blacklist_name_billing'] ) &&
				! empty( $name )
			) {

				$res = $this->main->check_list(
					$this->main->sc_options['customer_blacklist_name'],
					$name,
					'stripos'
				);

				if ( $res == true ) {
					$msg = sprintf(
						/* Translators: billing name */
						__('The billing name is blacklisted: "%s".', 'safercheckout-lite'),
						$name
					);
					/**
					* Stop processing and block that order.
					*/
					$this->main->end_filter( $msg, 'block');
					return;
				}

			}

			/**
			 * Shipping name.
			 */
			$name = trim(
				$this->main->order->get_shipping_first_name() .' '.
				$this->main->order->get_shipping_last_name() .' '.
				$this->main->order->get_shipping_company()
			);
			if ( ! empty( $this->main->sc_options['customer_blacklist_name_shipping'] ) &&
				! empty( $name )
			) {

				$res = $this->main->check_list(
					$this->main->sc_options['customer_blacklist_name'],
					$name,
					'stripos'
				);

				if ( $res == true ) {
				$msg = sprintf(
					/* Translators: shipping name */
						__('The shipping name is blacklisted: "%s".', 'safercheckout-lite'),
						$name
					);
					/**
					* Stop processing and block that order.
					*/
					$this->main->end_filter( $msg, 'block');
					return;
				}
			}
		} else {
			$this->main->log('info', __('Skipping order name blacklist.', 'safercheckout-lite') );
		}
	}

}

// ===========================================================================
// EOF
