<?php
if (! defined('ABSPATH') ) {
	exit;
}
/**
Plugin Name: SaferCheckout Lite
Plugin URI: https://nintechnet.com/safercheckout/
Description: Advanced fraud prevention for WooCommerce (lite version).
Author: The Ninja Technologies Network
Author URI: https://nintechnet.com/
License: GPLv3 or later
Text Domain: safercheckout-lite
WC requires at least: 7.0.0
WC tested up to: 9.0
Requires Plugins: woocommerce
Version: 1.0
*/
define('SAFERCHECKOUT_LITE_VERSION', '1.0');

/**
 +===========================================================================+
 |    ____         __            ____ _               _               _      |
 |   / ___|  __ _ / _| ___ _ __ / ___| |__   ___  ___| | _____  _   _| |_    |
 |   \___ \ / _` | |_ / _ \ '__| |   | '_ \ / _ \/ __| |/ / _ \| | | | __|   |
 |    ___) | (_| |  _|  __/ |  | |___| | | |  __/ (__|   < (_) | |_| | |_    |
 |   |____/ \__,_|_|  \___|_|   \____|_| |_|\___|\___|_|\_\___/ \__,_|\__|   |
 |                                                                           |
 | (c) NinTechNet Limited ~ https://nintechnet.com/safercheckout/            |
 +===========================================================================+	2024-09-29
*/
require_once __DIR__ .'/includes/class_helpers.php';

/**
 * Metabox requirement.
 */
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

/**
 * HPOS compatibility.
 */
add_action('before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
} );


function safercheckout_lite_activate() {

	// If the pro version is active, we refuse to run and throw an error message
	if ( is_plugin_active('safercheckout/index.php') ) {
		exit(
			esc_html__('SaferCheckout Pro is active on this site, please disable it first if you want to run the free version.',	'safercheckout-lite')
		);
	}
}
register_activation_hook( __FILE__, 'safercheckout_lite_activate');

class NinTechNet_SaferCheckout_Lite {

	private $sc_options	= [];
	private $sc_default	= [];
	public $sc_id			= 'safercheckout-lite';


	/**
	 * Initialize.
	 */
	public function __construct() {

		/**
		 * Get the plugin's options or use the default ones.
		 */
		$this->sc_options = get_option('safercheckout');
		$this->sc_default = SaferCheckoutLite_helpers::safercheckout_default_options();
		if ( empty( $this->sc_options ) ) {
			$this->sc_options = $this->sc_default;
			update_option('safercheckout', $this->sc_options );
		}

		/**
		 * Setup all hooks.
		 */
		$this->hooks();
	}


	/**
	 * Set up all required hooks.
	 */
	private function hooks() {

		/**
		 * Checkout hooks.
		 */
		add_action(
			'woocommerce_checkout_order_processed',
			[ $this, 'process_order'],
			$this->sc_options['hook_priority'],
			3
		);
		add_action(
			'woocommerce_store_api_checkout_order_processed',
			[ $this, 'process_order'],
			$this->sc_options['hook_priority'],
			1
		);


		/**
		 * Everything below is only required for the Shop Manager/Admin, in the admin backend.
		 */
		if (! is_admin() || ! current_user_can('manage_woocommerce') ) {
			return;
		}

		/**
		 * JS and styles, backend.
		 */
		add_action('admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts'] );
		add_filter('plugin_action_links_'. plugin_basename( __FILE__ ), [ $this, 'settings_link'] );

		/**
		 * Tabs, section and page for the settings.
		 */
		add_filter('woocommerce_settings_tabs_array', [ $this, 'settings_tab'], 50 );
		add_action('woocommerce_sections_'. $this->sc_id, [ $this, 'custom_sections'] );
		add_action('woocommerce_settings_'. $this->sc_id, [ $this, 'settings_page'] );

		/**
		 * Risk score column in the orders page.
		 */
		add_action(
			'manage_woocommerce_page_wc-orders_custom_column',
			[ $this, 'risk_score_column_data'], 10, 2
		);
		add_action(
			'manage_shop_order_posts_custom_column',
			[ $this, 'risk_score_column_data'], 10, 2
		);
		add_filter(
			'manage_woocommerce_page_wc-orders_columns', [ $this, 'add_risk_score_column'], 999
		);
		add_filter(
			'manage_edit-shop_order_columns', [ $this, 'add_risk_score_column'], 999
		);

		/**
		 * Metabox.
		 */
		add_action('add_meta_boxes', [ $this, 'add_metabox'] );

	}

	/**
	 * Register the metabox.
	 */
	public function add_metabox() {

		$screen = class_exists(
				'\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController'
			)
		&& wc_get_container()->get(
			CustomOrdersTableController::class
		)->custom_orders_table_usage_is_enabled()	? wc_get_page_screen_id('shop-order') :'shop_order';

		add_meta_box(
			'safercheckout-lite',
			'safercheckout-lite',
			[ $this, 'render_metabox'],
			$screen,
			'side',
			'high'
		);
	}


	/**
	 * Display metabox content, or risk score value in the orders list column ($list = true).
	 */
	public function render_metabox( $post_or_order_object, $list = false ) {

		$order = ( $post_or_order_object instanceof WP_Post ) ?
					wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		$meta = $order->get_meta('_safercheckout', true);

		/**
		 * No records.
		 */
		if ( empty( $meta ) ) {
			// Metabox
			if ( $list !== true ) {
				esc_html_e('SaferCheckout wasn\'t enabled when this order was placed.', 'safercheckout-lite');
			}
			return;
		}

		$score = (int) $meta[0];

		if ( $list === true ) {
			/**
			 * Orders list : return risk score only.
			 */
			return $score;
		}

		if ( empty( $meta[1] ) ) {
			esc_html_e('SaferCheckout wasn\'t enabled when that order was placed.', 'safercheckout-lite');
			return;
		}

		$percent = $score;
		if ( $percent > 100 ) {
			$percent = 100;
		}
		if ( $percent <= $this->sc_options['score_low'] ) {
			$background	= '#c6e1c6';
			$color		= '#5b841b';
		} elseif ( $percent <= $this->sc_options['score_medium'] ) {
			$background	= '#f8dda7';
			$color		= '#94660c';
		} else {
			$background	= '#eba3a3';
			$color		= '#761919';
		}

		/**
		 * Remove risk score element from array.
		 */
		array_shift( $meta );

		if ( $meta[0] == SAFERCHECKOUT_LITE_SIMULATION ) {
			echo '<center style="font-weight:bold">'.
				esc_html( SAFERCHECKOUT_LITE_SIMULATION ) .'</center>';
			array_shift( $meta );
		}
		echo '<ul>'.
			'<div class="sco-list-pc" style="color:'. esc_attr( $color ) .'">'.
			'<div class="sco-list-pc-bar" style="width:'. esc_attr( $percent ) .
			'%;background-color:'. esc_attr( $background ) .'">'.
			'</div><center>'. esc_html__('Risk score:', 'safercheckout-lite') .' '.
			esc_html( $score ) .'</center></div><br />';

		foreach( $meta as $item ) {
			if ( str_starts_with( $item, '⛔') || str_starts_with( $item, '✅') ) {
				echo '<li>'. esc_html( $item ) .'</li>';
			} else {
				echo '<li>⚠️ '. esc_html( $item ) .'</li>';
			}
		}
		echo '</ul>';
	}


	/**
	 * Risk score column definition.
	 */
	public function add_risk_score_column( $columns ) {

		$columns['risk-score'] = esc_html__('Risk score', 'safercheckout-lite');
		return $columns;
	}


	/**
	 * Populate content for our risk score column.
	 */
	public function risk_score_column_data( $column, $order ) {

		if ('risk-score' === $column ) {

			if ( is_object( $order ) ) {
				// HPOS
				$score = $this->render_metabox( $order, true );
			} else {
				// Legacy
				$score = $this->render_metabox( wc_get_order( $order ), true );
			}

			if ( isset( $score ) ) {
				if ( $score <= $this->sc_options['score_low'] ) {
					echo "<span class='riskscore-list' style='color:#5b841b;background-color:#c6e1c6;cursor:help'".
						" title='SaferCheckout: ".	esc_attr__('Low risk order.', 'safercheckout-lite') ."' >";

				} elseif ( $score <= $this->sc_options['score_medium'] ) {
					echo "<span class='riskscore-list' style='color:#94660c;background-color:#f8dda7;cursor:help'".
						" title='SaferCheckout: ".	esc_attr__('Medium risk order.', 'safercheckout-lite') ."' >";

				} else {
					echo "<span class='riskscore-list' style='color:#761919;background-color:#eba3a3;cursor:help'".
						" title='SaferCheckout: ". esc_attr__('High risk order.', 'safercheckout-lite') ."' >";
				}
				echo esc_html( $score ) .'</span>';
				return;
			}

			echo "<span class='riskscore-list' style='color:#2e4453;background-color:#d7d9dd;cursor:help'".
				" title='". esc_attr__('SaferCheckout wasn\'t enabled when this order was placed.',
				'safercheckout-lite'). "' >".	esc_html__('N/A', 'safercheckout-lite') .'</span>';
		}
	}


	/**
	 * Enqueue scripts in the backend.
	 */
	public function admin_enqueue_scripts() {

		$screen = get_current_screen();


		if ( isset( $_GET['section'] ) && $_GET['section'] == 'safercheckout-pro') {
			// Load thickbox JS and CSS for the Pro version promo tab
			$extra_js	= ['jquery', 'thickbox'];
			$extra_css	= ['thickbox'];
		} else {
			$extra_js	= ['jquery'];
			$extra_css	= [];
		}


		/**
		 * Load style on our settings tab or WC orders list page.
		 */
		if ( $screen->post_type == 'shop_order' ||
			( isset( $_GET['page'] ) && $_GET['page'] == 'wc-orders' ) ||
			( isset( $_GET['tab']  ) && $_GET['tab']  == 'safercheckout-lite' )
		) {
			wp_enqueue_style(
				'safercheckout_style',
				plugin_dir_url( __FILE__ ) .'static/safercheckout.css',
				$extra_css,
				SAFERCHECKOUT_LITE_VERSION

			);
		}

		/**
		 * Load script only for our settings page.
		 */
		if ( empty( $_GET['tab'] ) || $_GET['tab'] != 'safercheckout-lite' ) {
			return;
		}

		/**
		 * Main JS script.
		 */
		wp_enqueue_script(
			'safercheckout_script',
			plugin_dir_url( __FILE__ ) .'static/safercheckout.js',
		//	['jquery'],
			$extra_js,
			SAFERCHECKOUT_LITE_VERSION,
			true
		);

		/**
		 * JS i18n.
		 */
		$safercheckouti18n = [
			'low_max_98' =>
			esc_attr__('Low risk score cannot be higher than 98.',
				'safercheckout-lite'),
			'low_max_medium' =>
			esc_attr__('Low risk score cannot be equal or higher than medium risk score.',
				'safercheckout-lite'),
			'medium_max_99' =>
			esc_attr__('Medium risk score cannot be higher than 99.',
				'safercheckout-lite'),
			'medium_max_low' =>
			esc_attr__('Medium risk score cannot be equal or lower than low risk score.',
				'safercheckout-lite'),
			'dnsbl_success' =>
			esc_attr__('The test was successful !',
				'safercheckout-lite'),
			'dnsbl_error' =>
			esc_attr__('Error:',
				'safercheckout-lite'),
			'geo_error'	=>
			esc_attr__('Please select where geolocation should apply to: IP, billing and/or shipping address.','safercheckout-lite'),
			'address_error'	=>
			esc_attr__('Please select where the shipping/billing address blacklist should apply to: billing and/or shipping address.','safercheckout-lite'),
			'name_error'	=>
			esc_attr__('Please select where the name blacklist should apply to: billing and/or shipping name.','safercheckout-lite')
		];
		wp_localize_script(
			'safercheckout_script',
			'safercheckouti18n',
			$safercheckouti18n
		);
	}


	/**
	 * Settings link.
	 */
	public function settings_link( $links ) {

		$links[] = '<a href="'.
						get_admin_url( null, 'admin.php?page=wc-settings&tab=safercheckout-lite') .
						'">'.	esc_html__('Settings', 'safercheckout-lite'). '</a>';
		$links[] = '<a style="font-weight:700;color:#006393;" href="https://nintechnet.com/safercheckout/" '.
						'target="_blank" rel="noopener noreferrer">'.
						esc_html__('Go Pro', 'safercheckout-lite'). '</a>';
		return $links;
	}


	/**
	 * Add our main tab to the WooCommerce settings page.
	 */
	public function settings_tab( $tabs ) {

		$tabs['safercheckout-lite'] = __('SaferCheckout Lite', 'safercheckout-lite');
		return $tabs;
	}


	/**
	 * Add our custom sections to our main tab in the settings page.
	 */
	public function custom_sections() {

		global $current_section;

		SaferCheckoutLite_helpers::safercheckout_is_simulation( $this->sc_options );

		$sections = [
			''								=> __('General Settings', 'safercheckout-lite'),
			'settings-riskscore'		=> __('Risk Score', 'safercheckout-lite'),
			'settings-methods'		=> __('Payment Methods', 'safercheckout-lite'),
			'settings-ip'				=> __('IP Address', 'safercheckout-lite'),
			'settings-email'			=> __('Email Address', 'safercheckout-lite'),
			'settings-location'		=> __('Location', 'safercheckout-lite'),
			'settings-customer'		=> __('Customer', 'safercheckout-lite'),
			'settings-order'			=> __('Order', 'safercheckout-lite'),
			'settings-advanced'		=> __('Advanced Settings', 'safercheckout-lite'),
			'safercheckout-pro'		=> __('Pro Features', 'safercheckout-lite'),
			'settings-about'			=> __('About', 'safercheckout-lite')
		];

		$array_keys = array_keys( $sections );

		echo '<ul class="subsubsub">';
		foreach ( $sections as $id => $label ) {
			echo '<li>
				<a href="'. esc_attr( admin_url( 'admin.php?page=wc-settings&tab='. $this->sc_id  .
				'&section='. $id ) ) .'" class="'. ( $current_section == $id ? 'current' : '' ) .
				'">'. esc_html( $label ) .'</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) .
			'</li>';
		}
		echo '</ul><br class="clear" />';
	}


	/**
	 * Settings page.
	 */
	public function settings_page() {

		global $current_section;

		/**
		 * General settings.
		 */
		if ( empty( $current_section ) ) {
			require __DIR__ .'/includes/settings_general.php';

		/**
		 * Risk score settings.
		 */
		} elseif ( $current_section == 'settings-riskscore' ) {
			require __DIR__ .'/includes/settings_riskscore.php';

		/**
		 * Payment methods.
		 */
		} elseif ( $current_section == 'settings-methods' ) {
			require __DIR__ .'/includes/settings_methods.php';

		/**
		 * IP address.
		 */
		} elseif ( $current_section == 'settings-ip' ) {
			require __DIR__ .'/includes/settings_ip.php';

		/**
		 * Email address.
		 */
		} elseif ( $current_section == 'settings-email' ) {
			require __DIR__ .'/includes/settings_email.php';

		/**
		 * Geolocation.
		 */
		} elseif ( $current_section == 'settings-location' ) {
			require __DIR__ .'/includes/settings_location.php';

		/**
		 * Customer.
		 */
		} elseif ( $current_section == 'settings-customer' ) {
			require __DIR__ .'/includes/settings_customer.php';

		/**
		 * Order.
		 */
		} elseif ( $current_section == 'settings-order' ) {
			require __DIR__ .'/includes/settings_order.php';

		/**
		 * Advanced settings.
		 */
		} elseif ( $current_section == 'settings-advanced' ) {
			require __DIR__ .'/includes/settings_advanced.php';

		/**
		 * Pro features.
		 */
		} elseif ( $current_section == 'safercheckout-pro' ) {
			require __DIR__ .'/includes/pro_features.php';

		/**
		 * About.
		 */
		} elseif ( $current_section == 'settings-about' ) {
			require __DIR__ .'/includes/settings_about.php';

		}
	}


	/**
	 * Checkout verifications.
	 */
	public function process_order( $order_id ) {

		require_once __DIR__ .'/includes/class_checkout.php';
		new NinTechNet_SaferCheckout_Process( $this->sc_options, $order_id );
	}

}


/**
 * Load on `init` hook.
 */
function safercheckout_lite_init() {

	/**
	 * Saved settings.
	 */
	if (! empty( $_POST['safercheckout-settings'] ) && safercheckout_check_nonce_and_cap() ) {

		require_once __DIR__ .'/includes/settings_save.php';
	}

	new NinTechNet_SaferCheckout_Lite();
}

add_action('init', 'safercheckout_lite_init');

/**
 * Check whether the user has the right permission and security nonce.
 */
function safercheckout_check_nonce_and_cap( $block = true ) {

	if ( current_user_can('manage_woocommerce') && isset( $_POST['safercheckout-nonce'] ) &&
		wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['safercheckout-nonce'] ) ),
			'safercheckout-nonce'
		) ) {

		return true;
	}

	if ( $block ) {
		wp_die('You are not allowed to performed this task.', 'safercheckout-lite');

	} else {

		return false;
	}
}

/**
 * Check for the blog requirements, otherwise display an admin notice.
 */
function safercheckout_lite_check_wc() {
	if (! function_exists('WC') ) {
		add_action('admin_notices', 'safercheckout_lite_wc_required');
	}
	if ( version_compare( PHP_VERSION, '8.1', '<') ) {
		add_action('admin_notices', 'safercheckout_lite_php_required');
	}
}

add_action('plugins_loaded', 'safercheckout_lite_check_wc', 20 );

function safercheckout_lite_php_required() {
	?>
	<div class="error"><p><?php
		printf(
			/* Translators: PHP version */
			esc_html__('SaferCheckout requires PHP 8.1 or greater but your current version is %s.',
			'safercheckout-lite'),
			esc_html( PHP_VERSION )
		)
	?></p></div>
	<?php
	/**
	 * Deactive the plugin to prevent a crash
	 */
	deactivate_plugins('safercheckout/index.php', 1);
}

function safercheckout_lite_wc_required() {
	?>
	<div class="error"><p><?php
		esc_html_e('Please install and activate WooCommerce in order to use SaferCheckout.',
			'safercheckout-lite');
	?></p></div>
	<?php
}


/**
 * Update options in the database after an update, if applicable.
 */
function safercheckout_lite_after_update() {

	$sc_options = get_option('safercheckout');

	if ( empty( $sc_options['version_lite'] ) ||
		version_compare( $sc_options['version_lite'], SAFERCHECKOUT_LITE_VERSION, '<') ) {

		$sc_options['version_lite'] = SAFERCHECKOUT_LITE_VERSION;

		/**
		 * Update version and options.
		 */
		update_option('safercheckout', $sc_options );
	}

}
add_action('admin_init', 'safercheckout_lite_after_update');

// ===========================================================================
// EOF
