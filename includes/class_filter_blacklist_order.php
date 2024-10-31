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
 +===========================================================================+ 2024-04-17
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================

class SaferCheckout_filter_blacklist_order {

	private $main;

	public function __construct( NinTechNet_SaferCheckout_Process $main ) {

		$this->main = $main;
		$this->run();
	}


	private function run() {

		$price		= explode(':', $this->main->sc_options['order_price_threshold'] );
		$quantity	= explode(':', $this->main->sc_options['order_quantity_threshold'] );

		if ( empty( $price[0] ) && empty( $price[1] ) &&
			empty( $quantity[0] ) && empty( $quantity[1] ) ) {

			$this->main->log(
				'info',
				__('Skipping order threshold options, they are disabled.', 'safercheckout-lite')
			);

			return;
		}

		$this->main->log(
			'info',
			__('Checking order threshold options.', 'safercheckout-lite')
		);

		/**
		 * Check the order price limits.
		 */
		$total_price = $this->main->order->get_total();
		if (! empty( $price[0] ) && $total_price < $price[0] ) {

			$msg = sprintf(
				/* Translators: 1=value, 2=value */
				__('The total order value (%1$s) is less than the minimum allowed value (%2$s).', 'safercheckout-lite'),
				$total_price,
				number_format( $price[0], 2 )
			);
			/**
			* Stop processing and block that order.
			*/
			$this->main->end_filter( $msg, 'block');
			return;

		} elseif (! empty( $price[1] ) && $total_price > $price[1] ) {

			$msg = sprintf(
				/* Translators: 1=value, 2=value */
				__('The total order value (%1$s) is greater than the maximum allowed value (%2$s).', 'safercheckout-lite'),
				$total_price,
				number_format( $price[1], 2 )
			);
			/**
			* Stop processing and block that order.
			*/
			$this->main->end_filter( $msg, 'block');
			return;
		}

		/**
		 * Check the order quantity limits.
		 */
		$total_items = $this->main->order->get_item_count();
		if (! empty( $quantity[0] ) && $total_items < $quantity[0] ) {

			$items = sprintf(
				/* Translators: total items */
				_n('%s item', '%s items', $total_items, 'safercheckout-lite'), $total_items
			);
			$quant = sprintf(
				/* Translators: total items */
				_n('%s item', '%s items', $quantity[0], 'safercheckout-lite'), $quantity[0]
			);
			$msg = sprintf(
				/* Translators: 1=value, 2=value */
				__('The total order quantity (%1$s) is less than the minimum allowed quantity (%2$s).', 'safercheckout-lite'),
				$items,
				$quant
			);
			/**
			* Stop processing and block that order.
			*/
			$this->main->end_filter( $msg, 'block');
			return;

		} elseif ( ! empty( $quantity[1] ) && $total_items > $quantity[1] ) {

			$items = sprintf(
				/* Translators: total items */
				_n('%s item', '%s items', $total_items, 'safercheckout-lite'), $total_items
			);
			$quant = sprintf(
				/* Translators: total items */
				_n('%s item', '%s items', $quantity[1], 'safercheckout-lite'), $quantity[1]
			);
			$msg = sprintf(
				/* Translators: 1=value, 2=value */
				__('The total order quantity (%1$s) is greater than the maximum allowed quantity (%2$s).', 'safercheckout-lite'),
				$items,
				$quant
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
