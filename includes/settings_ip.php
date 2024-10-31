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
// Display the "IP Address" page.

wp_nonce_field('safercheckout-nonce', 'safercheckout-nonce', 0);
?>
<input type="hidden" id="safercheckout-settings" name="safercheckout-settings" value="settings-ip" />

<h2><?php esc_html_e('IP Address', 'safercheckout-lite') ?></h2>
<table class="form-table sco-border-grey">
<tbody>
	<?php
	// User IP address
	if (! isset( $this->sc_options['source_ip'] ) ) {
		$this->sc_options['source_ip'] = $this->sc_default['source_ip'];
	}
	if (! isset( $this->sc_options['ip_blacklist'] ) ) {
		$this->sc_options['ip_blacklist'] = $this->sc_default['ip_blacklist'];
	}
	if (! isset( $this->sc_options['ip_whitelist'] ) ) {
		$this->sc_options['ip_whitelist'] = $this->sc_default['ip_whitelist'];
	}
	?>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e("Retrieve IP address from", 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('This option should be used if you are behind a reverse proxy, a load balancer or using a CDN (e.g., Clouflare), in order to tell SaferCheckout which IP address it should use. By default, it will rely on REMOTE_ADDR.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
		<?php
		$ip_addresses = SaferCheckoutLite_helpers::safercheckout_get_ip_list();
		foreach( $ip_addresses as $var => $ip ) {
			// Warn if it's a private address
			if ( true === SaferCheckoutLite_helpers::is_private( $ip ) ) {
				echo '<p><label><input type="radio" name="safercheckout_source_ip" value ="'. esc_attr( $var ) .'"'. checked( $this->sc_options['source_ip'], $var, 0 ) .' /> <code>'. esc_html( $var) .'</code>: '. esc_html( $ip ) . ' <span style="color: rgb(214, 54, 56)">⚠️ ️️' . esc_html('This is a private IP address. Make sure to select a public address that shows your real IP.', 'safercheckout-lite') .	'</span></label></p>';

			} else {
				echo '<p><label><input type="radio" name="safercheckout_source_ip" value ="'. esc_attr( $var ) .'"'. checked( $this->sc_options['source_ip'], $var, 0 ) .' /> <code>'. esc_html( $var) .'</code>: '. esc_html( $ip ) .'</label></p>';
			}
		}
		?>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Allow the following IP addresses, CIDR or AS number', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('You can permanently allow an IP address, a whole range of IP addresses or even an AS number (Autonomous System number). If an IP address matched that list, the order would be immediately accepted.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>
		<td>
			<p><?php esc_html_e('Whitelist:', 'safercheckout-lite') ?></p>
			<textarea style="min-width: 50%; height: 100px;" maxlength="300" name="safercheckout_ip_whitelist"><?php
				foreach ( $this->sc_options['ip_whitelist'] as $ip => $null ) {
					echo esc_textarea( "$ip\n" );
				}
			?></textarea>
			<p class="description">
				<?php esc_html_e('One item per line.', 'safercheckout-lite') ?> <span><a href="javascript:" style="text-decoration:none;" onClick="safercheckout_toggle('view-ip-allow');"> <?php esc_html_e('View allowed syntax', 'safercheckout-lite') ?></a></span>
			</p>
			<div id="view-ip-allow" style="display:none">
				<ul class="view">
					<li><?php printf(
						/* Translators: IP address */
						esc_html__('IPv4 address: %s', 'safercheckout-lite') , '<code>66.155.10.20</code>' )
					?></li>
					<li><?php
						/* Translators: IP address */
						printf( esc_html__('IPv4 CIDR: %s', 'safercheckout-lite') , '<code>66.155.0.0/17</code>' )
					?></li>
					<li><?php printf(
						/* Translators: IP address */
						esc_html__('IPv6 address: %s', 'safercheckout-lite') , '<code>2001:db8:85a3::8a2e</code>' )
					?></li>
					<li><?php printf(
						/* Translators: IP address */
						esc_html__('IPv6 CIDR: %s', 'safercheckout-lite') , '<code>2c0f:f248::/32</code>' )
					?></li>
					<li><?php printf(
						/* Translators: IP address */
						esc_html__('Autonomous System number: %s', 'safercheckout-lite') , '<code>AS15169</code>' )
					?></li>
				</ul>
			</div>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label>
				<?php esc_html_e('Block the following IP addresses, CIDR or AS number', 'safercheckout-lite') ?> <span class="woocommerce-help-tip" tabindex="0" aria-label="<?php esc_attr_e('You can permanently block an IP address, a whole range of IP addresses or even an AS number (Autonomous System number). If an IP address matched that list, the order would be immediately rejected.', 'safercheckout-lite') ?>"></span>
			</label>
		</th>

		<td>
			<p><?php esc_html_e('Blacklist:', 'safercheckout-lite') ?></p>
			<textarea style="min-width: 50%; height: 100px;" maxlength="300" name="safercheckout_ip_blacklist"><?php
				foreach ( $this->sc_options['ip_blacklist'] as $ip => $null ) {
					echo esc_textarea( "$ip\n" );
				}
			?></textarea>
			<p class="description">
				<?php esc_html_e('One item per line.', 'safercheckout-lite') ?> <span><a href="javascript:" style="text-decoration:none;" onClick="safercheckout_toggle('view-ip-block');"> <?php esc_html_e('View allowed syntax', 'safercheckout-lite') ?></a></span>
			</p>
			<div id="view-ip-block" style="display:none">
				<ul class="view">
					<li><?php printf(
						/* Translators: IP address */
						esc_html__('IPv4 address: %s', 'safercheckout-lite') , '<code>66.155.10.20</code>' )
					?></li>
					<li><?php printf(
						/* Translators: IP address */
						esc_html__('IPv4 CIDR: %s', 'safercheckout-lite') , '<code>66.155.0.0/17</code>' )
					?></li>
					<li><?php printf(
						/* Translators: IP address */
						esc_html__('IPv6 address: %s', 'safercheckout-lite') , '<code>2001:db8:85a3::8a2e</code>' )
					?></li>
					<li><?php printf(
						/* Translators: IP address */
						esc_html__('IPv6 CIDR: %s', 'safercheckout-lite') , '<code>2c0f:f248::/32</code>' )
					?></li>
					<li><?php printf(
						/* Translators: IP address */
						esc_html__('Autonomous System number: %s', 'safercheckout-lite') , '<code>AS15169</code>' )
					?></li>
				</ul>
			</div>
		</td>
		<td>&nbsp;</td>
	</tr>

</tbody>
</table>
<?php

// ===========================================================================
// EOF
