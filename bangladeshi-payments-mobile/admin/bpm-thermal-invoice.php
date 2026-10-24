<?php
/**
 * Thermal POS Invoice & QR Slip Generator (Enterprise Integrated Edition).
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Thermal Print action button to WooCommerce Admin Order Actions.
 */
add_filter( 'woocommerce_admin_order_actions', 'bpm_add_thermal_print_action', 20, 2 );
function bpm_add_thermal_print_action( $actions, $order ) {
    if ( 'yes' !== get_option( 'bpm_enable_invoices', 'yes' ) ) {
        return $actions;
    }

    $order_id  = $order->get_id();
    $print_url = wp_nonce_url( admin_url( 'admin.php?bpm_action=print_doc&doc_type=slip&order_id=' . $order_id ), 'bpm_print_doc_action', 'bpm_print_nonce' );

    $actions['bpm_thermal_print'] = array(
        'url'    => esc_url( $print_url ),
        'name'   => __( 'থার্মাল ইনভয়েস প্রিন্ট', 'bangladeshi-payments-mobile' ),
        'action' => 'print-invoice',
        'target' => '_blank',
    );

    return $actions;
}