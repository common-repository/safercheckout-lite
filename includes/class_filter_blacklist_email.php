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

class SaferCheckout_filter_blacklist_email {

	private $main;

	public function __construct( NinTechNet_SaferCheckout_Process $main ) {

		$this->main = $main;
		$this->run();
	}


	private function run() {

		/**
		 * Check if blacklist is empty.
		 */
		if ( empty( $this->main->sc_options['email_blacklist'] ) ) {

			$this->main->log('info', __('Skipping email blacklist, it is empty.', 'safercheckout-lite') );
			return;

		} else {
			$this->main->log('info', __('Checking email blacklist.', 'safercheckout-lite') );
		}

		/**
		 * We must have an email address, obviously.
		 */
		if ( empty( $this->main->user_email ) ) {
			$this->main->log(
				'warning',
				__('No email address found, cancelling email blacklist verification.', 'safercheckout-lite'),
			);
			return;
		}

		$res = $this->main->check_list(
			$this->main->sc_options['email_blacklist'],
			$this->main->user_email,
			'stripos'
		);

		if ( $res == true ) {
			$msg = sprintf(
				/* Translators: email */
				__('The user email address is blacklisted: %s.', 'safercheckout-lite'),
				$this->main->user_email
			);
			/**
			* Stop processing and block that order.
			*/
			$this->main->end_filter( $msg, 'block');
			return;
		}
	}

}

// ===========================================================================
// EOF
