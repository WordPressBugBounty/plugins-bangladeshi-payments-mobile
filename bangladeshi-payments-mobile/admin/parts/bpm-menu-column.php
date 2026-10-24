<?php
/**
 * WooCommerce Orders Custom Columns (Pure Internal CSS Enterprise Edition).
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register custom order list table columns (Legacy CPT & HPOS).
 *
 * @param array $columns Existing table columns.
 * @return array
 */
function bpm_register_order_columns( $columns ) {
    $new_columns = array();

    // Insert directly after the Order Status column for standard WooCommerce UX
    foreach ( $columns as $key => $title ) {
        $new_columns[ $key ] = $title;
        if ( 'order_status' === $key ) {
            $new_columns['bpm_payment_info'] = esc_html__( 'Mobile Payment', 'bangladeshi-payments-mobile' );
        }
    }

    // Fallback placement if order_status column key was modified by another plugin
    if ( ! isset( $new_columns['bpm_payment_info'] ) ) {
        $target_index = 4;
        $new_columns  = array_slice( $columns, 0, $target_index, true ) 
                      + array( 'bpm_payment_info' => esc_html__( 'Mobile Payment', 'bangladeshi-payments-mobile' ) ) 
                      + array_slice( $columns, $target_index, null, true );
    }

    return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'bpm_register_order_columns', 20 );
add_filter( 'manage_woocommerce_page_wc-orders_columns', 'bpm_register_order_columns', 20 );

/**
 * Make Mobile Payment column sortable.
 *
 * @param array $sortable_columns
 * @return array
 */
function bpm_sortable_order_columns( $sortable_columns ) {
    $sortable_columns['bpm_payment_info'] = 'payment_method';
    return $sortable_columns;
}
add_filter( 'manage_edit-shop_order_sortable_columns', 'bpm_sortable_order_columns' );
add_filter( 'woocommerce_shop_order_list_table_sortable_columns', 'bpm_sortable_order_columns' );

/**
 * Enqueue Internal Styles for Custom Order Columns.
 */
add_action( 'admin_head', 'bpm_enqueue_order_column_styles' );
function bpm_enqueue_order_column_styles() {
    $screen = get_current_screen();
    if ( ! $screen || ! in_array( $screen->id, array( 'edit-shop_order', 'woocommerce_page_wc-orders' ), true ) ) {
        return;
    }
    ?>
    <?php
}

/**
 * Populate column values for orders (Legacy CPT & HPOS).
 *
 * @param string          $column_name       Name of the column being displayed.
 * @param int|WC_Order    $order_or_order_id Order instance or Order ID.
 */
function bpm_render_order_columns( $column_name, $order_or_order_id ) {
    if ( 'bpm_payment_info' !== $column_name ) {
        return;
    }

    $order = $order_or_order_id instanceof WC_Order ? $order_or_order_id : wc_get_order( $order_or_order_id );

    if ( ! $order instanceof WC_Order ) {
        return;
    }

    $payment_method     = $order->get_payment_method();
    $supported_gateways = array( 'bkash', 'nagad', 'rocket', 'upay' );

    // If order is not using one of our mobile gateways, render a clean muted dash
    if ( ! in_array( $payment_method, $supported_gateways, true ) ) {
        echo '<span class="bpm-cell-empty-dash">&ndash;</span>';
        return;
    }

    $brand_meta = array(
        'bkash'  => array( 'color' => '#E2136E', 'bg' => '#fdf2f8', 'border' => '#fbcfe8' ),
        'nagad'  => array( 'color' => '#ED1C24', 'bg' => '#fef2f2', 'border' => '#fecaca' ),
        'rocket' => array( 'color' => '#8C3494', 'bg' => '#faf5ff', 'border' => '#f3e8ff' ),
        'upay'   => array( 'color' => '#004B87', 'bg' => '#f0f9ff', 'border' => '#bae6fd' ),
    );

    $phone    = $order->get_meta( "_{$payment_method}_phone", true );
    $trx_id   = $order->get_meta( "_{$payment_method}_transaction_id", true );
    $meta_acc = $order->get_meta( "_{$payment_method}_account_type", true );
    $brand    = isset( $brand_meta[ $payment_method ] ) ? $brand_meta[ $payment_method ] : array( 'color' => '#0f172a', 'bg' => '#f8fafc', 'border' => '#e2e8f0' );
    $title    = $order->get_payment_method_title();
    $acc_type = $meta_acc ? ucfirst( $meta_acc ) : '';
    ?>
    <div class="bpm-cell-wrapper">
        
        <!-- Gateway & Account Type Header -->
        <div class="bpm-cell-header-row">
            <span class="bpm-cell-gateway-pill" style="background: <?php echo esc_attr( $brand['bg'] ); ?>; color: <?php echo esc_attr( $brand['color'] ); ?>; border: 1px solid <?php echo esc_attr( $brand['border'] ); ?>;">
                <span class="bpm-cell-pill-dot" style="background: <?php echo esc_attr( $brand['color'] ); ?>;"></span>
                <?php echo esc_html( $title ? $title : ucfirst( $payment_method ) ); ?>
            </span>

            <?php if ( $acc_type ) : ?>
                <span class="bpm-cell-acc-type">
                    (<?php echo esc_html( $acc_type ); ?>)
                </span>
            <?php endif; ?>
        </div>

        <!-- Sender / Payer Number -->
        <?php if ( $phone ) : ?>
            <div class="bpm-cell-phone-row">
                <svg class="bpm-cell-phone-svg" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                <span class="bpm-cell-phone-num"><?php echo esc_html( $phone ); ?></span>
            </div>
        <?php endif; ?>

        <!-- TrxID Chip with 1-Click Copy Interaction -->
        <?php if ( $trx_id ) : ?>
            <div>
                <span 
                    class="bpm-copy-pill"
                    title="<?php esc_attr_e( 'Click to copy TrxID', 'bangladeshi-payments-mobile' ); ?>"
                    onclick="bpmCopyTrx(this, '<?php echo esc_js( $trx_id ); ?>')"
                >
                    <span><?php echo esc_html( $trx_id ); ?></span>
                    <svg class="bpm-copy-icon" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                </span>
            </div>
        <?php endif; ?>

        <?php if ( ! $phone && ! $trx_id ) : ?>
            <span class="bpm-cell-empty-proof"><?php esc_html_e( 'No proof entered', 'bangladeshi-payments-mobile' ); ?></span>
        <?php endif; ?>
    </div>
    <?php
}
add_action( 'manage_shop_order_posts_custom_column', 'bpm_render_order_columns', 25, 2 );
add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'bpm_render_order_columns', 25, 2 );

/**
 * Enqueue unified lightweight JavaScript copy handler in admin footer.
 */
add_action( 'admin_footer', 'bpm_order_columns_script' );
function bpm_order_columns_script() {
    $screen = get_current_screen();
    if ( ! $screen || ! in_array( $screen->id, array( 'edit-shop_order', 'woocommerce_page_wc-orders' ), true ) ) {
        return;
    }
    ?>
    <script>
    function bpmCopyTrx(element, text) {
        if (!navigator.clipboard) {
            return;
        }
        navigator.clipboard.writeText(text).then(function() {
            var originalHtml = element.innerHTML;
            element.innerHTML = '<span style="color:#059669; font-weight:700;">✓ Copied</span>';
            element.style.borderColor = '#10b981';
            element.style.background = '#ecfdf5';

            setTimeout(function() {
                element.innerHTML = originalHtml;
                element.style.borderColor = '#cbd5e1';
                element.style.background = '#f8fafc';
            }, 1400);
        });
    }
    </script>
    <?php
}