<?php
/**
 * Enqueue Frontend and Admin Assets (Enterprise Asset Controller).
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue frontend gateway assets only on checkout & pay pages.
 */
function bangladeshi_payments_enqueue_scripts() {
    if ( function_exists( 'is_checkout' ) && ( is_checkout() || is_order_received_page() || is_checkout_pay_page() ) ) {
        wp_enqueue_style(
            'bpm-frontend-style',
            BPM_PLUGIN_URL . 'assets/css/style.css',
            array(),
            BPM_VERSION
        );
    }
}
add_action( 'wp_enqueue_scripts', 'bangladeshi_payments_enqueue_scripts' );

/**
 * Enqueue admin styles only on plugin pages and WooCommerce checkout settings.
 *
 * @param string $hook Page hook identifier.
 */
function bangladeshi_payments_enqueue_admin_styles( $hook ) {
    // Check if on WooCommerce Checkout Settings tab.
    $is_wc_gateways = isset( $_GET['page'], $_GET['tab'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        && 'wc-settings' === $_GET['page']
        && 'checkout' === $_GET['tab'];

    $is_plugin_screen = strpos( (string) $hook, 'bangladeshi-payments' ) !== false;

    if ( ! $is_plugin_screen && ! $is_wc_gateways ) {
        return;
    }

    // Main Admin CSS.
    wp_enqueue_style(
        'bpm-admin-style',
        BPM_PLUGIN_URL . 'assets/css/admin-style.css',
        array(),
        BPM_VERSION
    );

    // Check if we are currently viewing the reports tab or legacy transaction hook.
    $is_reports_screen = strpos( (string) $hook, 'bangladeshi-payments-transaction-info' ) !== false || 
                         ( isset( $_GET['page'], $_GET['tab'] ) && 'bangladeshi-payments-mobile' === $_GET['page'] && 'reports' === $_GET['tab'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    // Enqueue DataTables on the Transaction Reports screen.
    if ( $is_reports_screen ) {
        wp_enqueue_style(
            'bpm-datatables-css',
            BPM_PLUGIN_URL . 'admin/assets/css/jquery.dataTables.min.css',
            array(),
            '1.13.6'
        );

        wp_enqueue_script(
            'bpm-datatables-js',
            BPM_PLUGIN_URL . 'admin/assets/js/jquery.dataTables.min.js',
            array( 'jquery' ),
            '1.13.6',
            true
        );

        // DataTables initialization script matching modern table class selector.
        $inline_script = "
            jQuery(document).ready(function($){
                if ($('.bpm-trans-table').length) {
                    $('.bpm-trans-table').DataTable({
                        'order': [],
                        'pageLength': 25,
                        'columnDefs': [
                            { 'orderable': false, 'targets': -1 }
                        ]
                    });
                }
            });
        ";
        wp_add_inline_script( 'bpm-datatables-js', $inline_script );
    }
}
add_action( 'admin_enqueue_scripts', 'bangladeshi_payments_enqueue_admin_styles' );