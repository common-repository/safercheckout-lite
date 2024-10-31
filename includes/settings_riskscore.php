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
// Display the "Risk Score" page.

wp_nonce_field('safercheckout-nonce', 'safercheckout-nonce', 0);
?>
<input type="hidden" id="safercheckout-settings" name="safercheckout-settings" value="settings-riskscore" />

<h2><?php esc_html_e('Risk Score', 'safercheckout-lite') ?></h2>
<table class="form-table sco-border-grey">
<tbody>

	<?php
	// Risk scores and actions
	if ( empty( $this->sc_options['score_low'] ) ||
		! preg_match('/^(0|[1-9]|[1-8][0-9]|9[0-8])$/D', $this->sc_options['score_low'] ) ) {

		$this->sc_options['score_low'] = $this->sc_default['score_low'];
	}
	if ( empty( $this->sc_options['score_medium'] ) ||
		! preg_match( '/^([1-9]|[1-8][0-9]|9[0-9])$/D', $this->sc_default['score_medium'] ) ) {

		$this->sc_options['score_medium'] = $this->sc_default['score_medium'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Action & risk score', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('These options let you define the risk score range and the action to perform. Low and medium risk actions will mark the order accordingly and will let WooCommerce handle it. The high risk action will block the checkout process, i.e., customers won\'t be able to place their order and to access your payment processor.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<label class="sco-font-600"><?php esc_html_e('Low risk score range:', 'safercheckout-lite') ?></label>
			<p><label>
				<?php printf(
					/* Translators: risk score */
					esc_html__('From 0 to %s', 'safercheckout-lite'),
					'&nbsp;<input type="number" name="safercheckout_score_low" id="safercheckout_score_low" value="'. esc_attr( $this->sc_options['score_low'] ) .'" style="width:100px;" min="0" max="98" step="1" />'
				); ?>
			</label></p>
			<br />
			<p><label class="sco-font-600"><?php esc_html_e('Low risk action:', 'safercheckout-lite') ?></label></p>
			<p><?php esc_html_e('Mark the order as low risk and let WooCommerce handle it.', 'safercheckout-lite') ?></p>
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td class="sco-border-top-grey">
			<label class="sco-font-600"><?php esc_html_e('Medium risk score range:', 'safercheckout-lite') ?></label>
			<p><label>
				<?php
					$score = (int) $this->sc_options['score_low'] + 1;
					printf(
					/* Translators: min risk score, max risk core */
					esc_html__('From %1$s to %2$s', 'safercheckout-lite'),
					'<font id="sc_score_medium">'. esc_html( $score ) .'</font>',
					'&nbsp;<input type="number" name="safercheckout_score_medium" id="safercheckout_score_medium" value="'. esc_attr( $this->sc_options['score_medium'] ) .'" style="width:100px;" min="1" max="99" step="1" />'
				); ?>
			</label></p>
			<br />
			<p><label class="sco-font-600"><?php esc_html_e('Medium risk action:', 'safercheckout-lite') ?></label></p>
			<p><?php esc_html_e('Mark the order as medium risk and let WooCommerce handle it.', 'safercheckout-lite') ?></p>
		</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td class="sco-border-top-grey">
			<label class="sco-font-600"><?php esc_html_e('High risk score range:', 'safercheckout-lite') ?></label>
			<p><label>
				<?php
					$score = (int) $this->sc_options['score_medium'] + 1;
					printf(
					/* Translators: risk score */
					esc_html__('From %s to 100+', 'safercheckout-lite'),
					'<font id="sc_score_high">'. esc_html( $score ) .'</font>'
				);
			?>
			</label></p>
			<br />
			<p><label class="sco-font-600"><?php esc_html_e('High risk action:', 'safercheckout-lite') ?></label></p>
			<p><select name="score_high_action"><?php

				echo '<option value="cancelled" disabled>'.
					sprintf(
						esc_html('Block the order and set its status to "%s"', 'safercheckout-lite'),
						esc_html( _x('Cancelled', 'Order status', 'safercheckout-lite' ) )
					) .' - Pro version</option>';

				echo '<option value="failed" selected>'.
					sprintf(
						esc_html('Block the order and set its status to "%s"', 'safercheckout-lite'),
						esc_html( _x('Failed', 'Order status', 'safercheckout-lite' ) )
					) .'</option>';

				echo '<option value="checkout-draft" disabled>'.
					sprintf(
						esc_html('Block the order and set its status to "%s"', 'safercheckout-lite'),
						esc_html( _x('Draft', 'Order status', 'safercheckout-lite' ) )
					) .' - Pro version</option>';

				echo '<option value="trash" disabled>'.
					esc_html('Block the order and move it to Trash', 'safercheckout-lite') .' - Pro version</option>';

				echo '<option value="delete-permanently" disabled>'.
					esc_html('Block the order and delete it permanently (cannot be recovered)',
					'safercheckout-lite') .' - Pro version</option>';

			?></select></p>
		</td>
	</tr>
</tbody>
</table>
<?php

// ===========================================================================
// EOF
