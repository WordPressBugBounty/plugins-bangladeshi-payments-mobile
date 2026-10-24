<?php
/**
 * Transaction Reports Admin Page (Pure Internal CSS & Enterprise Architecture).
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle CSV Export for filtered transactions.
 */
add_action( 'admin_init', 'bpm_export_transactions_csv' );
function bpm_export_transactions_csv() {
    if ( ! isset( $_GET['bpm_action'] ) || 'export_csv' !== $_GET['bpm_action'] ) {
        return;
    }

    if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Unauthorized export request.', 'bangladeshi-payments-mobile' ) );
    }

    check_admin_referer( 'bpm_export_action', 'bpm_export_nonce' );

    $method     = isset( $_GET['payment_method'] ) ? sanitize_key( wp_unslash( $_GET['payment_method'] ) ) : 'all';
    $start_date = ! empty( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
    $end_date   = ! empty( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
    $search_kw  = ! empty( $_GET['bpm_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bpm_search'] ) ) : '';

    $query_args = array(
        'status' => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
        'limit'  => -1,
    );

    if ( 'all' !== $method && in_array( $method, array( 'bkash', 'nagad', 'rocket', 'upay' ), true ) ) {
        $query_args['payment_method'] = $method;
    } else {
        $query_args['payment_method'] = array( 'bkash', 'nagad', 'rocket', 'upay' );
    }

    if ( $start_date && $end_date ) {
        $query_args['date_created'] = $start_date . '...' . $end_date;
    } elseif ( $start_date ) {
        $query_args['date_created'] = '>=' . $start_date;
    } elseif ( $end_date ) {
        $query_args['date_created'] = '<=' . $end_date;
    }

    $orders = wc_get_orders( $query_args );

    header( 'Content-Type: text/csv; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename=bd-payments-report-' . gmdate( 'Y-m-d' ) . '.csv' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    $output = fopen( 'php://output', 'w' );
    fputs( $output, "\xEF\xBB\xBF" ); // UTF-8 BOM for Excel compatibility

    fputcsv( $output, array( 'Order ID', 'Method', 'Customer Name', 'Customer Email', 'Phone Number', 'Transaction ID', 'Amount (BDT)', 'Order Status', 'Date Created' ) );

    foreach ( $orders as $order ) {
        $m_slug = $order->get_payment_method();
        $trx_id = $order->get_meta( "_{$m_slug}_transaction_id", true );
        $phone  = $order->get_meta( "_{$m_slug}_phone", true );

        if ( $search_kw ) {
            $matched = ( false !== stripos( (string) $order->get_id(), $search_kw ) ) ||
                       ( false !== stripos( (string) $trx_id, $search_kw ) ) ||
                       ( false !== stripos( (string) $phone, $search_kw ) ) ||
                       ( false !== stripos( $order->get_formatted_billing_full_name(), $search_kw ) );
            if ( ! $matched ) {
                continue;
            }
        }

        fputcsv(
            $output,
            array(
                '#' . $order->get_id(),
                $order->get_payment_method_title(),
                $order->get_formatted_billing_full_name(),
                $order->get_billing_email(),
                $phone,
                $trx_id,
                $order->get_total(),
                wc_get_order_status_name( $order->get_status() ),
                $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : '',
            )
        );
    }

    fclose( $output );
    exit;
}

/**
 * Handle Quick Status Change from Reports Table.
 */
add_action( 'admin_init', 'bpm_handle_quick_order_status' );
function bpm_handle_quick_order_status() {
    if ( ! isset( $_GET['bpm_action'] ) || 'quick_verify' !== $_GET['bpm_action'] ) {
        return;
    }

    if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
        return;
    }

    check_admin_referer( 'bpm_verify_action', 'bpm_verify_nonce' );

    $order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
    if ( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( $order ) {
            $order->update_status( 'completed', __( 'Order verified and completed via Bangladeshi Payments Report.', 'bangladeshi-payments-mobile' ) );
        }
    }

    wp_safe_redirect( remove_query_arg( array( 'bpm_action', 'order_id', 'bpm_verify_nonce' ) ) );
    exit;
}

/**
 * Helper to get clean inline SVGs for payment brands.
 */
function bpm_get_report_brand_svg( $slug ) {
    switch ( $slug ) {
        case 'bkash':
            return '<svg class="bpm-brand-svg-sq" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44.494 44.291"><g fill="none"><path fill="#DF146E" d="M42.31 44.291H2.182C.981 44.291 0 43.308 0 42.107V2.186C0 .982.981 0 2.182 0H42.31c1.203 0 2.184.982 2.184 2.186v39.921c0 1.201-.981 2.184-2.184 2.184"/><path fill="#FFF" d="M31.894 24.251l-14.107-2.246 1.909 8.329zm.572-.682L21.374 8.16l-3.623 13.106zm-15.402-2.482L5.441 6.239l15.221 1.819zm-5.639-6.154l-6.449-6.08h1.695zm24.504 1.15L33.2 23.486l-4.426-6.118zM21.417 30.232l10.71-4.3.454-1.365zm-8.933 7.821l4.589-16.102 2.326 10.479zm24.099-21.914l-1.128 3.056 4.059-.07z"/></g></svg>';
        case 'nagad':
            return '<svg class="bpm-brand-svg-rect" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 178.69 98"><g transform="translate(-3.63 -11.08) scale(2.59472)"><g fill="#ed1c24"><path d="M80.6 20.7H60.4c-.4 0-.6.3-.6.6v1.6c0 .4.3.6.6.6h15.1v8.4c-.4-.6-.9-1.2-1.4-1.8-1.8-1.8-3.7-2.7-5.7-2.7-1.6 0-2.9.8-4 2.2-.9 1.2-1.4 2.6-1.4 3.9 0 1.3.2 3 1.2 4.6 1.2 1.9 3.2 2.5 5 2.5 2.3 0 4.2-1.6 4.2-3.7 0-1.2-.6-2.2-1.7-2.9l-1.1-.6v1.8c-.1.5-.9 1.2-1.8 1.2-.8 0-1.5-.3-2-.8-.3-.3-.5-.9-.4-1.3 0-.6.2-1.1.6-1.6.5-.6 1-.9 1.8-.9 2 0 3.7.9 5.1 2.9 1.1 1.7 1.7 3.3 1.7 5.1V44l3 1.8c.1.1.2.1.3.1.4 0 .6-.3.6-.6V23.6h1.2c.4 0 .6-.3.6-.6v-1.6c0-.4-.3-.7-.7-.7z"/><path d="M121 20.7H95.4c-.4 0-.6.3-.6.6v2.8c-2.5-2.6-4.7-3.9-6.7-3.9-1.9 0-3.5.4-4.9 1.5-1.3 1-2.1 2.3-2.1 3.8 0 4.5 5 4.4 6.3 3.8.2-.1.5-.3.8-.3 1 0 1.4.8 1.4 1.5 0 1-1.5 1.9-3.3 1.9-1 0-1.6-.3-2-.9l-.8-1.2-.5 1.4c-.1.3-.3.7-.3 1.2 0 1 .5 2 1.5 2.9.9.8 2 1.2 3.2 1.2 1.9 0 3.5-.7 4.5-2.1.9-1.1 1.3-2.4 1.3-3.9 0-.8-.3-1.7-1-2.7-.8-1.2-1.8-1.8-2.9-1.8-.4 0-.9.1-1.4.3-.2.1-.6.2-.7.2-.2 0-.5-.1-.7-.4-.2-.2-.4-.5-.4-1 0-1.1 1-2.2 2.8-2.2h.1c1.2 0 2.4.6 3.5 1.7.9.9 1.6 1.8 2.2 2.8v15.9l3 1.8c.1.1.2.1.3.1.4 0 .6-.3.6-.6V23.6h4.6v13.9l3.6 1.5h.2c.3 0 .6-.2.6-.6v-.1c.6-4.1 2.4-6.9 5.4-8.6v.8c0 .6 0 2.1.1 2.9 0 .5 0 .8.1 1.1 0 1.6.2 4 .7 5.8 1 3.4 2.7 4.2 3.9 4.2h.1c.7 0 1.3-.2 1.7-.6.2-.2.5-.6.5-1.3 0-.6-.1-1.1-.3-1.5l-.3-.5-.6.1c-.6.2-.9.2-.9.1h-.1c-.2 0-.2 0-.3-.1-.2-.1-.6-.4-.9-1.5-.2-.8-.3-1.9-.3-2.5 0-4.5.9-7.9 2.4-8.6h.1c.2-.1.4-.3.4-.6 0-.1 0-.2-.1-.3v-.1c-.7-1.4-2.2-2.4-4.2-2.9h-.4c-1.6.3-3.5 1.4-5.9 3.4-.6.5-1.2 1-1.7 1.5v-5.5h14c.4 0 .6-.3.6-.6v-1.6c.1-.4-.2-.7-.6-.7z"/></g><path fill="#ed1c24" d="M53.2 28.1c0 .7 0 1.3-.1 1.9-.2 2.8-.9 5.4-2 7.8-.4 1-.9 1.9-1.5 2.8-4.2 6.6-11.6 11-20 11-3.6 0-7-.8-10-2.2C11.6 45.7 6 37.5 6 28.1 6 18.8 11.4 10.8 19.1 7c-.6.8-1.2 1.6-1.7 2.5 0 .1-.1.1-.1.2-.3.3-.6.5-.9.8-.4.3-.7.7-1.1 1l-.2.2-.2.2c-.1.1-.2.3-.4.4-.2.3-.5.6-.7.9-1 1.3-1.8 2.7-2.4 4.2-.1.1-.1.3-.2.4-.1.2-.1.4-.2.5 0 .1-.1.2-.1.3-.1.3-.2.5-.3.8-.1.2-.1.4-.2.5 0 .1-.1.2-.1.3 0 .2-.1.4-.1.6l-.3 1.8c0 .2 0 .3-.1.5v2.4c0 6.4 2.9 12.2 7.6 15.9 3.6 2.9 8.1 4.7 13 4.7 4.5 0 8.6-1.4 12-3.9 2.5-1.8 4.5-4.1 6-6.8.2-.4.4-.7.6-1.1 1.2-2.5 1.9-5.2 1.9-8.1v-.7c0-.7 0-1.3-.1-2l.1.1c.3.3.6.5.9.8.3-.5.6-.9.9-1.4.2.9.3 1.8.4 2.8.1.9.1 1.6.1 2.3z"/><path fill="#f7941d" d="M32.4 9.2L28 1.1c-7.3 3.3-12.3 10.6-12.3 19.1 0 4.3 1.3 8.3 3.5 11.6-.2-1.1-.2-2.2-.2-3.4.1-8.7 5.6-16.1 13.4-19.2z"/><path fill="#ed1c24" d="M35.9 13.2c1.8-.5 3.8-.7 5.7-.7 1.2 0 2.5.1 3.6.3l-.1-3.6-.2-7.2c-.8-.1-1.7-.2-2.6-.2-4 0-7.7 1.3-10.7 3.4L34 9.6c-4.3 1.5-7.8 4.5-10 8.4-1.1 1.9-1.9 4.1-2.2 6.4.6-1.2 1.3-2.3 2.1-3.3 2.9-3.8 7.1-6.7 12-7.9z"/><path fill="#f7941d" d="M46.4 9.1l.2 5.1c-1.7-.5-3.4-.8-5.3-.8-3.5 0-6.8 1-9.7 2.7-3.6 2.2-6.4 5.7-7.8 9.8 1.4-1.9 3.1-3.6 5.1-4.9 2.9-1.9 6.4-3 10.1-3 2.8 0 5.4.6 7.8 1.7 1.8.8 3.4 1.9 4.8 3.3l2.8-4.2 3.6-5.5c-3.2-2.6-7.2-4.2-11.6-4.2z"/><path fill="#f7941d" d="M50.9 25.7v.7c0 4.2-1.7 7.7-1.9 8.1-.2.4-.4.7-.6 1.1-1.5 2.7-3.5 5-6 6.8-3.4 2.4-7.5 3.9-12 3.9-4.9 0-9.5-1.7-13-4.7-4.6-3.8-7.6-9.5-7.6-15.9v-2.4c0-.2 0-.3.1-.5l.3-1.8c0-.2.1-.4.1-.6 0-.1.1-.2.1-.3.1-.2.1-.4.2-.5.1-.3.2-.6.3-.8 0-.1.1-.2.1-.3.1-.2.1-.4.2-.5.1-.1.1-.3.2-.4.6-1.5 1.5-2.9 2.4-4.2.2-.3.5-.6.7-.9.1-.1.2-.3.4-.4.1-.1.1-.2.2-.2.1-.1.1-.2.2-.2.3-.4.7-.7 1.1-1 .3-.3.6-.5.9-.8 0 .1-.1.1-.1.2s-.1.2-.1.2c-1.2 2.4-2 5.3-2.3 8.3-.1.8-.1 1.6-.1 2.5 0 10.8 6.3 19.5 14.1 19.5h.8c1.1 0 2.2-.2 3.3-.4 5.3-1.4 9.2-6.3 9.2-12v-.3c-.1-3.4-1.5-6.5-3.8-8.6 1.6 0 3.2.3 4.7.7 2.9.7 5.5 2.1 7.8 3.9l.1.1c-.1.4 0 1.1 0 1.7z"/></g></svg>';
        case 'rocket':
            return '<svg class="bpm-brand-svg-rect" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 378 253.05"><path fill="#8C3494" d="M0 253.05h378V0H0z"/><path fill="#FFF" d="M263.39 41.11l-44.88 44.89 3.14 25.2 8.28-17.33 25.59 32.68 35.83-110.25-119.31 49.61 32.29 16.93 59.06-41.73zM207.1 167.11h-26.38c-16.34.98-21.66-25.99-1.58-29.53h27.96zm17.31-47.25h-47.25c-36.03 5.51-39.77 57.88-.4 64.97h47.65v-47.25c15.88.52 19.16 20.34 7.48 27.56 2.36 2.76 11.82 13 11.82 13 24.8-21.39 10.63-56.57-19.3-58.28zM323.25 143.48h-17.33l-7.48 17.33h14.54c4.43-.2 4.63 6.3 0 6.69h-38.19v-29.92h58.67v-17.72h-8.66c-2.56-36.62-46.37-37.7-57.88-17.72 2.27 1.87 12.99 11.42 12.99 11.42 6.6-10.34 24.91-9.84 27.56 6.3h-50.01v64.97h56.31c12.8-.2 18.7-9.65 20.08-17.72.78-12.99-7.09-21.66-18.12-23.63zM154.72 119.86h-22.84c-7.28 8.86-19.88 44.69 5.12 71.66 0 0 10.83-9.25 12.99-11.02-7.67-7.48-20.47-38 4.73-60.64zM96.59 192.15c0 7.91-6.41 14.31-14.32 14.31s-14.31-6.4-14.31-14.31 6.4-14.32 14.31-14.32 14.32 6.41 14.32 14.32z"/></svg>';
        default:
            return '<svg class="bpm-brand-svg-sq" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 208"><circle cx="24.8" cy="19.8" r="19.5" fill="#FDB913"/><circle cx="124.6" cy="19.8" r="19.5" fill="#004B87"/><path fill="#004B87" d="M57.9 114.1c-.6 5.8 1.8 11.1 5.8 14.6 3 2.6 7 4.2 11.3 4.3 10.2.2 20.7-1 30.6-4.4 25.3-8.3 39.1-29.9 38.9-56v-8.7c0-2.6-1-5-2.5-6.8-1.9-2.3-4.9-3.8-8.1-3.8h-15.5c-3.4 0-6.4 1.5-8.3 3.9-1.5 1.8-2.4 4.2-2.4 6.7v3.5c0 1.2 0 4.1 0 5.1 0 4.4-1 9.5-3.1 13.1-2.6 4.5-6.2 7.2-11.3 9.3-6.1 2.4-12.6 3.4-19.1 3.8-4 .2-7.6 1.8-10.5 4.4-3.3 2.6-5.4 6.5-5.8 11z"/><path fill="#FDB913" d="M91.7 114.1c.6 5.8-1.8 11.1-5.8 14.6-3 2.6-7 4.2-11.3 4.3-10.2.2-20.7-1-30.6-4.4-25.3-8.3-39.1-29.9-38.9-56v-8.7c0-2.6 1-5 2.5-6.8 1.9-2.3 4.9-3.8 8.1-3.8h15.5c3.4 0 6.4 1.5 8.3 3.9 1.5 1.8 2.4 4.2 2.4 6.7v3.5c0 1.2 0 4.1 0 5.1 0 4.4 1 9.5 3.1 13.1 2.6 4.5 6.2 7.2-11.3 9.3 6.1 2.4 12.6 3.4 19.1 3.8 4 .2 7.6 1.8 10.5 4.4 3.3 2.6 5.4 6.5 5.8 11z"/></svg>';
    }
}

/**
 * Main Render Function for Transaction Reports.
 */
function bangladeshi_payments_render_transaction_info_page() {
    if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bangladeshi-payments-mobile' ) );
    }

    $selected_method = isset( $_GET['payment_method'] ) ? sanitize_key( wp_unslash( $_GET['payment_method'] ) ) : 'all';
    $start_date      = ! empty( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
    $end_date        = ! empty( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
    $search_kw       = ! empty( $_GET['bpm_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bpm_search'] ) ) : '';

    $payment_methods = array(
        'all'    => esc_html__( 'All Gateways', 'bangladeshi-payments-mobile' ),
        'bkash'  => 'bKash',
        'nagad'  => 'Nagad',
        'rocket' => 'Rocket',
        'upay'   => 'Upay',
    );

    $brand_meta = array(
        'bkash'  => array( 'color' => '#E2136E', 'bg' => '#fdf2f8', 'border' => '#fbcfe8' ),
        'nagad'  => array( 'color' => '#ED1C24', 'bg' => '#fef2f2', 'border' => '#fecaca' ),
        'rocket' => array( 'color' => '#8C3494', 'bg' => '#faf5ff', 'border' => '#f3e8ff' ),
        'upay'   => array( 'color' => '#004B87', 'bg' => '#f0f9ff', 'border' => '#bae6fd' ),
    );

    $query_args = array(
        'status' => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
        'limit'  => 300,
    );

    if ( 'all' !== $selected_method && array_key_exists( $selected_method, $payment_methods ) ) {
        $query_args['payment_method'] = $selected_method;
    } else {
        $query_args['payment_method'] = array( 'bkash', 'nagad', 'rocket', 'upay' );
    }

    if ( $start_date && $end_date ) {
        $query_args['date_created'] = $start_date . '...' . $end_date;
    } elseif ( $start_date ) {
        $query_args['date_created'] = '>=' . $start_date;
    } elseif ( $end_date ) {
        $query_args['date_created'] = '<=' . $end_date;
    }

    $raw_orders      = wc_get_orders( $query_args );
    $orders          = array();
    $total_volume    = 0.0;
    $count_completed = 0;
    $count_on_hold   = 0;

    foreach ( $raw_orders as $o ) {
        $m_slug = $o->get_payment_method();
        $trx_id = $o->get_meta( "_{$m_slug}_transaction_id", true );
        $phone  = $o->get_meta( "_{$m_slug}_phone", true );

        if ( $search_kw ) {
            $matched = ( false !== stripos( (string) $o->get_id(), $search_kw ) ) ||
                       ( false !== stripos( (string) $trx_id, $search_kw ) ) ||
                       ( false !== stripos( (string) $phone, $search_kw ) ) ||
                       ( false !== stripos( $o->get_formatted_billing_full_name(), $search_kw ) );
            if ( ! $matched ) {
                continue;
            }
        }

        $orders[] = $o;
        $total_volume += (float) $o->get_total();

        if ( 'completed' === $o->get_status() ) {
            $count_completed++;
        } elseif ( 'on-hold' === $o->get_status() ) {
            $count_on_hold++;
        }
    }

    $export_url = add_query_arg(
        array(
            'page'           => 'bangladeshi-payments-mobile',
            'tab'            => 'reports',
            'bpm_action'     => 'export_csv',
            'payment_method' => $selected_method,
            'start_date'     => $start_date,
            'end_date'       => $end_date,
            'bpm_search'     => $search_kw,
        ),
        admin_url( 'admin.php' )
    );
    $export_url = wp_nonce_url( $export_url, 'bpm_export_action', 'bpm_export_nonce' );
    ?>

    <div class="wrap bpm-report-shell">
        <!-- Subbar -->
        <div class="bpm-subbar">
            <div class="bpm-subbar-info">
                <h3><?php esc_html_e( 'Mobile Transaction Ledger & Verification', 'bangladeshi-payments-mobile' ); ?></h3>
                <p><?php esc_html_e( 'Audit transaction IDs, customer mobile numbers, and verify payments in one click.', 'bangladeshi-payments-mobile' ); ?></p>
            </div>
            <div>
                <a href="<?php echo esc_url( $export_url ); ?>" class="bpm-btn-export">
                    <svg class="bpm-export-icon" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span><?php esc_html_e( 'Export Ledger (CSV)', 'bangladeshi-payments-mobile' ); ?></span>
                </a>
            </div>
        </div>

        <!-- 4 Metrics KPI Tiles -->
        <div class="bpm-metrics-grid">
            <div class="bpm-metric-tile">
                <div class="bpm-metric-head">
                    <span class="bpm-metric-title"><?php esc_html_e( 'Aggregated Volume', 'bangladeshi-payments-mobile' ); ?></span>
                    <div class="bpm-metric-icon bpm-metric-icon-blue">৳</div>
                </div>
                <div class="bpm-metric-val bpm-metric-val-blue"><?php echo wp_kses_post( wc_price( $total_volume ) ); ?></div>
            </div>

            <div class="bpm-metric-tile">
                <div class="bpm-metric-head">
                    <span class="bpm-metric-title"><?php esc_html_e( 'Matched Orders', 'bangladeshi-payments-mobile' ); ?></span>
                    <div class="bpm-metric-icon bpm-metric-icon-slate">
                        <span class="dashicons dashicons-clipboard"></span>
                    </div>
                </div>
                <div class="bpm-metric-val"><?php echo esc_html( number_format_i18n( count( $orders ) ) ); ?></div>
            </div>

            <div class="bpm-metric-tile">
                <div class="bpm-metric-head">
                    <span class="bpm-metric-title"><?php esc_html_e( 'Requires Verification', 'bangladeshi-payments-mobile' ); ?></span>
                    <div class="bpm-metric-icon bpm-metric-icon-amber">
                        <span class="dashicons dashicons-clock"></span>
                    </div>
                </div>
                <div class="bpm-metric-val bpm-metric-val-amber"><?php echo esc_html( number_format_i18n( $count_on_hold ) ); ?></div>
            </div>

            <div class="bpm-metric-tile">
                <div class="bpm-metric-head">
                    <span class="bpm-metric-title"><?php esc_html_e( 'Verified & Cleared', 'bangladeshi-payments-mobile' ); ?></span>
                    <div class="bpm-metric-icon bpm-metric-icon-emerald">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </div>
                </div>
                <div class="bpm-metric-val bpm-metric-val-emerald"><?php echo esc_html( number_format_i18n( $count_completed ) ); ?></div>
            </div>
        </div>

        <!-- Filter Hub -->
        <div class="bpm-filter-box">
            <!-- Gateway Pills -->
            <div class="bpm-tab-pills">
                <?php foreach ( $payment_methods as $slug => $label ) : 
                    $tab_active = ( $selected_method === $slug ) ? 'active' : '';
                    $tab_url    = add_query_arg(
                        array(
                            'page'           => 'bangladeshi-payments-mobile',
                            'tab'            => 'reports',
                            'payment_method' => $slug,
                            'start_date'     => $start_date,
                            'end_date'       => $end_date,
                            'bpm_search'     => $search_kw,
                        ),
                        admin_url( 'admin.php' )
                    );
                    ?>
                    <a href="<?php echo esc_url( $tab_url ); ?>" class="bpm-pill-link <?php echo esc_attr( $tab_active ); ?>">
                        <?php echo esc_html( $label ); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <form method="GET" class="bpm-filters-form">
                <input type="hidden" name="page" value="bangladeshi-payments-mobile">
                <input type="hidden" name="tab" value="reports">
                <input type="hidden" name="payment_method" value="<?php echo esc_attr( $selected_method ); ?>">

                <div class="bpm-field-wrap">
                    <label for="bpm_search"><?php esc_html_e( 'Search Anything', 'bangladeshi-payments-mobile' ); ?></label>
                    <input 
                        type="text" 
                        name="bpm_search" 
                        id="bpm_search" 
                        class="bpm-search-input"
                        value="<?php echo esc_attr( $search_kw ); ?>" 
                        placeholder="<?php esc_attr_e( 'TrxID, phone, name, or #ID...', 'bangladeshi-payments-mobile' ); ?>" 
                    >
                </div>

                <div class="bpm-field-wrap">
                    <label for="start_date"><?php esc_html_e( 'Date From', 'bangladeshi-payments-mobile' ); ?></label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr( $start_date ); ?>">
                </div>

                <div class="bpm-field-wrap">
                    <label for="end_date"><?php esc_html_e( 'Date To', 'bangladeshi-payments-mobile' ); ?></label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr( $end_date ); ?>">
                </div>

                <div class="bpm-filter-buttons">
                    <button type="submit" class="button bpm-filter-btn">
                        <?php esc_html_e( 'Apply Filter', 'bangladeshi-payments-mobile' ); ?>
                    </button>
                    <?php if ( $start_date || $end_date || $search_kw || 'all' !== $selected_method ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=bangladeshi-payments-mobile&tab=reports' ) ); ?>" class="button bpm-reset-btn">
                            <?php esc_html_e( 'Clear', 'bangladeshi-payments-mobile' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Transaction Table -->
        <div class="bpm-table-wrapper">
            <table class="bpm-trans-table">
                <thead>
                    <tr>
                        <th class="bpm-th-order"><?php esc_html_e( 'Order', 'bangladeshi-payments-mobile' ); ?></th>
                        <th><?php esc_html_e( 'Channel', 'bangladeshi-payments-mobile' ); ?></th>
                        <th><?php esc_html_e( 'Transaction ID', 'bangladeshi-payments-mobile' ); ?></th>
                        <th><?php esc_html_e( 'Payer Number', 'bangladeshi-payments-mobile' ); ?></th>
                        <th><?php esc_html_e( 'Customer', 'bangladeshi-payments-mobile' ); ?></th>
                        <th><?php esc_html_e( 'Amount', 'bangladeshi-payments-mobile' ); ?></th>
                        <th><?php esc_html_e( 'Date & Time', 'bangladeshi-payments-mobile' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'bangladeshi-payments-mobile' ); ?></th>
                        <th class="bpm-th-actions"><?php esc_html_e( 'Actions', 'bangladeshi-payments-mobile' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $orders ) ) : ?>
                        <tr class="bpm-empty-row">
                            <td colspan="9">
                                <span class="dashicons dashicons-search bpm-empty-icon"></span>
                                <p class="bpm-empty-title"><?php esc_html_e( 'No mobile transactions found', 'bangladeshi-payments-mobile' ); ?></p>
                                <p class="bpm-empty-subtitle"><?php esc_html_e( 'Try selecting a different date range or clearing your search filters.', 'bangladeshi-payments-mobile' ); ?></p>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php
                        foreach ( $orders as $order ) :
                            $m_slug     = $order->get_payment_method();
                            $brand      = isset( $brand_meta[ $m_slug ] ) ? $brand_meta[ $m_slug ] : array( 'color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe' );
                            $trx_id     = $order->get_meta( "_{$m_slug}_transaction_id", true );
                            $phone      = $order->get_meta( "_{$m_slug}_phone", true );
                            $status     = $order->get_status();
                            $status_cls = 'bpm-stat-' . sanitize_html_class( $status );

                            $verify_url = wp_nonce_url(
                                add_query_arg(
                                    array(
                                        'bpm_action' => 'quick_verify',
                                        'order_id'   => $order->get_id(),
                                    )
                                ),
                                'bpm_verify_action',
                                'bpm_verify_nonce'
                            );
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>" class="bpm-order-link">
                                        #<?php echo esc_html( $order->get_id() ); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="bpm-brand-chip" style="background: <?php echo esc_attr( $brand['bg'] ); ?>; color: <?php echo esc_attr( $brand['color'] ); ?>; border: 1px solid <?php echo esc_attr( $brand['border'] ); ?>;">
                                        <?php echo bpm_get_report_brand_svg( $m_slug ); ?>
                                        <span><?php echo esc_html( $order->get_payment_method_title() ); ?></span>
                                    </span>
                                </td>
                                <td>
                                    <?php if ( $trx_id ) : ?>
                                        <span class="bpm-interactive-cell" title="<?php esc_attr_e( 'Click to copy TrxID', 'bangladeshi-payments-mobile' ); ?>" onclick="bpmCopyText('<?php echo esc_js( $trx_id ); ?>', this)">
                                            <span><?php echo esc_html( $trx_id ); ?></span>
                                            <svg class="bpm-copy-icon" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                        </span>
                                    <?php else : ?>
                                        <span class="bpm-empty-dash">&ndash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $phone ) : ?>
                                        <span class="bpm-interactive-cell" title="<?php esc_attr_e( 'Click to copy Phone', 'bangladeshi-payments-mobile' ); ?>" onclick="bpmCopyText('<?php echo esc_js( $phone ); ?>', this)">
                                            <span><?php echo esc_html( $phone ); ?></span>
                                            <svg class="bpm-copy-icon" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                        </span>
                                    <?php else : ?>
                                        <span class="bpm-empty-dash">&ndash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="bpm-customer-cell">
                                        <span class="bpm-cust-name"><?php echo esc_html( $order->get_formatted_billing_full_name() ? $order->get_formatted_billing_full_name() : __( 'Guest', 'bangladeshi-payments-mobile' ) ); ?></span>
                                        <?php if ( $order->get_billing_email() ) : ?>
                                            <span class="bpm-cust-email"><?php echo esc_html( $order->get_billing_email() ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong class="bpm-amount-val"><?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?></strong>
                                </td>
                                <td>
                                    <span class="bpm-time-stamp">
                                        <?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date_i18n( wc_date_format() . ' ' . wc_time_format() ) : '' ); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="bpm-stat-badge <?php echo esc_attr( $status_cls ); ?>">
                                        <span class="bpm-stat-dot"></span>
                                        <?php echo esc_html( wc_get_order_status_name( $status ) ); ?>
                                    </span>
                                </td>
                                <td class="bpm-th-actions">
                                    <div class="bpm-action-cluster">
                                        <?php if ( 'completed' !== $status ) : ?>
                                            <a href="<?php echo esc_url( $verify_url ); ?>" class="bpm-btn-verify-quick" title="<?php esc_attr_e( 'Verify and mark Completed', 'bangladeshi-payments-mobile' ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Verify payment and mark order as Completed?', 'bangladeshi-payments-mobile' ) ); ?>');">
                                                <svg class="bpm-verify-icon" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span><?php esc_html_e( 'Approve', 'bangladeshi-payments-mobile' ); ?></span>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>" class="bpm-btn-view-quick" title="<?php esc_attr_e( 'View Full Order Details', 'bangladeshi-payments-mobile' ); ?>">
                                            <svg class="bpm-view-icon" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function bpmCopyText(text, el) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    const originalHTML = el.innerHTML;
                    el.innerHTML = "<span><?php echo esc_js( __( 'Copied!', 'bangladeshi-payments-mobile' ) ); ?></span>";
                    el.classList.add('bpm-stat-completed');
                    setTimeout(function() {
                        el.innerHTML = originalHTML;
                        el.classList.remove('bpm-stat-completed');
                    }, 1500);
                });
            }
        }
    </script>
    <?php
}