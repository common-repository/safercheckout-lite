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
 +===========================================================================+ 2024-06-10
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================
// Display the "Payment Methods" page.

wp_nonce_field('safercheckout-nonce', 'safercheckout-nonce', 0);
?>
<input type="hidden" id="safercheckout-settings" name="safercheckout-settings" value="settings-methods" />

<h2><?php esc_html_e('Payment Methods', 'safercheckout-lite') ?></h2>
<table class="form-table sco-border-grey">
<tbody>

	<?php
	// Payment methods
	if (! isset( $this->sc_options['payment_methods'] ) ) {
		$this->sc_options['payment_methods'] = $this->sc_default['payment_methods'];
	}
	$wc_payment_methods = WC()->payment_gateways->payment_gateways();
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Enable SaferCheckout for the following payment methods',
					'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php
					esc_attr_e('Use this option to select which payment method should be filtered by SaferCheckout. Note that if you added a new payment method to WooCommerce, it would be automatically appended to this list.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<p><?php esc_html_e('Available methods:', 'safercheckout-lite') ?></p>
			<div class="sco-f-sub">
				<table class="form-table">
			<?php
			$row		= 0;
			$count	= 0;
			foreach( $wc_payment_methods as $key => $value ) {
				$checked = ' checked';
				$row++;
				if ( $row % 2 == 0 ) {
					$r_color = 'sco-f-white';
				} else {
					$r_color = 'sco-f-grey';
				}
				if ( isset( $this->sc_options['payment_methods'][ $key ] ) ) {
					$checked = '';
					$count++;
				}
				echo '<tr class="'. esc_attr( $r_color ) .'">'.
							'<td class="sco-country-list">'.
								'<label>'.
									'<input type="checkbox" name="safercheckout_payment_methods['.
										esc_attr( $key ) .']"'. esc_html( $checked ).
										' value="1" /> '. esc_html ( $value->method_title ) .
								'</label>'.
							'</td>'.
						'</tr>';
			}
			?>
				</table>
			</div>
		</td>
		<td>&nbsp;</td>
	</tr>
</tbody>
</table>
<?php

// ===========================================================================
// EOF
