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

class SaferCheckout_filter_blacklist_address {

	private $main;

	public function __construct( NinTechNet_SaferCheckout_Process $main ) {

		$this->main = $main;
		$this->run();
	}


	private function run() {

		/**
		 * Country check.
		 */
		if (! empty( $this->main->sc_options['countries'] ) &&
		(
			! empty( $this->main->sc_options['address_blacklist_ip'] ) ||
			! empty( $this->main->sc_options['address_blacklist_bill'] ) ||
			! empty( $this->main->sc_options['address_blacklist_ship'] )
		) ) {

			$where	= [];
			$i18n		= [
				0 => __('IP address', 'safercheckout-lite'),
				1 => __('billing address', 'safercheckout-lite'),
				2 => __('shipping address', 'safercheckout-lite')
			];

			if (! empty( $this->main->sc_options['address_blacklist_ip'] ) &&
				$this->main->private_ip === false &&
				! empty( $this->main->country ) ) {

				$where[0] = $this->main->country;
			}

			if (! empty( $this->main->sc_options['address_blacklist_bill'] ) ) {

				$res = $this->main->order->get_billing_country();
				if (! empty( $res ) ) {
					$where[1] = $res;
				}
			}
			if (! empty( $this->main->sc_options['address_blacklist_ship'] ) ) {

				$res = $this->main->order->get_shipping_country();
				/**
				 * Use billing country if shipping country is missing.
				 */
				if ( empty( $res ) ) {
					$res = $this->main->order->get_billing_country();
				}
				if (! empty( $res ) ) {
					$where[2] = $res;
				}
			}

			/**
			 * Something is wrong, issue a warning.
			 */
			if (! $where ) {
				$this->main->log(
					'warning',
					__('Skipping location blacklist (no data found).', 'safercheckout-lite')
				);
				return;
			}

			$this->main->log('info', __('Checking location blacklist.', 'safercheckout-lite') );

			foreach( $where as $key => $value ) {

				$res = $this->main->check_list( $this->main->sc_options['countries'], $value, '==');

				if ( $res == true ) {
					$msg = sprintf(
						/* Translators: address type (IP, shipping, billing) => country */
						__('The country is blacklisted: %1$s => %2$s.', 'safercheckout-lite'),
						$i18n[ $key ],
						$value
					);
					/**
					* Stop processing and block that order.
					*/
					$this->main->end_filter( $msg, 'block');
					return;
				}
			}
		} else {
			$this->main->log('info', __('Skipping location blacklist.', 'safercheckout-lite') );
		}


		/**
		 * Billing and shipping addresses blacklist.
		 */
		if (! empty( $this->main->sc_options['order_blacklist_address'] ) &&
		(
			! empty( $this->main->sc_options['order_blacklist_bill'] ) ||
			! empty( $this->main->sc_options['order_blacklist_ship'] )
		) ) {

			$this->main->log('info', __('Running address checks.', 'safercheckout-lite') );

			/**
			 * Billing.
			 */
			if ( ! empty( $this->main->sc_options['order_blacklist_bill'] ) &&
				! empty( $this->main->order->get_billing_country() )
			) {

				$country	= $this->main->order->get_billing_country();
				$state	= $this->main->order->get_billing_state();
				if ( $country && $state ) {
					$state = WC()->countries->get_states( $country )[$state];
				} else {
					$state = '';
				}

				$billing = trim(
					$this->main->order->get_billing_address_1() .' '.
					$this->main->order->get_billing_address_2() .' '.
					$this->main->order->get_billing_city() .' '.
					$state .' '.
					$this->main->order->get_billing_postcode() .' '.
					$this->main->order->get_billing_phone()
				);

				$res = $this->main->check_list(
					$this->main->sc_options['order_blacklist_address'],
					$billing,
					'stripos'
				);

				if ( $res == true ) {
					$msg = sprintf(
						/* Translators: billing adress */
						__('The billing address is blacklisted: "%s".', 'safercheckout-lite'),
						$billing
					);
					/**
					* Stop processing and block that order.
					*/
					$this->main->end_filter( $msg, 'block');
					return;
				}
			}

			/**
			 * Shipping.
			 */
			if ( ! empty( $this->main->sc_options['order_blacklist_ship'] ) &&
				! empty( $this->main->order->get_shipping_country() )
			) {

				$country	= $this->main->order->get_shipping_country();
				$state	= $this->main->order->get_shipping_state();
				if ( $country && $state ) {
					$state = WC()->countries->get_states( $country )[$state];
				} else {
					$state = '';
				}

				$shipping = trim(
					$this->main->order->get_shipping_address_1() .' '.
					$this->main->order->get_shipping_address_2() .' '.
					$this->main->order->get_shipping_city() .' '.
					$state .' '.
					$this->main->order->get_shipping_postcode()
				);

				$res = $this->main->check_list(
					$this->main->sc_options['order_blacklist_address'],
					$shipping,
					'stripos'
				);

				if ( $res == true ) {
				$msg = sprintf(
						/* Translators: shipping address */
						__('The shipping address is blacklisted: "%s".', 'safercheckout-lite'),
						$shipping
					);
					/**
					* Stop processing and block that order.
					*/
					$this->main->end_filter( $msg, 'block');
					return;
				}
			}
		} else {
			$this->main->log('info', __('Skipping order address blacklist.', 'safercheckout-lite') );
		}
	}

}

// ===========================================================================
// EOF
