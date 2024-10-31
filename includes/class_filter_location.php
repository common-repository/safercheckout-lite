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

class SaferCheckout_filter_location {

	private $main;


	public function __construct( NinTechNet_SaferCheckout_Process $main ) {

		$this->main = $main;
		$this->run();
	}

	private function run() {

		if ( empty( $this->main->sc_options['location_user_billing'] ) &&
			empty( $this->main->sc_options['location_shipping_billing'] ) ) {

			$this->main->log('info', __('Skipping location checks.', 'safercheckout-lite') );
			return;

		} else {
			$this->main->log('info', __('Running location checks.', 'safercheckout-lite') );
		}

		$billing_country	= strtoupper( $this->main->order->get_billing_country() );
		$shipping_country	= strtoupper( $this->main->order->get_shipping_country() );
		/**
		 * Use billing country if shipping country is missing.
		 */
		if ( empty( $shipping_country ) ) {
			$shipping_country = $billing_country;
		}

		/**
		 * Customer's IP address must match the billing country.
		 */
		if (! $this->main->private_ip &&
			$this->main->country &&
			$this->main->sc_options['location_user_billing'] &&
			! empty( $billing_country )
		) {

			if ( $this->main->country != $billing_country ) {
				$msg = sprintf(
					/* Translators: 1=country code, 2=country code, 3=country, 4=risk score */
					__('Customer location (%1$s => %2$s) does not match the billing country (%3$s). Risk score: +%4$s.', 'safercheckout-lite'),
					$this->main->user_ip,
					$this->main->country,
					$billing_country,
					$this->main->sc_options['location_user_billing_score']
				);
				$this->main->log('info', $msg );
				$this->main->risk_note[] = $msg;
				$this->main->risk_score += $this->main->sc_options['location_user_billing_score'];
			}
		}


		/**
		 * Billing country must match the shipping country.
		 */
		if ( $this->main->sc_options['location_shipping_billing'] &&
			! empty( $shipping_country )
		) {

			if ( $billing_country != $shipping_country ) {
				$msg = sprintf(
					/* Translators: 1=country code, 2=country code, 3=risk score */
					__('Billing country (%1$s) does not match shipping country (%2$s). Risk score: +%3$s.',
						'safercheckout-lite'),
					$billing_country,
					$shipping_country,
					$this->main->sc_options['location_shipping_billing_score']
				);
				$this->main->log('info', $msg );
				$this->main->risk_note[] = $msg;
				$this->main->risk_score += $this->main->sc_options['location_shipping_billing_score'];
			}
		}
	}

}

// ===========================================================================
// EOF
