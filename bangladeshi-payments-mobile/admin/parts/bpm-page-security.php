<?php
/**
 * Fraud Shield & Advance COD Settings Tab & Protection Engine.
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle form submission for Fraud Shield & COD settings.
 */
function bpm_save_security_settings() {
    if ( ! isset( $_POST['bpm_security_settings_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bpm_security_settings_nonce'] ) ), 'bpm_save_security_settings_action' ) ) {
        echo '<div class="notice notice-error is-dismissible bpm-notice-error"><p>' . esc_html__( 'Security validation failed. Please refresh the page.', 'bangladeshi-payments-mobile' ) . '</p></div>';
        return;
    }

    if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
        echo '<div class="notice notice-error is-dismissible bpm-notice-error"><p>' . esc_html__( 'Unauthorized user permissions.', 'bangladeshi-payments-mobile' ) . '</p></div>';
        return;
    }

    // 1. Duplicate TrxID Protection
    $enable_trx_blocker = isset( $_POST['bpm_enable_duplicate_trx_blocker'] ) ? 'yes' : 'no';
    update_option( 'bpm_enable_duplicate_trx_blocker', $enable_trx_blocker );

    // 2. Advance COD Settings
    $enable_advance_cod = isset( $_POST['bpm_enable_advance_cod'] ) ? 'yes' : 'no';
    $cod_fee_inside     = isset( $_POST['bpm_cod_fee_inside'] ) ? absint( $_POST['bpm_cod_fee_inside'] ) : 70;
    $cod_fee_outside    = isset( $_POST['bpm_cod_fee_outside'] ) ? absint( $_POST['bpm_cod_fee_outside'] ) : 130;
    $cod_min_amount     = isset( $_POST['bpm_cod_min_amount'] ) ? absint( $_POST['bpm_cod_min_amount'] ) : 0;

    update_option( 'bpm_enable_advance_cod', $enable_advance_cod );
    update_option( 'bpm_cod_fee_inside', $cod_fee_inside );
    update_option( 'bpm_cod_fee_outside', $cod_fee_outside );
    update_option( 'bpm_cod_min_amount', $cod_min_amount );

    // 3. Blacklists (Phone & IP)
    $blacklisted_numbers = isset( $_POST['bpm_blacklisted_numbers'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bpm_blacklisted_numbers'] ) ) : '';
    $blacklisted_ips     = isset( $_POST['bpm_blacklisted_ips'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bpm_blacklisted_ips'] ) ) : '';

    update_option( 'bpm_blacklisted_numbers', $blacklisted_numbers );
    update_option( 'bpm_blacklisted_ips', $blacklisted_ips );

    // 4. Rate Limiting & Velocity Controls
    $max_orders_per_phone = isset( $_POST['bpm_max_orders_per_phone'] ) ? absint( $_POST['bpm_max_orders_per_phone'] ) : 3;
    $rate_limit_hours     = isset( $_POST['bpm_rate_limit_hours'] ) ? absint( $_POST['bpm_rate_limit_hours'] ) : 24;

    update_option( 'bpm_max_orders_per_phone', $max_orders_per_phone );
    update_option( 'bpm_rate_limit_hours', $rate_limit_hours );

    echo '<div class="notice notice-success is-dismissible bpm-notice-success"><p><strong>' . esc_html__( 'Fraud Shield & Advance COD settings saved successfully!', 'bangladeshi-payments-mobile' ) . '</strong></p></div>';
}

/**
 * Render Fraud Shield & Advance COD Settings Tab.
 */
function bpm_render_security_settings_tab() {
    bpm_save_security_settings();

    // Fetch saved values
    $enable_trx_blocker   = get_option( 'bpm_enable_duplicate_trx_blocker', 'yes' );
    $enable_advance_cod   = get_option( 'bpm_enable_advance_cod', 'no' );
    $cod_fee_inside       = get_option( 'bpm_cod_fee_inside', 70 );
    $cod_fee_outside      = get_option( 'bpm_cod_fee_outside', 130 );
    $cod_min_amount       = get_option( 'bpm_cod_min_amount', 0 );

    $blacklisted_numbers  = get_option( 'bpm_blacklisted_numbers', '' );
    $blacklisted_ips      = get_option( 'bpm_blacklisted_ips', '' );

    $max_orders_per_phone = get_option( 'bpm_max_orders_per_phone', 3 );
    $rate_limit_hours     = get_option( 'bpm_rate_limit_hours', 24 );
    ?>

    <div class="bpm-security-shell">
        <form method="POST">
            <?php wp_nonce_field( 'bpm_save_security_settings_action', 'bpm_security_settings_nonce' ); ?>

            <div class="bpm-settings-2col">
                <!-- Left Column: TrxID & Blacklists -->
                <div>
                    <!-- Section 1: Anti-Fraud TrxID Guard -->
                    <div class="bpm-setting-card">
                        <div class="bpm-card-header">
                            <div class="bpm-icon-badge bpm-icon-red">
                                <svg class="bpm-header-svg" viewBox="0 0 24 24">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    <path d="M9 12l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2>1. Anti-Fraud TrxID Protection</h2>
                                <p>Prevent fake or reused mobile wallet transaction IDs</p>
                            </div>
                        </div>

                        <div class="bpm-toggle-row">
                            <div>
                                <strong class="bpm-toggle-title"><?php esc_html_e( 'Block Duplicate Transaction IDs', 'bangladeshi-payments-mobile' ); ?></strong>
                                <p class="bpm-desc">
                                    <?php esc_html_e( 'Automatically verifies TrxID uniqueness across all WooCommerce orders and rejects duplicate entries in real time.', 'bangladeshi-payments-mobile' ); ?>
                                </p>
                                <span class="bpm-shield-badge">
                                    <svg class="bpm-badge-svg" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    <?php esc_html_e( 'Protects bKash, Nagad, Rocket & Upay', 'bangladeshi-payments-mobile' ); ?>
                                </span>
                            </div>
                            <label class="bpm-switch">
                                <input type="checkbox" name="bpm_enable_duplicate_trx_blocker" value="yes" <?php checked( 'yes', $enable_trx_blocker ); ?>>
                                <span class="bpm-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Section 2: Blacklist Controls (Phone & IP) -->
                    <div class="bpm-setting-card">
                        <div class="bpm-card-header">
                            <div class="bpm-icon-badge bpm-icon-red">
                                <svg class="bpm-header-svg" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                </svg>
                            </div>
                            <div>
                                <h2>2. Blacklist Controls (Phone & IP)</h2>
                                <p>Block abusive customers and competitors before checkout</p>
                            </div>
                        </div>

                        <div class="bpm-form-group">
                            <label class="bpm-label" for="bpm_blacklisted_numbers">
                                <?php esc_html_e( 'Blacklisted Phone Numbers (Comma Separated)', 'bangladeshi-payments-mobile' ); ?>
                            </label>
                            <textarea
                                name="bpm_blacklisted_numbers"
                                id="bpm_blacklisted_numbers"
                                rows="3"
                                class="bpm-textarea-field"
                                placeholder="01700000000, 01800000000"
                            ><?php echo esc_textarea( $blacklisted_numbers ); ?></textarea>
                            <p class="bpm-desc">
                                <?php esc_html_e( 'List phone numbers of serial cancelers or fake buyers. Matching numbers will be blocked at checkout.', 'bangladeshi-payments-mobile' ); ?>
                            </p>
                        </div>

                        <div class="bpm-form-group bpm-form-group-padded">
                            <label class="bpm-label" for="bpm_blacklisted_ips">
                                <?php esc_html_e( 'Blacklisted IP Addresses (Comma Separated)', 'bangladeshi-payments-mobile' ); ?>
                            </label>
                            <textarea
                                name="bpm_blacklisted_ips"
                                id="bpm_blacklisted_ips"
                                rows="2"
                                class="bpm-textarea-field"
                                placeholder="103.100.10.1, 192.168.1.1"
                            ><?php echo esc_textarea( $blacklisted_ips ); ?></textarea>
                            <p class="bpm-desc">
                                <?php esc_html_e( 'Blocks bad actors attempting automated or spam orders from specific IP addresses.', 'bangladeshi-payments-mobile' ); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Advance COD & Rate Limiting -->
                <div>
                    <!-- Section 3: Advance COD Delivery Prepayment -->
                    <div class="bpm-setting-card">
                        <div class="bpm-card-header">
                            <div class="bpm-icon-badge bpm-icon-emerald">
                                <svg class="bpm-header-svg" viewBox="0 0 24 24">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <div>
                                <h2>3. Advance COD (Delivery Prepayment)</h2>
                                <p>Reduce parcel returns by collecting courier fees upfront</p>
                            </div>
                        </div>

                        <div class="bpm-toggle-row">
                            <div>
                                <strong class="bpm-toggle-title"><?php esc_html_e( 'Enable Advance Delivery Charge for COD', 'bangladeshi-payments-mobile' ); ?></strong>
                                <p class="bpm-desc">
                                    <?php esc_html_e( 'Requires customers to prepay delivery fees via mobile wallet before confirming Cash on Delivery.', 'bangladeshi-payments-mobile' ); ?>
                                </p>
                            </div>
                            <label class="bpm-switch">
                                <input type="checkbox" id="bpm_cod_toggle" class="bpm-cod-switch" name="bpm_enable_advance_cod" value="yes" <?php checked( 'yes', $enable_advance_cod ); ?>>
                                <span class="bpm-slider"></span>
                            </label>
                        </div>

                        <!-- Dynamic Input Fields Section -->
                        <div id="bpm_cod_fields_wrap" class="bpm-collapsible-box <?php echo 'yes' === $enable_advance_cod ? '' : 'is-hidden'; ?>">
                            <div class="bpm-field-grid-2col">
                                <div class="bpm-form-group bpm-form-group-reset">
                                    <label class="bpm-label" for="bpm_cod_fee_inside"><?php esc_html_e( 'Inside Dhaka (BDT)', 'bangladeshi-payments-mobile' ); ?></label>
                                    <input
                                        type="number"
                                        name="bpm_cod_fee_inside"
                                        id="bpm_cod_fee_inside"
                                        value="<?php echo esc_attr( $cod_fee_inside ); ?>"
                                        class="bpm-input-field"
                                        placeholder="70"
                                    >
                                </div>
                                <div class="bpm-form-group bpm-form-group-reset">
                                    <label class="bpm-label" for="bpm_cod_fee_outside"><?php esc_html_e( 'Outside Dhaka (BDT)', 'bangladeshi-payments-mobile' ); ?></label>
                                    <input
                                        type="number"
                                        name="bpm_cod_fee_outside"
                                        id="bpm_cod_fee_outside"
                                        value="<?php echo esc_attr( $cod_fee_outside ); ?>"
                                        class="bpm-input-field"
                                        placeholder="130"
                                    >
                                </div>
                            </div>

                            <div class="bpm-form-group bpm-form-group-reset">
                                <label class="bpm-label" for="bpm_cod_min_amount"><?php esc_html_e( 'Minimum Cart Total to Require Advance (BDT)', 'bangladeshi-payments-mobile' ); ?></label>
                                <input
                                    type="number"
                                    name="bpm_cod_min_amount"
                                    id="bpm_cod_min_amount"
                                    value="<?php echo esc_attr( $cod_min_amount ); ?>"
                                    class="bpm-input-field"
                                    placeholder="0"
                                >
                                <p class="bpm-desc"><?php esc_html_e( 'Set "0" to require advance on all orders. Example: 1000 requires advance only if cart total exceeds ৳1,000.', 'bangladeshi-payments-mobile' ); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Order Rate Limiting -->
                    <div class="bpm-setting-card">
                        <div class="bpm-card-header">
                            <div class="bpm-icon-badge bpm-icon-blue">
                                <svg class="bpm-header-svg" viewBox="0 0 24 24">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                            </div>
                            <div>
                                <h2>4. Order Velocity & Rate Limiter</h2>
                                <p>Defend against order flooding and repeated bot attempts</p>
                            </div>
                        </div>

                        <div class="bpm-field-grid-rate">
                            <div class="bpm-form-group bpm-form-group-reset">
                                <label class="bpm-label" for="bpm_max_orders_per_phone"><?php esc_html_e( 'Max Orders Per Phone', 'bangladeshi-payments-mobile' ); ?></label>
                                <input
                                    type="number"
                                    name="bpm_max_orders_per_phone"
                                    id="bpm_max_orders_per_phone"
                                    value="<?php echo esc_attr( $max_orders_per_phone ); ?>"
                                    class="bpm-input-field"
                                    min="1"
                                    max="50"
                                >
                            </div>
                            <div class="bpm-form-group bpm-form-group-reset">
                                <label class="bpm-label" for="bpm_rate_limit_hours"><?php esc_html_e( 'Time Window (Hours)', 'bangladeshi-payments-mobile' ); ?></label>
                                <input
                                    type="number"
                                    name="bpm_rate_limit_hours"
                                    id="bpm_rate_limit_hours"
                                    value="<?php echo esc_attr( $rate_limit_hours ); ?>"
                                    class="bpm-input-field"
                                    min="1"
                                    max="168"
                                >
                            </div>
                        </div>
                        <p class="bpm-desc bpm-rate-desc">
                            <?php esc_html_e( 'Limits how many orders can be placed by a single phone number within the specified timeframe (Default: 3 orders in 24 hours).', 'bangladeshi-payments-mobile' ); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bpm-submit-row">
                <button type="submit" class="bpm-save-btn">
                    <svg class="bpm-btn-svg" viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <?php esc_html_e( 'Save Security Settings', 'bangladeshi-payments-mobile' ); ?>
                </button>
            </div>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Smooth Toggle for Advance COD Fee Inputs
        $('#bpm_cod_toggle').on('change', function() {
            var $wrap = $('#bpm_cod_fields_wrap');
            if ($(this).is(':checked')) {
                $wrap.removeClass('is-hidden').hide().slideDown(200);
            } else {
                $wrap.slideUp(200, function() {
                    $(this).addClass('is-hidden');
                });
            }
        });
    });
    </script>
    <?php
}

/**
 * ========================================================================
 * FRONTEND: SECURITY & FRAUD SHIELD ENFORCEMENT HOOKS
 * ========================================================================
 */

/**
 * Helper: Verify checkout process nonce
 *
 * @return bool
 */
function bpm_verify_security_checkout_nonce() {
    $nonce = isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) : '';
    return ! empty( $nonce ) && wp_verify_nonce( $nonce, 'woocommerce-process_checkout' );
}

/**
 * Helper: Extract accurate client IP (handling Cloudflare & Proxies)
 */
function bpm_get_client_ip_address() {
    $ip_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
    foreach ( $ip_keys as $key ) {
        if ( ! empty( $_SERVER[ $key ] ) ) {
            $ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
            $ip      = trim( $ip_list[0] );
            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Helper: Clean phone numbers for exact comparisons
 */
function bpm_clean_phone_number( $phone ) {
    $bangla  = array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' );
    $english = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
    $phone   = str_replace( $bangla, $english, $phone );
    $clean   = preg_replace( '/[^0-9]/', '', $phone );
    if ( strpos( $clean, '880' ) === 0 ) {
        $clean = substr( $clean, 2 );
    }
    return $clean;
}

/**
 * 1. CHECKOUT VALIDATION: Blacklists & Velocity Limiters
 */
add_action( 'woocommerce_checkout_process', 'bpm_enforce_blacklist_and_velocity_rules' );
function bpm_enforce_blacklist_and_velocity_rules() {
    // Nonce verification before accessing $_POST data
    if ( ! bpm_verify_security_checkout_nonce() ) {
        return;
    }

    if ( ! isset( $_POST['woocommerce-process-checkout-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' ) ) {
        return;
    }

    // 1A. Phone & IP Blacklist Check
    $phone       = isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) : '';
    $clean_phone = bpm_clean_phone_number( $phone );
    $client_ip   = bpm_get_client_ip_address();

    // Check Blacklisted Numbers
    $raw_blocked_numbers = get_option( 'bpm_blacklisted_numbers', '' );
    if ( ! empty( $raw_blocked_numbers ) && ! empty( $clean_phone ) ) {
        $blocked_list = array_map( 'trim', explode( ',', $raw_blocked_numbers ) );
        foreach ( $blocked_list as $blocked_num ) {
            $clean_blocked = bpm_clean_phone_number( $blocked_num );
            if ( ! empty( $clean_blocked ) && $clean_phone === $clean_blocked ) {
                wc_add_notice(
                    __( 'দুঃখিত, আপনার মোবাইল নম্বরটি ব্লক করা হয়েছে। সহায়তার জন্য সাপোর্টে যোগাযোগ করুন।', 'bangladeshi-payments-mobile' ),
                    'error'
                );
                return;
            }
        }
    }

    // Check Blacklisted IPs
    $raw_blocked_ips = get_option( 'bpm_blacklisted_ips', '' );
    if ( ! empty( $raw_blocked_ips ) ) {
        $blocked_ips = array_map( 'trim', explode( ',', $raw_blocked_ips ) );
        if ( in_array( $client_ip, $blocked_ips, true ) ) {
            wc_add_notice(
                __( 'দুঃখিত, আপনার আইপি অ্যাড্রেস থেকে নতুন অর্ডার দেওয়া সাময়িকভাবে স্থগিত রয়েছে।', 'bangladeshi-payments-mobile' ),
                'error'
            );
            return;
        }
    }

    // 1B. Order Velocity / Rate Limiting per Phone
    $max_orders = absint( get_option( 'bpm_max_orders_per_phone', 3 ) );
    $time_hours = absint( get_option( 'bpm_rate_limit_hours', 24 ) );

    if ( $max_orders > 0 && $time_hours > 0 && ! empty( $clean_phone ) ) {
        $time_cutoff = time() - ( $time_hours * HOUR_IN_SECONDS );

        // Search orders in recent window via cached WC order query
        $recent_orders = wc_get_orders( array(
            'limit'        => $max_orders + 1,
            'date_created' => '>=' . $time_cutoff,
            'billing_phone'=> $clean_phone,
            'return'       => 'ids',
        ) );

        if ( count( $recent_orders ) >= $max_orders ) {
            wc_add_notice(
                sprintf(
                    /* translators: 1: time window hours, 2: max orders count */
                    __( 'আপনি বিগত %1$d ঘণ্টার মধ্যে সর্বোচ্চ %2$d টি অর্ডার সম্পন্ন করেছেন। অতিরিক্ত অর্ডারের জন্য অপেক্ষা করুন বা আমাদের সাথে যোগাযোগ করুন।', 'bangladeshi-payments-mobile' ),
                    $time_hours,
                    $max_orders
                ),
                'error'
            );
        }
    }
}

/**
 * 2. DUPLICATE TRANSACTION ID BLOCKER (bKash, Nagad, Rocket, Upay)
 */
add_action( 'woocommerce_checkout_process', 'bpm_enforce_duplicate_trx_id_blocker' );
function bpm_enforce_duplicate_trx_id_blocker() {
    // Nonce verification before accessing $_POST data
    if ( ! bpm_verify_security_checkout_nonce() ) {
        return;
    }

    if ( ! isset( $_POST['woocommerce-process-checkout-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' ) ) {
        return;
    }

    if ( 'yes' !== get_option( 'bpm_enable_duplicate_trx_blocker', 'yes' ) ) {
        return;
    }

    $payment_method   = isset( $_POST['payment_method'] ) ? sanitize_key( wp_unslash( $_POST['payment_method'] ) ) : '';
    $allowed_gateways = array( 'bkash', 'nagad', 'rocket', 'upay' );

    if ( ! in_array( $payment_method, $allowed_gateways, true ) ) {
        return;
    }

    // Look for submitted transaction ID field
    $trx_field_key = $payment_method . '_trx_id';
    $raw_trx_id    = isset( $_POST[ $trx_field_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $trx_field_key ] ) ) : '';

    if ( empty( $raw_trx_id ) ) {
        return; // Empty check handled by gateway itself
    }

    $clean_trx_id = strtoupper( trim( $raw_trx_id ) );
    $meta_key     = '_' . $payment_method . '_transaction_id';

    // Native cached HPOS & legacy-compatible query
    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
    $existing_orders = wc_get_orders( array(
        'limit'        => 1,
        'meta_key'     => $meta_key,
        'meta_value'   => $clean_trx_id,
        'meta_compare' => '=',
        'return'       => 'ids',
    ) );

    if ( ! empty( $existing_orders ) ) {
        wc_add_notice(
            sprintf(
                /* translators: %s: Transaction ID */
                __( 'ত্রুটি: ট্রানজেকশন আইডি "%s" ইতিপূর্বে অন্য একটি অর্ডারে ব্যবহার করা হয়েছে। অনুগ্রহ করে সঠিক TrxID দিন।', 'bangladeshi-payments-mobile' ),
                esc_html( $clean_trx_id )
            ),
            'error'
        );
    }
}

/**
 * 3. ADVANCE COD ENGINE: Require advance delivery charges
 */
add_action( 'woocommerce_checkout_process', 'bpm_enforce_advance_cod_rules' );
function bpm_enforce_advance_cod_rules() {
    // Nonce verification before accessing $_POST data
    if ( ! bpm_verify_security_checkout_nonce() ) {
        return;
    }

    if ( ! isset( $_POST['woocommerce-process-checkout-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' ) ) {
        return;
    }

    if ( 'yes' !== get_option( 'bpm_enable_advance_cod', 'no' ) ) {
        return;
    }

    $payment_method = isset( $_POST['payment_method'] ) ? sanitize_key( wp_unslash( $_POST['payment_method'] ) ) : '';
    if ( 'cod' !== $payment_method ) {
        return;
    }

    // Check cart minimum threshold
    $min_threshold = absint( get_option( 'bpm_cod_min_amount', 0 ) );
    $cart_total    = (float) WC()->cart->get_subtotal();

    if ( $min_threshold > 0 && $cart_total < $min_threshold ) {
        return; // Below threshold; pure COD allowed
    }

    // Determine Inside/Outside Dhaka rate
    $district    = isset( $_POST['billing_state'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['billing_state'] ) ) ) : '';
    $fee_inside  = absint( get_option( 'bpm_cod_fee_inside', 70 ) );
    $fee_outside = absint( get_option( 'bpm_cod_fee_outside', 130 ) );

    $required_advance = ( 'DHAKA' === $district ) ? $fee_inside : $fee_outside;

    // Reject pure COD checkout without advance payment step
    wc_add_notice(
        sprintf(
            /* translators: %s: Advance delivery amount formatted with currency */
            __( 'ক্যাশ অন ডেলিভারিতে অর্ডার নিশ্চিত করতে অগ্রিম ডেলিভারি চার্জ ৳%s পরিশোধ করতে হবে। অনুগ্রহ করে বিকাশ, নগদ বা রকেট সিলেক্ট করে ডেলিভারি চার্জ পরিশোধ করুন।', 'bangladeshi-payments-mobile' ),
            esc_html( (string) $required_advance )
        ),
        'error'
    );
}

/**
 * Record Advance COD delivery notice inside COD payment gateway description
 */
add_filter( 'woocommerce_gateway_description', 'bpm_append_advance_cod_gateway_notice', 20, 2 );
function bpm_append_advance_cod_gateway_notice( $description, $gateway_id ) {
    if ( 'cod' !== $gateway_id || 'yes' !== get_option( 'bpm_enable_advance_cod', 'no' ) ) {
        return $description;
    }

    $fee_inside  = absint( get_option( 'bpm_cod_fee_inside', 70 ) );
    $fee_outside = absint( get_option( 'bpm_cod_fee_outside', 130 ) );
    $min_amount  = absint( get_option( 'bpm_cod_min_amount', 0 ) );

    $notice = '<div style="margin-top:10px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#991b1b;font-size:12.5px;line-height:1.4;">';
    $notice .= '<strong>⚠️ ডেলিভারি চার্জ অগ্রিম নোটিশ:</strong><br>';
    if ( $min_amount > 0 ) {
        /* translators: %d: Minimum order threshold amount */
        $notice .= sprintf( __( '৳%d এর বেশি অর্ডারে ', 'bangladeshi-payments-mobile' ), $min_amount );
    }
    /* translators: 1: Delivery charge inside Dhaka, 2: Delivery charge outside Dhaka */
    $notice .= sprintf(
        __( 'ঢাকার ভেতর ডেলিভারি চার্জ ৳%1$d এবং ঢাকার বাইরে ৳%2$d অগ্রিম প্রদেয়।', 'bangladeshi-payments-mobile' ),
        $fee_inside,
        $fee_outside
    );
    $notice .= '</div>';

    return $description . $notice;
}