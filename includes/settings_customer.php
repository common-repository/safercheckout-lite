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
// Display the "Customer" page.

wp_nonce_field('safercheckout-nonce', 'safercheckout-nonce', 0);
?>
<input type="hidden" id="safercheckout-settings" name="safercheckout-settings" value="settings-customer" />

<h2><?php esc_html_e('Customer', 'safercheckout-lite') ?></h2>
<table class="form-table sco-border-grey">
<tbody>
	<?php
	if (! isset( $this->sc_options['customer_whitelist'] ) ) {
		$this->sc_options['customer_whitelist'] = $this->sc_default['customer_whitelist'];
	}
	if (! isset( $this->sc_options['customer_whitelist_order'] ) ) {
		$this->sc_options['customer_whitelist_order'] = $this->sc_default['customer_whitelist_order'];
	}
	$tot = explode(':', $this->sc_options['customer_whitelist_order'] );
	if ( isset( $tot[0] ) &&  isset( $tot[1] ) ) {
		$tot_orders	= (int) $tot[0];
		$tot_days	= (int) $tot[1];
	} else {
		$tot_orders	= 0;
		$tot_days	= 0;
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Repeat or recurring customers', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('This option allows you to immediately accept repeat or recurring customers based on their previous completed orders ("wc-completed"). It can apply to authenticated and unauthenticated customers. Note that if a customer is not authenticated, SaferCheckout will search the database by their email address.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td style="line-height: 2.5;">
			<label>
			<?php
				printf(
					/* Translators: 1=number of orders, 2=number of days */
					esc_html__('Accept the order if the customer has already placed %1$s or more successfull orders, more than %2$s days ago.', 'safercheckout-lite'),
					'<input type="number" name="safercheckout_customer_whitelist_order_1" value="'.
						esc_attr( $tot_orders ) .'" style="width:70px;" min="1" max="100" step="1" />',
					'<input type="number" name="safercheckout_customer_whitelist_order_2" value="'.
						esc_attr( $tot_days ) .'" style="width:70px;" min="1" max="365" step="1" />'
				);
			?>
			</label>
			<br />
			<p><label>
				<input type="radio" name="safercheckout_customer_whitelist" value="auth"<?php checked( $this->sc_options['customer_whitelist'], 'auth' ) ?> /> <?php esc_html_e('Apply to authenticated customers only.', 'safercheckout-lite' ) ?>
			</label></p>
			<p><label>
				<input type="radio" name="safercheckout_customer_whitelist" value="all"<?php checked( $this->sc_options['customer_whitelist'], 'all' ) ?> /> <?php esc_html_e('Apply to authenticated and unauthenticated customers.', 'safercheckout-lite' ) ?>
			</label></p>
			<p><label>
				<input type="radio" name="safercheckout_customer_whitelist" value="0"<?php checked( $this->sc_options['customer_whitelist'], 0 ) ?> /> <?php esc_html_e('Disable this option.', 'safercheckout-lite' ) ?>
			</label></p>
		</td>
		<td>&nbsp;</td>
	</tr>

	<?php
	// Blacklist
	if (! isset( $this->sc_options['customer_blacklist_name'] ) ) {
		$this->sc_options['customer_blacklist_name'] = $this->sc_default['customer_blacklist_name'];
	}
	if (! isset( $this->sc_options['customer_blacklist_name_billing'] ) ) {
		$this->sc_options['customer_blacklist_name_billing'] = $this->sc_default['customer_blacklist_name_billing'];
	}
	if (! isset( $this->sc_options['customer_blacklist_name_shipping'] ) ) {
		$this->sc_options['customer_blacklist_name_shipping'] = $this->sc_default['customer_blacklist_name_shipping'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Block the following name (first/last, company)', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('You can permanently block a name (first and last name of a customer, or a company name) or any part of it. If a string matched that list, the order would be immediately rejected.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<p><?php esc_html_e('Blacklist:', 'safercheckout-lite') ?></p>
			<textarea id="customer_blacklist_name" style="min-width: 50%; height: 100px;" maxlength="300" name="safercheckout_customer_blacklist_name"><?php
				foreach ( $this->sc_options['customer_blacklist_name'] as $name => $null ) {
					echo esc_textarea( "$name\n" );
				}
			?></textarea>
			<p class="description"><?php esc_html_e('Full or partial case-insensitive string, one per line.', 'safercheckout-lite') ?>
				<span><a href="javascript:" style="text-decoration:none;" onClick="safercheckout_toggle('view-blacklist-name');"> <?php esc_html_e('View examples', 'safercheckout-lite') ?></a></span>
				<div id="view-blacklist-name" style="display:none">
					<ul class="view">
						<li><code>John Doe</code></li>
						<li><code>Doe</code></li>
						<li><code>Acme Limited</code></li>
					</ul>
				</div>
			</p>
		</td>
		<td>
			&nbsp;
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			&nbsp;
		</th>
		<td>
			<label id="name-apply"><?php esc_html_e('Apply blacklist to:', 'safercheckout-lite') ?></label>
			<p><label><input type="checkbox" id="safercheckout_customer_blacklist_name_billing" name="safercheckout_customer_blacklist_name_billing" value="1"<?php checked( $this->sc_options['customer_blacklist_name_billing'] , 1)?> /> <?php esc_html_e('Customer\'s billing name.', 'safercheckout-lite') ?></label></p>
			<p><label><input type="checkbox" id="safercheckout_customer_blacklist_name_shipping" name="safercheckout_customer_blacklist_name_shipping" value="1"<?php checked( $this->sc_options['customer_blacklist_name_shipping'] , 1)?> /> <?php esc_html_e('Customer\'s shipping name.', 'safercheckout-lite') ?></label></p>
		</td>
		<td>
			&nbsp;
		</td>
	</tr>

</tbody>
</table>
<?php

// ===========================================================================
// EOF
