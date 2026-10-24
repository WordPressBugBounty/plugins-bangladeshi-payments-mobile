<?php
/**
 * Unified Bangladeshi Mobile Payment Gateways.
 * Combines bKash, Nagad, Rocket, and Upay into a shared architecture.
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Base Gateway Class providing shared functionality for all 4 wallets.
 */
abstract class BPM_Gateway_Base extends WC_Payment_Gateway {

    public $account_type;
    public $account_number;
    public $wallet_charge;
    public $apply_wallet_charge;
    public $enable_qr;
    public $instructions;

    // Subclasses declare these unique brand identifiers
    protected $brand_name;
    protected $brand_color;
    protected $phone_pattern   = '/^01[3-9][0-9]{8}$/'; // Default 11-digit Bangladeshi mobile
    protected $phone_maxlength = 11;
    protected $default_charge  = 1.85;

    public function __construct() {
        $this->has_fields = true;

        /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
        $this->method_title = sprintf( __( '%s Payment', 'bangladeshi-payments-mobile' ), $this->brand_name );

        /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
        $this->method_description = sprintf( __( 'Accept manual %s payments by requiring phone number and transaction ID.', 'bangladeshi-payments-mobile' ), $this->brand_name );

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option( 'title', $this->brand_name );

        /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
        $this->description = $this->get_option( 'description', sprintf( __( 'Pay with %s. Enter your phone number and transaction ID below.', 'bangladeshi-payments-mobile' ), $this->brand_name ) );

        /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
        $this->instructions = $this->get_option( 'instructions', sprintf( __( 'Thank you for your payment. We will verify your %s transaction and process your order shortly.', 'bangladeshi-payments-mobile' ), $this->brand_name ) );

        $this->account_type        = $this->get_option( 'account_type', 'personal' );
        $this->account_number      = $this->get_option( 'account_number', '' );
        $this->apply_wallet_charge = 'yes' === $this->get_option( 'apply_charge', 'no' );
        $this->wallet_charge       = (float) $this->get_option( 'wallet_charge', $this->default_charge );
        $this->enable_qr           = 'yes' === $this->get_option( 'enable_qr', 'yes' );

        $this->icon = apply_filters( "woocommerce_{$this->id}_icon", BPM_PLUGIN_URL . "assets/img/{$this->id}.png" );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'bangladeshi-payments-mobile' ),
                'type'    => 'checkbox',
                /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
                'label'   => sprintf( __( 'Enable %s Payment Gateway', 'bangladeshi-payments-mobile' ), $this->brand_name ),
                'default' => 'no',
            ),
            'title' => array(
                'title'       => __( 'Title', 'bangladeshi-payments-mobile' ),
                'type'        => 'text',
                'description' => __( 'Controls the title displayed on checkout.', 'bangladeshi-payments-mobile' ),
                'default'     => $this->brand_name,
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'bangladeshi-payments-mobile' ),
                'type'        => 'textarea',
                'description' => __( 'Instructions that will be added to the checkout page.', 'bangladeshi-payments-mobile' ),
                /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
                'default'     => sprintf( __( 'Please complete your %s payment first, then submit your transaction details below.', 'bangladeshi-payments-mobile' ), $this->brand_name ),
            ),
            'instructions' => array(
                'title'       => __( 'Instructions', 'bangladeshi-payments-mobile' ),
                'type'        => 'textarea',
                'description' => __( 'Instructions that will be added to the thank you page and customer order emails.', 'bangladeshi-payments-mobile' ),
                /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
                'default'     => sprintf( __( 'Thank you for your payment. We will verify your %s transaction and process your order shortly.', 'bangladeshi-payments-mobile' ), $this->brand_name ),
            ),
            'account_type' => array(
                'title'   => __( 'Account Type', 'bangladeshi-payments-mobile' ),
                'type'    => 'select',
                'options' => array(
                    'personal' => __( 'Personal (Send Money)', 'bangladeshi-payments-mobile' ),
                    'agent'    => __( 'Agent (Cash Out)', 'bangladeshi-payments-mobile' ),
                    'merchant' => __( 'Merchant (Payment)', 'bangladeshi-payments-mobile' ),
                ),
                'default' => 'personal',
            ),
            'account_number' => array(
                /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
                'title'       => sprintf( __( '%s Account Number', 'bangladeshi-payments-mobile' ), $this->brand_name ),
                'type'        => 'text',
                /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
                'description' => sprintf( __( 'Enter your %s number where customers should send funds.', 'bangladeshi-payments-mobile' ), $this->brand_name ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'apply_charge' => array(
                /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
                'title'   => sprintf( __( '%s Fee', 'bangladeshi-payments-mobile' ), $this->brand_name ),
                'type'    => 'checkbox',
                /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
                'label'   => sprintf( __( 'Inform customer about the %s transfer fee on checkout', 'bangladeshi-payments-mobile' ), $this->brand_name ),
                'default' => 'no',
            ),
            'wallet_charge' => array(
                /* translators: %s: Brand payment gateway name (e.g. bKash, Nagad) */
                'title'             => sprintf( __( '%s Fee (%%)', 'bangladeshi-payments-mobile' ), $this->brand_name ),
                'type'              => 'number',
                'default'           => (string) $this->default_charge,
                'custom_attributes' => array(
                    'step' => '0.01',
                    'min'  => '0',
                ),
            ),
            'enable_qr' => array(
                'title'   => __( 'QR Code Display', 'bangladeshi-payments-mobile' ),
                'type'    => 'checkbox',
                'label'   => __( 'Display an auto-generated offline SVG QR code during checkout', 'bangladeshi-payments-mobile' ),
                'default' => 'yes',
            ),
        );
    }

    public function calculate_fee_amount() {
        if ( ! $this->apply_wallet_charge || ! WC()->cart ) {
            return 0.0;
        }

        $cart_total = (float) WC()->cart->get_total( 'edit' );
        return ( $cart_total * $this->wallet_charge ) / 100;
    }

    public function payment_fields() {
        if ( $this->description ) {
            echo wp_kses_post( wpautop( wptexturize( $this->description ) ) );
        }

        $fee_amount  = $this->calculate_fee_amount();
        $cart_total  = WC()->cart ? (float) WC()->cart->get_total( 'edit' ) : 0.0;
        $grand_total = $cart_total + $fee_amount;
        $phone_field = "{$this->id}_phone";
        $trx_field   = "{$this->id}_transaction_id";
        ?>
        <fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-form" class="wc-payment-form" style="background: transparent; padding: 0; border: none;">
            <?php if ( $this->apply_wallet_charge && $fee_amount > 0 ) : ?>
                <p class="form-row form-row-wide">
                    <?php
                    printf(
                        /* translators: 1: Total amount in BDT, 2: Fee amount in BDT */
                        esc_html__( 'Please send a total of %1$s (including %2$s transfer fee).', 'bangladeshi-payments-mobile' ),
                        '<strong>' . wp_kses_post( wc_price( $grand_total ) ) . '</strong>',
                        '<strong>' . wp_kses_post( wc_price( $fee_amount ) ) . '</strong>'
                    );
                    ?>
                </p>
            <?php endif; ?>

            <p class="form-row form-row-wide">
                <label><?php esc_html_e( 'Account Type:', 'bangladeshi-payments-mobile' ); ?></label>
                <strong><?php echo esc_html( ucfirst( $this->account_type ) ); ?></strong>
            </p>

            <p class="form-row form-row-wide">
                <label><?php echo esc_html( $this->brand_name ); ?> <?php esc_html_e( 'Number:', 'bangladeshi-payments-mobile' ); ?></label>
                <span style="font-weight: 600;"><?php echo esc_html( $this->account_number ); ?></span>
            </p>

            <p class="form-row form-row-wide">
                <label for="<?php echo esc_attr( $phone_field ); ?>">
                    <?php
                    /* translators: %s: Brand name (e.g. bKash, Nagad) */
                    printf( esc_html__( 'Your %s Phone Number', 'bangladeshi-payments-mobile' ), esc_html( $this->brand_name ) );
                    ?>
                    <span class="required">*</span>
                </label>
                <input type="tel" class="input-text" name="<?php echo esc_attr( $phone_field ); ?>" id="<?php echo esc_attr( $phone_field ); ?>" placeholder="01XXXXXXXXX" maxlength="<?php echo esc_attr( $this->phone_maxlength ); ?>" required>
            </p>

            <p class="form-row form-row-wide">
                <label for="<?php echo esc_attr( $trx_field ); ?>">
                    <?php
                    /* translators: %s: Brand name (e.g. bKash, Nagad) */
                    printf( esc_html__( '%s Transaction ID (TrxID)', 'bangladeshi-payments-mobile' ), esc_html( $this->brand_name ) );
                    ?>
                    <span class="required">*</span>
                </label>
                <input type="text" class="input-text" name="<?php echo esc_attr( $trx_field ); ?>" id="<?php echo esc_attr( $trx_field ); ?>" placeholder="<?php esc_attr_e( 'Transaction ID', 'bangladeshi-payments-mobile' ); ?>" maxlength="20" required>
            </p>

            <?php if ( $this->enable_qr && ! empty( $this->account_number ) && function_exists( 'bpm_generate_qr_svg' ) ) : ?>
                <p class="form-row form-row-wide" style="text-align: center; margin-top: 10px;">
                    <?php echo bpm_generate_qr_svg( $this->account_number, 130, $this->brand_color ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <br>
                    <small style="display: block; margin-top: 4px;">
                        <?php
                        /* translators: %s: Brand name (e.g. bKash, Nagad) */
                        printf( esc_html__( 'Scan from %s App to Send Money / Pay', 'bangladeshi-payments-mobile' ), esc_html( $this->brand_name ) );
                        ?>
                    </small>
                </p>
            <?php endif; ?>
        </fieldset>
        <?php
    }

    public function validate_fields() {
        $nonce_value = isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) : '';
        if ( empty( $nonce_value ) || ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
            return false;
        }

        $phone_field = "{$this->id}_phone";
        $trx_field   = "{$this->id}_transaction_id";

        $phone         = isset( $_POST[ $phone_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $phone_field ] ) ) : '';
        $trx_id        = isset( $_POST[ $trx_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $trx_field ] ) ) : '';
        $billing_phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) : '';

        // Normalize Bengali digits (০-৯ to 0-9)
        if ( 'yes' === get_option( 'bpm_enable_bangla_converter', 'yes' ) ) {
            $bangla_digits = array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' );
            $eng_digits    = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
            $phone         = str_replace( $bangla_digits, $eng_digits, $phone );
            $trx_id        = str_replace( $bangla_digits, $eng_digits, $trx_id );
            $billing_phone = str_replace( $bangla_digits, $eng_digits, $billing_phone );
        }

        // Feature: Fraud Phone Blacklist Check
        $blacklist_raw = get_option( 'bpm_blacklisted_numbers', '' );
        if ( ! empty( $blacklist_raw ) ) {
            $blacklisted_array = array_map( 'trim', explode( ',', $blacklist_raw ) );
            $clean_phone       = preg_replace( '/[^0-9]/', '', $billing_phone );
            
            foreach ( $blacklisted_array as $bad_num ) {
                $clean_bad_num = preg_replace( '/[^0-9]/', '', $bad_num );
                if ( ! empty( $clean_bad_num ) && false !== strpos( $clean_phone, $clean_bad_num ) ) {
                    wc_add_notice( __( 'দুঃখিত, আপনার মোবাইল নম্বর থেকে অর্ডার গ্রহণ সাময়িকভাবে বন্ধ আছে। সহায়তার জন্য যোগাযোগ করুন।', 'bangladeshi-payments-mobile' ), 'error' );
                    return false;
                }
            }
        }

        // Validate Phone Number
        if ( empty( $phone ) || ! preg_match( $this->phone_pattern, $phone ) ) {
            /* translators: %s: Brand name (e.g. bKash, Nagad) */
            wc_add_notice( sprintf( __( 'Please enter a valid %s mobile number.', 'bangladeshi-payments-mobile' ), $this->brand_name ), 'error' );
            return false;
        }

        // Validate Transaction ID Format
        if ( empty( $trx_id ) || ! preg_match( '/^[A-Za-z0-9]{6,20}$/', $trx_id ) ) {
            /* translators: %s: Brand name (e.g. bKash, Nagad) */
            wc_add_notice( sprintf( __( 'Please enter a valid alphanumeric %s Transaction ID.', 'bangladeshi-payments-mobile' ), $this->brand_name ), 'error' );
            return false;
        }

        // Feature: Anti-Fraud Duplicate TrxID Blocker (Cached HPOS & Legacy Compatible)
        if ( 'yes' === get_option( 'bpm_enable_duplicate_trx_blocker', 'yes' ) ) {
            $meta_key        = "_{$this->id}_transaction_id";
            $clean_trx_upper = strtoupper( trim( $trx_id ) );

            $existing_orders = wc_get_orders( array(
                'limit'        => 1,
                'meta_key'     => $meta_key,
                'meta_value'   => $clean_trx_upper,
                'meta_compare' => '=',
                'return'       => 'ids',
            ) );

            if ( ! empty( $existing_orders ) ) {
                wc_add_notice( 
                    sprintf( 
                        /* translators: %s: Submitted Transaction ID */
                        __( 'এই Transaction ID (%s) টি অলরেডি অন্য একটি অর্ডারে ব্যবহার করা হয়েছে! সঠিক TrxID লিখুন।', 'bangladeshi-payments-mobile' ), 
                        esc_html( $clean_trx_upper ) 
                    ), 
                    'error' 
                );
                return false;
            }
        }

        return true;
    }

    public function process_payment( $order_id ) {
        $nonce_value = isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) : '';
        if ( empty( $nonce_value ) || ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
            return array( 'result' => 'fail', 'redirect' => '' );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return array( 'result' => 'fail', 'redirect' => '' );
        }

        $phone_field = "{$this->id}_phone";
        $trx_field   = "{$this->id}_transaction_id";

        $phone  = isset( $_POST[ $phone_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $phone_field ] ) ) : '';
        $trx_id = isset( $_POST[ $trx_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $trx_field ] ) ) : '';

        // Normalize Bengali digits before saving
        if ( 'yes' === get_option( 'bpm_enable_bangla_converter', 'yes' ) ) {
            $bangla_digits = array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' );
            $eng_digits    = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
            $phone         = str_replace( $bangla_digits, $eng_digits, $phone );
            $trx_id        = str_replace( $bangla_digits, $eng_digits, $trx_id );
        }

        // HPOS Compatible Meta Storage
        $order->update_meta_data( "_{$this->id}_phone", $phone );
        $order->update_meta_data( "_{$this->id}_transaction_id", strtoupper( $trx_id ) );
        $order->save();

        $order->update_status(
            'on-hold',
            sprintf(
                /* translators: 1: Brand name, 2: Phone number, 3: Transaction ID */
                __( '%1$s payment submitted. Phone: %2$s, TrxID: %3$s. Awaiting verification.', 'bangladeshi-payments-mobile' ),
                $this->brand_name,
                $phone,
                strtoupper( $trx_id )
            )
        );

        wc_reduce_stock_levels( $order->get_id() );
        WC()->cart->empty_cart();

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }

    public function thankyou_page( $order_id ) {
        if ( $this->instructions ) {
            echo '<p class="bpm-thankyou-instructions">' . wp_kses_post( wpautop( wptexturize( $this->instructions ) ) ) . '</p>';
        }
    }

    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $sent_to_admin || $this->id !== $order->get_payment_method() || ! $this->instructions ) {
            return;
        }
        echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) ) . PHP_EOL;
    }
}

/**
 * 1. bKash Gateway Class
 */
class WC_Gateway_bKash extends BPM_Gateway_Base {
    public function __construct() {
        $this->id             = 'bkash';
        $this->brand_name     = 'bKash';
        $this->brand_color    = '#E2136E';
        $this->default_charge = 1.85;
        parent::__construct();
    }
}

/**
 * 2. Nagad Gateway Class
 */
class WC_Gateway_Nagad extends BPM_Gateway_Base {
    public function __construct() {
        $this->id             = 'nagad';
        $this->brand_name     = 'Nagad';
        $this->brand_color    = '#F7941D';
        $this->default_charge = 1.4;
        parent::__construct();
    }
}

/**
 * 3. Rocket Gateway Class (supports 11 or 12 digits)
 */
class WC_Gateway_Rocket extends BPM_Gateway_Base {
    public function __construct() {
        $this->id             = 'rocket';
        $this->brand_name     = 'Rocket';
        $this->brand_color    = '#8C3494';
        $this->default_charge = 1.8;
        $this->phone_pattern   = '/^01[3-9][0-9]{8,9}$/';
        $this->phone_maxlength = 12;
        parent::__construct();
    }
}

/**
 * 4. Upay Gateway Class
 */
class WC_Gateway_Upay extends BPM_Gateway_Base {
    public function __construct() {
        $this->id             = 'upay';
        $this->brand_name     = 'Upay';
        $this->brand_color    = '#004B87';
        $this->default_charge = 1.4;
        parent::__construct();
    }
}

/**
 * Universal Order Admin Meta Box Display (HPOS & Legacy).
 * Handles all 4 gateways dynamically using one hook.
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'bpm_display_unified_admin_order_meta', 10, 1 );
function bpm_display_unified_admin_order_meta( $order ) {
    if ( ! $order instanceof WC_Order ) {
        return;
    }

    $method   = $order->get_payment_method();
    $gateways = array(
        'bkash'  => array( 'title' => 'bKash',  'color' => '#E2136E' ),
        'nagad'  => array( 'title' => 'Nagad',  'color' => '#F7941D' ),
        'rocket' => array( 'title' => 'Rocket', 'color' => '#8C3494' ),
        'upay'   => array( 'title' => 'Upay',   'color' => '#004B87' ),
    );

    if ( ! array_key_exists( $method, $gateways ) ) {
        return;
    }

    $phone  = $order->get_meta( "_{$method}_phone", true );
    $trx_id = $order->get_meta( "_{$method}_transaction_id", true );

    if ( ! $phone && ! $trx_id ) {
        return;
    }

    $brand = $gateways[ $method ];
    ?>
<style>
    .bpm-payment-details-card {
        margin-top: 20px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .bpm-payment-details-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 12px;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }
    .bpm-payment-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .bpm-payment-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .bpm-payment-table tr:last-child td {
        border-bottom: none;
    }
    .bpm-label-cell {
        width: 220px;
        font-weight: 600;
        color: #475569;
    }
    .bpm-value-cell {
        color: #0f172a;
        font-weight: 500;
    }
    .bpm-flex-align {
        align-items: center;
        gap: 8px;
    }
    .bpm-icon-svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }
    .bpm-trx-code {
        padding: 3px 8px;
        border-radius: 6px;
        font-family: monospace;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        border: 1px solid rgba(0,0,0,0.08);
    }
    .bpm-copy-btn {
        cursor: pointer;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 11px;
        color: #475569;
        transition: all 0.2s;
    }
    .bpm-copy-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>

<div class="order_data_column bpm-payment-details-card" style="width: 100%;">
    <div class="bpm-payment-details-title">
        <svg class="bpm-icon-svg" style="color: <?php echo esc_attr( $brand['color'] ); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <span>
            <?php
            /* translators: %s: Brand title name (e.g. bKash, Nagad) */
            printf( esc_html__( '%s Payment Details', 'bangladeshi-payments-mobile' ), esc_html( $brand['title'] ) );
            ?>
        </span>
    </div>

    <table class="bpm-payment-table">
        <tbody>
            <tr>
                <td class="bpm-label-cell">
                    <span class="bpm-flex-align">
                        <?php
                        /* translators: %s: Brand title name (e.g. bKash, Nagad) */
                        printf( esc_html__( '%s No:', 'bangladeshi-payments-mobile' ), esc_html( $brand['title'] ) );
                        ?>
                    </span>
                </td>
                <td class="bpm-value-cell">
                    <?php if ( $phone ) : ?>
                        <span class="bpm-flex-align">
                            <strong id="bpm-phone-val"><?php echo esc_html( $phone ); ?></strong>
                            <button type="button" class="bpm-copy-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js( $phone ); ?>'); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy', 1500);">Copy</button>
                        </span>
                    <?php else : ?>
                        <span style="color: #94a3b8;"><?php esc_html_e( 'N/A', 'bangladeshi-payments-mobile' ); ?></span>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <td class="bpm-label-cell">
                    <span class="bpm-flex-align">
                        <?php esc_html_e( 'Trx ID:', 'bangladeshi-payments-mobile' ); ?>
                    </span>
                </td>
                <td class="bpm-value-cell">
                    <?php if ( $trx_id ) : ?>
                        <span class="bpm-flex-align">
                            <code class="bpm-trx-code" style="color: <?php echo esc_attr( $brand['color'] ); ?>; background-color: <?php echo esc_attr( $brand['color'] . '12' ); ?>;">
                                <?php echo esc_html( $trx_id ); ?>
                            </code>
                            <button type="button" class="bpm-copy-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js( $trx_id ); ?>'); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy', 1500);">Copy</button>
                        </span>
                    <?php else : ?>
                        <span style="color: #94a3b8;"><?php esc_html_e( 'N/A', 'bangladeshi-payments-mobile' ); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>
    <?php
}