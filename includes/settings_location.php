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
 +===========================================================================+ 2024-06-13
*/

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// ===========================================================================
// Display the "Location" page.

wp_nonce_field('safercheckout-nonce', 'safercheckout-nonce', 0);
?>
<input type="hidden" id="safercheckout-settings" name="safercheckout-settings" value="settings-location" />

<h2><?php esc_html_e('Location', 'safercheckout-lite') ?></h2>
<table class="form-table sco-border-grey">
<tbody>
	<?php
	// Geolocation
	$iso31666 = SaferCheckoutLite_helpers::safercheckout_get_iso31666();
	if ( empty( $this->sc_options['countries'] ) ) {
		$this->sc_options['countries'] = [];
	}
	if (! isset( $this->sc_options['address_blacklist_ip'] ) ) {
		$this->sc_options['address_blacklist_ip'] = $this->sc_default['address_blacklist_ip'];
	}
	if (! isset( $this->sc_options['address_blacklist_ship'] ) ) {
		$this->sc_options['address_blacklist_ship'] = $this->sc_default['address_blacklist_ship'];
	}
	if (! isset( $this->sc_options['address_blacklist_bill'] ) ) {
		$this->sc_options['address_blacklist_bill'] = $this->sc_default['address_blacklist_bill'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Block the following countries & territories', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('You can select which country or territory you want to block and whether it should apply to the cutomer\'s IP, billing and/or shipping address.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td id="td-countries">
		<?php esc_html_e('Blacklist:', 'safercheckout-lite'); ?>
			<div class="sco-f-sub">
				<table class="form-table">
			<?php
			$row		= 0;
			$count	= 0;
			foreach( $iso31666 as $country ) {
				$checked = '';
				$row++;
				if ( $row % 2 == 0 ) {
					$r_color = 'sco-f-white';
				} else {
					$r_color = 'sco-f-grey';
				}
				if ( isset( $this->sc_options['countries'][ $country['alpha-2'] ] ) ) {
					$checked = ' checked';
					$count++;
				}
				echo '<tr class="'. esc_attr( $r_color ) .'">'.
							'<td class="sco-country-list">'.
								'<label>'.
									'<input type="checkbox" onClick="safercheckout_update_counter(this)" '.
										'name="safercheckout_countries['. esc_attr( $country['alpha-2'] ) .
										']"'. esc_html( $checked ) .' /> '. esc_html ( $country['name'] ) .
								'</label>'.
							'</td>'.
						'</tr>';
			}
			?>
				</table>
			</div>
			<?php
			printf(
				/* Translators: num of items */
				esc_html__('Total blocked items: %s', 'safercheckout-lite'),
				'<font id="total-items">'. (int) $count .'</font>'
			);
			?>
			<p class="description"><a href="javascript:" style="text-decoration:none;" onclick="safercheckout_check(1)"><?php esc_html_e('Check all', 'safercheckout-lite') ?></a> - <a href="javascript:" style="text-decoration:none;" onclick="safercheckout_check(0)"><?php esc_html_e('Uncheck all', 'safercheckout-lite') ?></a></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			&nbsp;
		</th>
		<td>
			<label id="geo-apply"><?php esc_html_e('Apply blacklist to:', 'safercheckout-lite') ?></label>
			<p><label><input type="checkbox" id="safercheckout_address_blacklist_ip" name="safercheckout_address_blacklist_ip" value="1"<?php checked( $this->sc_options['address_blacklist_ip'] , 1)?> /> <?php esc_html_e('Customer\'s IP address.', 'safercheckout-lite') ?></label></p>
			<p><label><input type="checkbox" id="safercheckout_address_blacklist_bill" name="safercheckout_address_blacklist_bill" value="1"<?php checked( $this->sc_options['address_blacklist_bill'] , 1)?> /> <?php esc_html_e('Customer\'s billing address.', 'safercheckout-lite') ?></label></p>
			<p><label><input type="checkbox" id="safercheckout_address_blacklist_ship" name="safercheckout_address_blacklist_ship" value="1"<?php checked( $this->sc_options['address_blacklist_ship'] , 1)?> /> <?php esc_html_e('Customer\'s shipping address.', 'safercheckout-lite') ?></label></p>
		</td>
	</tr>

	<?php
	// Location matching
	if (! isset( $this->sc_options['location_user_billing'] ) ) {
		$this->sc_options['location_user_billing'] = $this->sc_default['location_user_billing'];
	}
	if (! isset( $this->sc_options['location_user_billing_score'] ) ) {
		$this->sc_options['location_user_billing_score'] = $this->sc_default['location_user_billing_score'];
	}
	if (! isset( $this->sc_options['location_shipping_billing'] ) ) {
		$this->sc_options['location_shipping_billing'] = $this->sc_default['location_shipping_billing'];
	}
	if (! isset( $this->sc_options['location_shipping_billing_score'] ) ) {
		$this->sc_options['location_shipping_billing_score'] = $this->sc_default['location_shipping_billing_score'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Location matching', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('You can use this option to ensure that the country of origin of the customer\'s IP address matches the billing country. Otherwise, the corresponding risk score will be applied.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<label>
				<input type="checkbox" name="safercheckout_location_user_billing" value="1"<?php checked( $this->sc_options['location_user_billing'], 1 ) ?> /> <?php esc_html_e('Customer\'s IP address must match the billing country.', 'safercheckout-lite' ) ?>
			</label>
		</td>
		<td>
			<label>
				<?php esc_html_e('Risk score:', 'safercheckout-lite') ?><br /><input type="number" name="safercheckout_location_user_billing_score" value="<?php echo esc_attr( $this->sc_options['location_user_billing_score'] ) ?>" style="width:100px;" min="1" max="100" step="1" />
			</label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label>
				&nbsp; <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('You can use this option to ensure that the billing country matches the shipping country. Otherwise, the corresponding risk score will be applied.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<label>
				<input type="checkbox" name="safercheckout_location_shipping_billing" value="1"<?php checked( $this->sc_options['location_shipping_billing'], 1 ) ?> /> <?php esc_html_e('Customer\'s shipping country must match the billing country.', 'safercheckout-lite' ) ?>
			</label>
		</td>
		<td>
			<label>
				<?php esc_html_e('Risk score:', 'safercheckout-lite') ?><br /><input type="number" name="safercheckout_location_shipping_billing_score" value="<?php echo esc_attr( $this->sc_options['location_shipping_billing_score'] ) ?>" style="width:100px;" min="1" max="100" step="1" />
			</label>
		</td>
	</tr>

	<?php
	// Blacklist
	if (! isset( $this->sc_options['order_blacklist_address'] ) ) {
		$this->sc_options['order_blacklist_address'] = $this->sc_default['order_blacklist_address'];
	}
	if (! isset( $this->sc_options['order_blacklist_ship'] ) ) {
		$this->sc_options['order_blacklist_ship'] = $this->sc_default['order_blacklist_ship'];
	}
	if (! isset( $this->sc_options['order_blacklist_bill'] ) ) {
		$this->sc_options['order_blacklist_bill'] = $this->sc_default['order_blacklist_bill'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Block the following shipping/billing addresses', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('You can permanently block an address or any part of it. If an address matched that list, the order would be immediately rejected. The filtering applies to the following checkout fields: street, apartment, postal/zip code, state, city and phone number. It doesn\'t apply to the country.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<p><?php esc_html_e('Blacklist:', 'safercheckout-lite') ?></p>
			<textarea style="min-width: 50%; height: 100px;" maxlength="300" id ="order_blacklist_address" name="safercheckout_order_blacklist_address"><?php
				foreach ( $this->sc_options['order_blacklist_address'] as $address => $null ) {
					echo esc_textarea( "$address\n" );
				}
			?></textarea>
			<p class="description"><?php esc_html_e('Full or partial case-insensitive string, one per line.', 'safercheckout-lite') ?>
				<span><a href="javascript:" style="text-decoration:none;" onClick="safercheckout_toggle('view-blacklist-address');"> <?php esc_html_e('View examples', 'safercheckout-lite') ?></a></span>
				<div id="view-blacklist-address" style="display:none">
					<ul class="view">
						<li><?php esc_html_e('Street:', 'safercheckout-lite') ?> <code>123 Main Street</code> <?php esc_html_e('or', 'safercheckout-lite') ?> <code>Main Street</code> <?php esc_html_e('or', 'safercheckout-lite') ?> <code>Main</code></li>
						<li><?php esc_html_e('ZIP/post code:', 'safercheckout-lite') ?> <code>10024</code></li>
						<li><?php esc_html_e('City:', 'safercheckout-lite') ?> <code>Los Angeles</code> <?php esc_html_e('or', 'safercheckout-lite') ?> <code>Angel</code></li>
						<li><?php esc_html_e('State:', 'safercheckout-lite') ?> <code>California</code></li>
						<li><?php esc_html_e('Phone:', 'safercheckout-lite') ?> <code>01632960345</code> <?php esc_html_e('or', 'safercheckout-lite') ?> <code>016329</code></li>
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
			<label id="address-apply"><?php esc_html_e('Apply blacklist to:', 'safercheckout-lite') ?></label>
			<p><label><input type="checkbox" id="safercheckout_order_blacklist_bill" name="safercheckout_order_blacklist_bill" value="1"<?php checked( $this->sc_options['order_blacklist_bill'] , 1)?> /> <?php esc_html_e('Customer\'s billing address.', 'safercheckout-lite') ?></label></p>
			<p><label><input type="checkbox" id="safercheckout_order_blacklist_ship" name="safercheckout_order_blacklist_ship" value="1"<?php checked( $this->sc_options['order_blacklist_ship'] , 1)?> /> <?php esc_html_e('Customer\'s shipping address.', 'safercheckout-lite') ?></label></p>
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
