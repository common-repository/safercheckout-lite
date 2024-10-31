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

class SaferCheckout_filter_whitelist_customer {

	private $main;

	public function __construct( NinTechNet_SaferCheckout_Process $main ) {

		$this->main = $main;
		$this->run();
	}


	private function run() {

		/**
		 * Check whether the customer has placed one or more orders lately.
		 */
		if (! empty( $this->main->sc_options['customer_whitelist'] ) ) {

			/**
			 * Check if the customer has an ID or is unauthenticated.
			 */
			$customer_id = $this->main->order->get_customer_id();

			if ( empty( $customer_id ) && $this->main->sc_options['customer_whitelist'] == 'auth' ) {
				$this->main->log(
					'info',
					__('Skipping customer whitelist, customer is not authenticated.', 'safercheckout-lite')
				);

			} else {

				$this->main->log('info', __('Checking customer whitelist.', 'safercheckout-lite') );
				/**
				 * Look for previous orders in the DB.
				 */
				$tot	= explode(':', $this->main->sc_options['customer_whitelist_order'] );
				$date	= gmdate('Y-m-d', strtotime( "-{$tot[1]} days" ) );
				$args	= [
					'status'			=> 'wc-completed',
					'type'			=> 'shop_order',
					'date_before'	=> $date,
					'limit' 			=> $tot[0]
				];
				if ( empty( $customer_id ) ) {
					/**
					 * Search based on the email address if the user is unauthenticated.
					 */
					$args['billing_email'] = $this->main->user_email;

				} else {
					/**
					 * Search based on the customer ID if the user is authenticated.
					 */
					$args['customer_id']	= $customer_id;
				}

				$res = count( wc_get_orders( $args ) );

				if ( $res >= $tot[0] ) {
					$msg = sprintf(
						/* Translators: num of orders, num of days */
						__('The customer has placed %1$s or more orders, more than %2$s days ago.',
							'safercheckout-lite'),
						$res,
						$tot[1]
					);
					/**
					 * Stop processing and allow that order.
					 */
					$this->main->end_filter( $msg, 'allow');
					return;
				}
			}

		} else {
			$this->main->log(
				'info',
				__('Skipping customer whitelist, it is disabled.', 'safercheckout-lite')
			);
		}
	}

}

// ===========================================================================
// EOF
