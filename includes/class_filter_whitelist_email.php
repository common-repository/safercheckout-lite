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

class SaferCheckout_filter_whitelist_email {

	private $main;

	public function __construct( NinTechNet_SaferCheckout_Process $main ) {

		$this->main = $main;
		$this->run();
	}


	private function run() {

		/**
		 * We must have an email address, obviously.
		 */
		if ( empty( $this->main->order->get_billing_email() ) ) {
			$this->main->log(
				'warning',
				__('No email address found, cancelling email whitelist verification.', 'safercheckout-lite'),
			);
			return;
		}

		/**
		 * Save it.
		 */
		$this->main->user_email = strtolower( trim( $this->main->order->get_billing_email() ) );


		/**
		 * Check if whitelist is empty.
		 */
		if ( empty( $this->main->sc_options['email_whitelist'] ) ) {

			$this->main->log('info', __('Skipping email whitelist, it is empty.', 'safercheckout-lite') );
			return;

		} else {
			$this->main->log('info', __('Checking email whitelist.', 'safercheckout-lite') );
		}

		$res = $this->main->check_list(
			$this->main->sc_options['email_whitelist'],
			$this->main->user_email,
			'stripos'
		);

		if ( $res == true ) {
			$msg = sprintf(
				/* Translators: email address */
				__('The user email address is whitelisted: %s.', 'safercheckout-lite'),
				$this->main->user_email
			);
			/**
			 * Stop processing and accept that order.
			 */
			$this->main->end_filter( $msg, 'allow');
			return;
		}
	}

}

// ===========================================================================
// EOF
