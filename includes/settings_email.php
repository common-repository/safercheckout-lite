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
 +===========================================================================+ 2024-09-30
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================
// Display the "Email Address" page.

wp_nonce_field('safercheckout-nonce', 'safercheckout-nonce', 0);
?>
<input type="hidden" id="safercheckout-settings" name="safercheckout-settings" value="settings-email" />

<h2><?php esc_html_e('Email Address', 'safercheckout-lite') ?></h2>
<table class="form-table sco-border-grey">
<tbody>

	<?php
	// User email address
	if (! isset( $this->sc_options['email_whitelist'] ) ) {
		$this->sc_options['email_whitelist'] = $this->sc_default['email_whitelist'];
	}
	if (! isset( $this->sc_options['email_blacklist'] ) ) {
		$this->sc_options['email_blacklist'] = $this->sc_default['email_blacklist'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Allow the following email addresses', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('You can permanently allow an email address or any part of it. If an address matched that list, the order would be immediately accepted.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<p><?php esc_html_e('Whitelist:', 'safercheckout-lite') ?></p>
			<textarea style="min-width: 50%; height: 100px;" maxlength="300" name="safercheckout_email_whitelist"><?php
				foreach ( $this->sc_options['email_whitelist'] as $email => $null ) {
					echo esc_textarea( "$email\n" );
				}
			?></textarea>
			<p class="description"><?php esc_html_e('Full or partial case-insensitive email address, one per line.', 'safercheckout-lite') ?>
				<span><a href="javascript:" style="text-decoration:none;" onClick="safercheckout_toggle('view-whitelist-email');"> <?php esc_html_e('View examples', 'safercheckout-lite') ?></a></span>
				<div id="view-whitelist-email" style="display:none">
					<ul class="view">
						<li><code>foo@hotmail.com</code></li>
						<li><code>foo</code></li>
						<li><code>@hotmail.com</code></li>
						<li><code>hotmail</code></li>
					</ul>
				</div>
			</p>
		</td>
		<td>&nbsp;</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Block the following email addresses', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('You can permanently block an email address or any part of it. If an address matched that list, the order would be immediately rejected.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<p><?php esc_html_e('Blacklist:', 'safercheckout-lite') ?></p>
			<textarea style="min-width: 50%; height: 100px;" maxlength="300" name="safercheckout_email_blacklist"><?php
				foreach ( $this->sc_options['email_blacklist'] as $rdns => $null ) {
					echo esc_textarea( "$rdns\n" );
				}
			?></textarea>
			<p class="description"><?php esc_html_e('Full or partial case-insensitive email address, one per line.', 'safercheckout-lite') ?>
				<span><a href="javascript:" style="text-decoration:none;" onClick="safercheckout_toggle('view-blacklist-email');"> <?php esc_html_e('View examples', 'safercheckout-lite') ?></a></span>
				<div id="view-blacklist-email" style="display:none">
					<ul class="view">
						<li><code>foo@hotmail.com</code></li>
						<li><code>foo</code></li>
						<li><code>@hotmail.com</code></li>
						<li><code>hotmail</code></li>
					</ul>
				</div>
			</p>
		</td>
		<td>&nbsp;</td>
	</tr>
</tbody>
</table>
<?php

// ===========================================================================
// EOF
