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

class SaferCheckout_filter_blacklist_ip {

	private $main;

	public function __construct( NinTechNet_SaferCheckout_Process $main ) {

		$this->main = $main;
		$this->run();
	}


	private function run() {

		/**
		 * Ignore private IP addresses.
		 */
		if ( $this->main->private_ip === true ) {

			return;
		}

		/**
		 * Check whether the IP address is blacklisted.
		 */
		if (! empty( $this->main->sc_options['ip_blacklist'] ) ) {

			$this->main->log('info', __('Checking IP blacklist.', 'safercheckout-lite') );

			$res = $this->main->check_list_ip(
				$this->main->sc_options['ip_blacklist'],
				$this->main->user_ip
			);

			if ( $res ) {
				$msg = sprintf(
					/* Translators: IP address */
					__('The IP address is blacklisted: %s.', 'safercheckout-lite'),
					$this->main->user_ip
				);
				/**
				* Stop processing and block that order.
				*/
				$this->main->end_filter( $msg, 'block');
				return;
			}

		} else {
			$this->main->log('info', __('Skipping IP blacklist, it is empty.', 'safercheckout-lite') );
		}

	}

}

// ===========================================================================
// EOF
