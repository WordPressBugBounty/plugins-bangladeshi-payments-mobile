<?php
/**
 * Plugin Name: Bangladeshi Payments Mobile – MFS Gateway, QR Code, Advance COD Protection, Thermal POS Invoicing & WhatsApp Suite
 * Plugin URI: https://devnahian.com/bangladeshi-payments-mobile/
 * Description: The ultimate enterprise WooCommerce MFS payment gateway for bKash, Nagad, Rocket, and Upay featuring offline QR codes, Advance COD fraud protection, thermal POS invoicing, and WhatsApp sales tools.
 * Version: 1.6
 * Author: Abdullah Nahian
 * Author URI: https://devnahian.com
 * Text Domain: bangladeshi-payments-mobile
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.3
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define core constants.
define( 'BPM_VERSION', '1.6' );
define( 'BPM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BPM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare High-Performance Order Storage (HPOS) Compatibility.
 */
add_action( 'before_woocommerce_init', 'bpm_declare_hpos_compatibility' );
function bpm_declare_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}

/**
 * Add Settings shortcut link on Plugins list page.
 *
 * @param array $links Array of action links.
 * @return array
 */
add_filter( 'plugin_action_links_' . BPM_PLUGIN_BASENAME, 'bpm_settings_link' );
function bpm_settings_link( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'admin.php?page=bangladeshi-payments-mobile' ) ),
		esc_html__( 'Settings', 'bangladeshi-payments-mobile' )
	);
	array_unshift( $links, $settings_link );
	return $links;
}

/**
 * ========================================================================
 * ENQUEUE FRONTEND & ADMIN CSS / JS ASSETS
 * ========================================================================
 */

// 1. Frontend CSS
add_action( 'wp_enqueue_scripts', 'bpm_enqueue_frontend_assets' );
function bpm_enqueue_frontend_assets() {
	$css_file = BPM_PLUGIN_DIR . 'assets/css/bpm-frontend.css';
	$css_url  = BPM_PLUGIN_URL . 'assets/css/bpm-frontend.css';

	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'bpm-frontend-css',
			$css_url,
			array(),
			filemtime( $css_file )
		);
	}
}

// 2. Admin CSS
add_action( 'admin_enqueue_scripts', 'bpm_enqueue_admin_assets' );
function bpm_enqueue_admin_assets() {
	$screen = get_current_screen();

	$allowed_screens = array(
		'woocommerce_page_wc-settings',
		'woocommerce_page_wc-orders',
		'edit-shop_order',
		'shop_order',
	);

	if ( ! $screen || ( false === strpos($screen->id, 'bangladeshi-payments-mobile' ) && ! in_array( $screen->id,$allowed_screens, true ) ) ) {
		return;
	}

	$file_path = BPM_PLUGIN_DIR . 'assets/css/admin-style.css';$file_url  = BPM_PLUGIN_URL . 'assets/css/admin-style.css';

	if ( file_exists( $file_path ) ) {
		wp_enqueue_style( 'bpm-admin-css', $file_url, array( 'dashicons' ), filemtime($file_path ) );
	}
}

/**
 * Initialize Plugin & Dependencies.
 */
add_action( 'plugins_loaded', 'bpm_init_plugin', 15 );
function bpm_init_plugin() {
	// Load plugin translations.
	load_plugin_textdomain( 'bangladeshi-payments-mobile', false, dirname( BPM_PLUGIN_BASENAME ) . '/languages' );

	// Check if WooCommerce core classes are present.
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'admin_notices', 'bpm_woocommerce_missing_notice' );
		return;
	}

	// 1. Load Standalone Offline SVG QR Code Generator.
	if ( file_exists( BPM_PLUGIN_DIR . 'includes/bpm-qr-generator.php' ) ) {
		require_once BPM_PLUGIN_DIR . 'includes/bpm-qr-generator.php';
	}

	// 2. Load Unified Gateways (bKash, Nagad, Rocket, Upay).
	if ( file_exists( BPM_PLUGIN_DIR . 'includes/class-bpm-gateways.php' ) ) {
		require_once BPM_PLUGIN_DIR . 'includes/class-bpm-gateways.php';
	}

	// 3. Load Zilla & Upazilla Module (if present).
	if ( file_exists( BPM_PLUGIN_DIR . 'includes/modules/class-bpm-zilla-upazilla.php' ) ) {
		require_once BPM_PLUGIN_DIR . 'includes/modules/class-bpm-zilla-upazilla.php';
	}

	// 4. Load Security & Advanced Modules.
	if ( file_exists( BPM_PLUGIN_DIR . 'includes/modules/class-bpm-partial-payment.php' ) ) {
		require_once BPM_PLUGIN_DIR . 'includes/modules/class-bpm-partial-payment.php';
	}
	if ( file_exists( BPM_PLUGIN_DIR . 'includes/modules/class-bpm-ip-blocker.php' ) ) {
		require_once BPM_PLUGIN_DIR . 'includes/modules/class-bpm-ip-blocker.php';
	}
	if ( file_exists( BPM_PLUGIN_DIR . 'includes/modules/class-bpm-risk-score.php' ) ) {
		require_once BPM_PLUGIN_DIR . 'includes/modules/class-bpm-risk-score.php';
	}

	// 5. Load Thermal Invoice Module for Admin.
	if ( is_admin() && file_exists( BPM_PLUGIN_DIR . 'admin/bpm-thermal-invoice.php' ) ) {
		require_once BPM_PLUGIN_DIR . 'admin/bpm-thermal-invoice.php';
	}

	// 6. Load Admin Tab-Based Menu Handlers.
	if ( file_exists( BPM_PLUGIN_DIR . 'admin/bpm-menu.php' ) ) {
		require_once BPM_PLUGIN_DIR . 'admin/bpm-menu.php';
	}

	// 7. Hook Gateways into WooCommerce.
	add_filter( 'woocommerce_payment_gateways', 'bpm_add_gateways' );

	// 8. Hook Dynamic Add to Cart & Place Order Button Text.
	bpm_init_button_text_filters();
}

/**
 * Register payment gateways into WooCommerce.
 *
 * @param array $gateways Registered gateway classes.
 * @return array
 */
function bpm_add_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_bKash';
	$gateways[] = 'WC_Gateway_Nagad';
	$gateways[] = 'WC_Gateway_Rocket';
	$gateways[] = 'WC_Gateway_Upay';
	return $gateways;
}

/**
 * Dynamic Button Text Customizer (Add to Cart & Place Order).
 */
function bpm_init_button_text_filters() {
	$cart_btn_text = get_option( 'bpm_custom_add_to_cart_text', '' );
	if ( ! empty( $cart_btn_text ) ) {
		add_filter( 'woocommerce_product_single_add_to_cart_text', 'bpm_get_custom_add_to_cart_text' );
		add_filter( 'woocommerce_product_add_to_cart_text', 'bpm_get_custom_add_to_cart_text' );
	}

	$place_order_text = get_option( 'bpm_custom_place_order_text', '' );
	if ( ! empty( $place_order_text ) ) {
		add_filter( 'woocommerce_order_button_text', 'bpm_get_custom_place_order_text' );
	}
}

function bpm_get_custom_add_to_cart_text( $default_text ) {
	$custom = get_option( 'bpm_custom_add_to_cart_text', '' );
	return ! empty( $custom ) ? esc_html( $custom ) : $default_text;
}

function bpm_get_custom_place_order_text( $default_text ) {
	$custom = get_option( 'bpm_custom_place_order_text', '' );
	return ! empty( $custom ) ? esc_html( $custom ) : $default_text;
}

/**
 * Individual Checkout Fields Toggle Filter.
 */
add_filter( 'woocommerce_checkout_fields', 'bpm_apply_individual_field_toggles', 999 );
function bpm_apply_individual_field_toggles( $fields ) {
	// 1. Company Name
	if ( 'no' === get_option( 'bpm_field_company', 'no' ) ) {
		unset( $fields['billing']['billing_company'] );
	}

	// 2. Postcode / ZIP
	if ( 'no' === get_option( 'bpm_field_postcode', 'no' ) ) {
		unset( $fields['billing']['billing_postcode'] );
	}

	// 3. Address Line 2
	if ( 'no' === get_option( 'bpm_field_address_2', 'no' ) ) {
		unset( $fields['billing']['billing_address_2'] );
	}

	// 4. Separate Last Name (Merge First & Last Name into Single Full Name field)
	if ( 'no' === get_option( 'bpm_field_last_name', 'no' ) ) {
		unset( $fields['billing']['billing_last_name'] );
		if ( isset( $fields['billing']['billing_first_name'] ) ) {
			$fields['billing']['billing_first_name']['label'] = __( 'Full Name (পুরো নাম)', 'bangladeshi-payments-mobile' );
			$fields['billing']['billing_first_name']['class'] = array( 'form-row-wide' );
		}
	}

	// 5. Email Address
	if ( 'no' === get_option( 'bpm_field_email', 'yes' ) ) {
		unset( $fields['billing']['billing_email'] );
	}

	// 6. Order Notes
	if ( 'no' === get_option( 'bpm_field_order_notes', 'yes' ) ) {
		add_filter( 'woocommerce_enable_order_comments', '__return_false' );
	}

	return $fields;
}

/**
 * Skip Email Validation on Checkout if Email Field is Disabled.
 */
add_action( 'woocommerce_checkout_process', 'bpm_skip_email_validation_if_hidden' );
function bpm_skip_email_validation_if_hidden() {
	if ( 'no' === get_option( 'bpm_field_email', 'yes' ) ) {
		if ( empty( $_POST['billing_email'] ) ) {
			$_POST['billing_email'] = 'no-email-' . time() . '@checkout.local';
		}
	}
}

/**
 * Display Custom Delivery Notice Above Payment Gateways on Checkout.
 */
add_action( 'woocommerce_before_payment_gateways', 'bpm_render_delivery_notice_banner' );
function bpm_render_delivery_notice_banner() {
	if ( 'yes' !== get_option( 'bpm_enable_delivery_notice', 'no' ) ) {
		return;
	}

	$notice = get_option( 'bpm_delivery_notice_text', '' );
	if ( ! empty( $notice ) ) {
		echo '<div class="bpm-delivery-notice-banner">';
		echo '📢 ' . esc_html( $notice );
		echo '</div>';
	}
}

/**
 * Automatically Apply Wallet Transfer Fee to WooCommerce Cart.
 *
 * @param WC_Cart $cart Cart instance.
 */
add_action( 'woocommerce_cart_calculate_fees', 'bpm_apply_gateway_fee', 20, 1 );
function bpm_apply_gateway_fee( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	$chosen_gateway = WC()->session ? WC()->session->get( 'chosen_payment_method' ) : '';
	$supported      = array(
		'bkash'  => array( 'title' => 'bKash Fee',  'rate' => 1.85 ),
		'nagad'  => array( 'title' => 'Nagad Fee',  'rate' => 1.40 ),
		'rocket' => array( 'title' => 'Rocket Fee', 'rate' => 1.80 ),
		'upay'   => array( 'title' => 'Upay Fee',   'rate' => 1.40 ),
	);

	if ( ! isset( $supported[ $chosen_gateway ] ) ) {
		return;
	}

	$gateways = WC()->payment_gateways ? WC()->payment_gateways->payment_gateways() : array();
	if ( ! isset( $gateways[ $chosen_gateway ] ) ) {
		return;
	}

	$gateway      = $gateways[ $chosen_gateway ];
	$apply_charge = method_exists( $gateway, 'get_option' ) && 'yes' === $gateway->get_option( 'apply_charge', 'no' );

	if ( ! $apply_charge ) {
		return;
	}

	$custom_rate   = (float) $gateway->get_option( 'wallet_charge', $supported[ $chosen_gateway ]['rate'] );
	$cart_subtotal = (float) $cart->get_subtotal();
	$fee_amount    = ( $cart_subtotal * $custom_rate ) / 100;

	if ( $fee_amount > 0 ) {
		$cart->add_fee( esc_html( $supported[ $chosen_gateway ]['title'] ), $fee_amount, false );
	}
}

/**
 * Context-aware missing WooCommerce notice.
 */
function bpm_woocommerce_missing_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$is_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );

	if ( $is_installed ) {
		$action_url  = wp_nonce_url(
			'plugins.php?action=activate&plugin=woocommerce%2Fwoocommerce.php',
			'activate-plugin_woocommerce/woocommerce.php'
		);
		$button_text = esc_html__( 'Activate WooCommerce', 'bangladeshi-payments-mobile' );
	} else {
		$action_url  = wp_nonce_url(
			self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ),
			'install-plugin_woocommerce'
		);
		$button_text = esc_html__( 'Install WooCommerce', 'bangladeshi-payments-mobile' );
	}
	?>
	<div class="notice notice-error bpm-missing-notice-wrap" style="padding:12px;border-left-color:#d63638;">
		<p style="font-size:14px;margin:0 0 8px;">
			<strong><?php esc_html_e( 'Bangladeshi Payments Mobile requires WooCommerce', 'bangladeshi-payments-mobile' ); ?></strong>
		</p>
		<p style="margin:0 0 12px;color:#50575e;">
			<?php esc_html_e( 'This plugin requires WooCommerce to accept and manage mobile payments. Please install or activate WooCommerce to get started.', 'bangladeshi-payments-mobile' ); ?>
		</p>
		<p style="margin: 0;">
			<a href="<?php echo esc_url( $action_url ); ?>" class="button button-primary">
				<?php echo $button_text; ?>
			</a>
		</p>
	</div>
	<?php
}