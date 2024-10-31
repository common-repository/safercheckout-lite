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
 +===========================================================================+ 2024-04-14
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================

class SaferCheckout_pre_filter {

	private $main;

	public function __construct( NinTechNet_SaferCheckout_Process $main ) {

		$this->main = $main;
		$this->run();
	}


	private function run() {

		/**
		 * Check if the payment method should be filtered by SaferCheckout.
		 */
		if (! empty( $this->main->sc_options['payment_methods'][
			$this->main->order->get_payment_method()
		] ) ) {

			$msg = sprintf(
				/* Translators: payment method */
				__('The payment method is whitelisted: %s.', 'safercheckout-lite'),
				$this->main->order->get_payment_method_title()
			);
			/**
			 * Stop processing and allow that order.
			 */
			$this->main->end_filter( $msg, 'allow');
			return;
		}


		/**
		 * Get user IP address info.
		 */
		$this->main->parse_user_ip();
	}
}

// ===========================================================================
// EOF
