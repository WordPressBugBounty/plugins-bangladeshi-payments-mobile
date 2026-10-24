<?php
/**
 * Admin Menu Registration & Tab Navigation Controller (Single Column Modern Tab Bar).
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Admin Menu (Clean single root menu).
 */
add_action( 'admin_menu', 'bpm_register_admin_menus' );
function bpm_register_admin_menus() {
    add_menu_page(
        __( 'Bangladeshi Payments Mobile', 'bangladeshi-payments-mobile' ),
        __( 'BD Payments', 'bangladeshi-payments-mobile' ),
        'manage_woocommerce',
        'bangladeshi-payments-mobile',
        'bpm_render_dashboard_tab_view',
        'dashicons-money-alt',
        56
    );
}

/**
 * Render Master Tab Controller.
 */
function bpm_render_dashboard_tab_view() {
    if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'bangladeshi-payments-mobile' ) );
    }

    $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';

    // 6 Tabs across single unified row
    $tabs = array(
        'overview' => array(
            'label' => __( 'Payment Gateways', 'bangladeshi-payments-mobile' ),
            'icon'  => 'dashicons-smartphone',
        ),
        'reports'  => array(
            'label' => __( 'Transactions', 'bangladeshi-payments-mobile' ),
            'icon'  => 'dashicons-list-view',
        ),
        'security' => array(
            'label' => __( 'Advance COD & Fraud', 'bangladeshi-payments-mobile' ),
            'icon'  => 'dashicons-shield-alt',
        ),
        'checkout' => array(
            'label' => __( 'Checkout & Address', 'bangladeshi-payments-mobile' ),
            'icon'  => 'dashicons-cart',
        ),
        'whatsapp' => array(
            'label' => __( 'WhatsApp & Tools', 'bangladeshi-payments-mobile' ),
            'icon'  => 'dashicons-whatsapp',
        ),
        'invoice'  => array(
            'label' => __( 'Invoice & Slips', 'bangladeshi-payments-mobile' ),
            'icon'  => 'dashicons-media-document',
        ),
    );
    ?>

    <div class="wrap bpm-dashboard-wrap">
        <!-- Dashboard Top Header -->
        <div class="bpm-admin-header">
            <div class="bpm-admin-title">
                <div class="bpm-brand-logo-icon">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
                <div>
                    <h1><?php esc_html_e( 'Bangladeshi Payments Mobile', 'bangladeshi-payments-mobile' ); ?></h1>
                </div>
                <span class="bpm-version-badge">v<?php echo esc_html( BPM_VERSION ); ?></span>
            </div>

            <div class="bpm-header-actions">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ); ?>" class="bpm-header-btn">
                    <span class="dashicons dashicons-admin-generic" style="font-size: 15px; width: 15px; height: 15px;"></span>
                    <?php esc_html_e( 'WooCommerce Settings', 'bangladeshi-payments-mobile' ); ?>
                </a>
            </div>
        </div>

        <!-- Single Horizontal Unified Tab Bar -->
        <div class="bpm-tab-bar-container">
            <nav class="bpm-single-row-tabs">
                <?php foreach ( $tabs as $slug => $tab ) : ?>
                    <?php
                    $is_active = ( $active_tab === $slug );
                    $tab_url   = admin_url( 'admin.php?page=bangladeshi-payments-mobile&tab=' . $slug );
                    ?>
                    <a href="<?php echo esc_url( $tab_url ); ?>" class="bpm-tab-item <?php echo $is_active ? 'active' : ''; ?>">
                        <span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?> bpm-tab-icon"></span>
                        <span><?php echo esc_html( $tab['label'] ); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Dynamic Content Router -->
        <div class="bpm-tab-pane">
            <?php
            switch ( $active_tab ) {
                case 'reports':
                    if ( function_exists( 'bangladeshi_payments_render_transaction_info_page' ) ) {
                        bangladeshi_payments_render_transaction_info_page();
                    } elseif ( function_exists( 'bangladeshi_payments_render_transaction_page' ) ) {
                        bangladeshi_payments_render_transaction_page();
                    }
                    break;

                case 'security':
                    if ( function_exists( 'bpm_render_security_settings_tab' ) ) {
                        bpm_render_security_settings_tab();
                    }
                    break;

                case 'checkout':
                    if ( function_exists( 'bpm_render_checkout_settings_tab' ) ) {
                        bpm_render_checkout_settings_tab();
                    }
                    break;

                case 'whatsapp':
                    if ( function_exists( 'bpm_render_whatsapp_settings_tab' ) ) {
                        bpm_render_whatsapp_settings_tab();
                    }
                    break;

                case 'invoice':
                    if ( function_exists( 'bpm_render_invoice_settings_tab' ) ) {
                        bpm_render_invoice_settings_tab();
                    }
                    break;

                case 'overview':
                default:
                    if ( function_exists( 'bangladeshi_payments_render_general_page' ) ) {
                        bangladeshi_payments_render_general_page();
                    }
                    break;
            }
            ?>
        </div>
    </div>
    <?php
}