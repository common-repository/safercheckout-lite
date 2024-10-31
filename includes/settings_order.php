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
 +===========================================================================+ 2024-05-10
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================
// Display the "Order" page.

wp_nonce_field('safercheckout-nonce', 'safercheckout-nonce', 0);
?>
<input type="hidden" id="safercheckout-settings" name="safercheckout-settings" value="settings-order" />

<h2><?php esc_html_e('Order', 'safercheckout-lite') ?></h2>
<table class="form-table sco-border-grey">
<tbody>
	<?php
	// Purchase price threshold
	if (! isset( $this->sc_options['order_price_threshold'] ) ||
		! preg_match('/^\d+:\d+$/D', $this->sc_options['order_price_threshold'] ) ) {

		$this->sc_options['order_price_threshold'] = $this->sc_default['order_price_threshold'];
	}
	$price = explode(':', $this->sc_options['order_price_threshold'] );
	// Order quantity threshold
	if (! isset( $this->sc_options['order_quantity_threshold'] ) ||
		! preg_match('/^\d+:\d+$/D', $this->sc_options['order_quantity_threshold'] ) ) {

		$this->sc_options['order_quantity_threshold'] = $this->sc_default['order_quantity_threshold'];
	}
	$quantity = explode(':', $this->sc_options['order_quantity_threshold'] );
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Order value limits', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('This is the minimum and maximum amount that the shoppers need to spend to checkout successfully, otherwise the order will be rejected.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<p><label><?php esc_html_e('Minimum order value:', 'safercheckout-lite');	?>
			&nbsp;<input type="number" name="safercheckout_order_price_threshold_1" id="safercheckout_order_price_threshold_1" value="<?php echo esc_attr( $price[0] ) ?>" style="width:90px;" min="0" step="1" />
			</label></p>
			<p class="description"><?php esc_html_e('Set this policy to 0 if you want to disable it.', 'safercheckout-lite') ?></p>
			<br />
			<p><label><?php esc_html_e('Maximum order value:', 'safercheckout-lite');	?>
			&nbsp;<input type="number" name="safercheckout_order_price_threshold_2" id="safercheckout_order_price_threshold_2" value="<?php echo esc_attr( $price[1] ) ?>" style="width:90px;" min="0" step="1" />
			<p class="description"><?php esc_html_e('Set this policy to 0 if you want to disable it.', 'safercheckout-lite') ?></p>
			</label></p>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Order quantity limits', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('This is the minimum and maximum purchase quantity required for a product across all variations in a single order. If the quantity is outside that range, the order will be rejected.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<p><label><?php esc_html_e('Minimum order quantity:', 'safercheckout-lite');	?>
			&nbsp;<input type="number" name="safercheckout_order_quantity_threshold_1" id="safercheckout_order_quantity_threshold_1" value="<?php echo esc_attr( $quantity[0] ) ?>" style="width:90px;" min="0" step="1" />
			</label></p>
			<p class="description"><?php esc_html_e('Set this policy to 0 if you want to disable it.', 'safercheckout-lite') ?></p>
			<br />
			<p><label><?php esc_html_e('Maximum order quantity:', 'safercheckout-lite');	?>
			&nbsp;<input type="number" name="safercheckout_order_quantity_threshold_2" id="safercheckout_order_quantity_threshold_2" value="<?php echo esc_attr( $quantity[1] ) ?>" style="width:90px;" min="0" step="1" />
			</label></p>
			<p class="description"><?php esc_html_e('Set this policy to 0 if you want to disable it.', 'safercheckout-lite') ?></p>
		</td>
	</tr>

</tbody>
</table>
<?php
// ===========================================================================
// EOF
