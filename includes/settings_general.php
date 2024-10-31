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
 +===========================================================================+ 2024-06-05
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================
// Display the "General Settings" page.

wp_nonce_field('safercheckout-nonce', 'safercheckout-nonce', 0);
?>
<input type="hidden" id="safercheckout-settings" name="safercheckout-settings" value="settings-general" />

<h2><?php esc_html_e('General Settings', 'safercheckout-lite') ?></h2>
<table class="form-table sco-border-grey">
<tbody>
	<?php
	// Default message
	if ( empty( $this->sc_options['default_message'] ) ) {
		$this->sc_options['default_message'] = $this->sc_default['default_message'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Default message', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('This is the default message to display to blocked customers on the checkout page.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<textarea style="min-width: 50%; height: 100px;" maxlength="300" name="safercheckout_default_message"><?php echo esc_textarea( $this->sc_options['default_message'] ) ?></textarea>
			<p class="description"><?php esc_html_e('Maximum 300 characters.', 'safercheckout-lite') ?></p>
		</td>
	</tr>

	<?php
	// Simulation mode
	if (! isset( $this->sc_options['simulation_mode'] ) ||
		! preg_match('/^[01]$/D', $this->sc_options['simulation_mode'] ) ) {

		$this->sc_options['simulation_mode'] = $this->sc_default['simulation_mode'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Simulation mode', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('In simulation mode SaferChekout will filter your customers order but will not block it regardless of its risk score. We recommend to enable it for a while when you first installed the plugin in order to let you tweak its configuration without affecting your customers.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<label><input type="checkbox" name="safercheckout_simulation_mode" value="1"<?php checked( $this->sc_options['simulation_mode'], 1 ); ?> /> <?php esc_html_e('Enable simulation mode.', 'safercheckout-lite') ?></label>
		</td>
	</tr>
</tbody>
</table>
<?php

// ===========================================================================
// EOF
