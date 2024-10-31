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
 +===========================================================================+ 2024-06-11
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================
// Display the "Advanced Settings" page.

wp_nonce_field('safercheckout-nonce', 'safercheckout-nonce', 0);

?>
<input type="hidden" id="safercheckout-settings" name="safercheckout-settings" value="settings-advanced" />

<h2><?php esc_html_e('Advanced Settings', 'safercheckout-lite') ?></h2>
<table class="form-table sco-border-grey">
<tbody>
	<?php
	// Hook priority
	if (! isset( $this->sc_options['hook_priority'] ) ) {
		$this->sc_options['hook_priority'] = $this->sc_default['hook_priority'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Hook priority', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('SaferCheckout hooks into WooCommerce checkout process in order to filter it. You can use this option to change that hook priority. Lower numbers correspond with earlier execution.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<label><input type="number" style="width:100px;" name="safercheckout_hook_priority" value="<?php echo (int) $this->sc_options['hook_priority']; ?>" min="-<?php echo (int) PHP_INT_MAX ?>" max="<?php echo (int) PHP_INT_MAX ?>" step="1" /></label>
			<p class="description"><?php esc_html_e('Any integer (positive, negative or null).', 'safercheckout-lite'); ?></p>
		</td>
	</tr>

	<?php
	// Debugging
	if (! isset( $this->sc_options['wc_logger'] ) ||
		! preg_match('/^[0-3]$/D', $this->sc_options['wc_logger'] ) ) {

		$this->sc_options['wc_logger'] = $this->sc_default['wc_logger'];
	}
	// Check if there's any log
	$count = SaferCheckoutLite_helpers::safercheckout_find_logs();
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('WooCommerce Logger', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('WooCommerce features a logging system accessible via WooCommerce > Status > Logs, which records errors among other pertinent information. SaferCheckout can use it to record warnings, errors or even all events that occurred during the checkout process. By default, only errors and warnings are logged.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<select name="safercheckout_wc_logger">
				<option value="3"<?php selected( $this->sc_options['wc_logger'], 3 ) ?>><?php
					esc_html_e('Log all events', 'safercheckout-lite') ?></option>
				<option value="2"<?php selected( $this->sc_options['wc_logger'], 2 ) ?>><?php
					esc_html_e('Log warnings and errors', 'safercheckout-lite') ?></option>
				<option value="1"<?php selected( $this->sc_options['wc_logger'], 1 ) ?>><?php
					esc_html_e('Log errors only', 'safercheckout-lite') ?></option>
				<option value="0"<?php selected( $this->sc_options['wc_logger'], 0 ) ?>><?php
					esc_html_e('Disable logging', 'safercheckout-lite') ?></option>
			</select>
			<p class="description">
				<?php
				/* Translators: number of logs */
				printf( esc_html__('Available logs: %s', 'safercheckout-lite'), (int) $count );
				if ( $count ) {
					echo ' <a href="?page=wc-status&tab=logs&source=safercheckout" target="_blank">(' .
						esc_html__('view', 'safercheckout-lite') .'</a>)';
				}
				?>
			</p>
		</td>
	</tr>
</tbody>
</table>
<?php

// ===========================================================================
// EOF
