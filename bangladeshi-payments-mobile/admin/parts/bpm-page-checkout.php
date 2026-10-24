<?php
/**
 * Checkout, Comprehensive Field Customizer & Cart Engine.
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Master List of All Standard Checkout Fields.
 * All toggles default to disabled ('no') by default.
 *
 * @return array
 */
function bpm_get_all_checkout_field_definitions() {
	return array(
		'first_name'  => array(
			'title'      => __( 'First Name / Full Name', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_first_name',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'নাম (Full Name)',
			'def_ph'     => 'আপনার পুরো নাম লিখুন',
		),
		'last_name'   => array(
			'title'      => __( 'Last Name', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_last_name',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'নামের শেষ অংশ (Last Name)',
			'def_ph'     => '',
		),
		'phone'       => array(
			'title'      => __( 'Phone Number', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_phone',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'মোবাইল নম্বর (Phone)',
			'def_ph'     => '017XXXXXXXX',
		),
		'email'       => array(
			'title'      => __( 'Email Address', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_email',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'ইমেইল অ্যাড্রেস (Email)',
			'def_ph'     => 'example@mail.com',
		),
		'company'     => array(
			'title'      => __( 'Company Name', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_company',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'কোম্পানির নাম (Company)',
			'def_ph'     => 'কোম্পানি বা প্রতিষ্ঠানের নাম (ঐচ্ছিক)',
		),
		'country'     => array(
			'title'      => __( 'Country / Region', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_country',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'দেশ (Country)',
			'def_ph'     => '',
		),
		'address_1'   => array(
			'title'      => __( 'Street Address (Line 1)', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_address_1',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'সম্পূর্ণ ঠিকানা (Full Address)',
			'def_ph'     => 'বাসা/রোড নম্বর, এলাকা ও থানার নাম',
		),
		'address_2'   => array(
			'title'      => __( 'Apartment / Suite (Line 2)', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_address_2',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'ফ্ল্যাট / অ্যাপার্টমেন্ট নম্বর',
			'def_ph'     => 'অ্যাপার্টমেন্ট, স্যুট, ইউনিট ইত্যাদি (ঐচ্ছিক)',
		),
		'city'        => array(
			'title'      => __( 'Town / City', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_city',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'শহর / জেলা (City/District)',
			'def_ph'     => 'আপনার জেলা বা শহরের নাম',
		),
		'state'       => array(
			'title'      => __( 'State / County / Division', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_state',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'বিভাগ / স্টেট (State)',
			'def_ph'     => '',
		),
		'postcode'    => array(
			'title'      => __( 'Postcode / ZIP', 'bangladeshi-payments-mobile' ),
			'section'    => 'billing',
			'wc_key'     => 'billing_postcode',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'পোস্ট কোড (Postcode)',
			'def_ph'     => '1200',
		),
		'order_notes' => array(
			'title'      => __( 'Order Notes / Comments', 'bangladeshi-payments-mobile' ),
			'section'    => 'order',
			'wc_key'     => 'order_comments',
			'def_enable' => 'no',
			'def_req'    => 'no',
			'def_label'  => 'অর্ডার সংক্রান্ত নির্দেশনা (Order Notes)',
			'def_ph'     => 'ডেলিভারি সংক্রান্ত কোনো বিশেষ নির্দেশনা থাকলে লিখুন',
		),
	);
}

/**
 * Handle form save for Checkout & Cart settings.
 *
 * @return array|null
 */
function bpm_save_checkout_settings() {
	if ( ! isset( $_POST['bpm_checkout_settings_nonce'] ) ) {
		return null;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bpm_checkout_settings_nonce'] ) ), 'bpm_save_checkout_settings_action' ) ) {
		return array(
			'type'    => 'error',
			'title'   => __( 'Verification Failed', 'bangladeshi-payments-mobile' ),
			'message' => __( 'Security validation failed. Please refresh the page.', 'bangladeshi-payments-mobile' ),
		);
	}

	if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
		return array(
			'type'    => 'error',
			'title'   => __( 'Access Denied', 'bangladeshi-payments-mobile' ),
			'message' => __( 'Unauthorized user permissions.', 'bangladeshi-payments-mobile' ),
		);
	}

	// 1. Layout & Direct Checkout.
	$enable_2col_layout = isset( $_POST['bpm_enable_2col_checkout'] ) ? 'yes' : 'no';
	$add_to_cart_text   = isset( $_POST['bpm_custom_add_to_cart_text'] ) ? sanitize_text_field( wp_unslash( $_POST['bpm_custom_add_to_cart_text'] ) ) : '';
	$place_order_text   = isset( $_POST['bpm_custom_place_order_text'] ) ? sanitize_text_field( wp_unslash( $_POST['bpm_custom_place_order_text'] ) ) : '';
	$skip_cart_page     = isset( $_POST['bpm_skip_cart_page'] ) ? 'yes' : 'no';

	update_option( 'bpm_enable_2col_checkout', $enable_2col_layout );
	update_option( 'bpm_custom_add_to_cart_text', $add_to_cart_text );
	update_option( 'bpm_custom_place_order_text', $place_order_text );
	update_option( 'bpm_skip_cart_page', $skip_cart_page );

	// 2. Granular Field Customizer Settings for All Fields.
	$field_definitions = bpm_get_all_checkout_field_definitions();

	foreach ( array_keys( $field_definitions ) as $field_key ) {
		$enabled  = isset( $_POST[ "bpm_field_{$field_key}_enable" ] ) ? 'yes' : 'no';
		$required = isset( $_POST[ "bpm_field_{$field_key}_required" ] ) ? 'yes' : 'no';
		$label    = isset( $_POST[ "bpm_field_{$field_key}_label" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "bpm_field_{$field_key}_label" ] ) ) : '';
		$ph       = isset( $_POST[ "bpm_field_{$field_key}_placeholder" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "bpm_field_{$field_key}_placeholder" ] ) ) : '';

		update_option( "bpm_field_{$field_key}_enable", $enabled );
		update_option( "bpm_field_{$field_key}_required", $required );
		update_option( "bpm_field_{$field_key}_label", $label );
		update_option( "bpm_field_{$field_key}_placeholder", $ph );
	}

	// 3. Cart Enhancements.
	$enable_free_shipping_bar = isset( $_POST['bpm_enable_free_shipping_bar'] ) ? 'yes' : 'no';
	$free_shipping_min_amount = isset( $_POST['bpm_free_shipping_min_amount'] ) ? absint( $_POST['bpm_free_shipping_min_amount'] ) : 2000;
	$enable_sticky_cart_btn   = isset( $_POST['bpm_enable_sticky_cart_btn'] ) ? 'yes' : 'no';
	$enable_bangla_converter  = isset( $_POST['bpm_enable_bangla_converter'] ) ? 'yes' : 'no';

	update_option( 'bpm_enable_free_shipping_bar', $enable_free_shipping_bar );
	update_option( 'bpm_free_shipping_min_amount', $free_shipping_min_amount );
	update_option( 'bpm_enable_sticky_cart_btn', $enable_sticky_cart_btn );
	update_option( 'bpm_enable_bangla_converter', $enable_bangla_converter );

	return array(
		'type'    => 'success',
		'title'   => __( 'Settings Saved!', 'bangladeshi-payments-mobile' ),
		'message' => __( 'All checkout fields, layout, and cart options updated successfully.', 'bangladeshi-payments-mobile' ),
	);
}

/**
 * Render the Checkout & Cart Settings Tab.
 */
function bpm_render_checkout_settings_tab() {
	$save_notice = bpm_save_checkout_settings();

	// Default everything disabled ('no' / empty)
	$enable_2col_layout = get_option( 'bpm_enable_2col_checkout', 'no' );
	$add_to_cart_text   = get_option( 'bpm_custom_add_to_cart_text', '' );
	$place_order_text   = get_option( 'bpm_custom_place_order_text', '' );
	$skip_cart_page     = get_option( 'bpm_skip_cart_page', 'no' );

	$enable_free_shipping_bar = get_option( 'bpm_enable_free_shipping_bar', 'no' );
	$free_shipping_min_amount = get_option( 'bpm_free_shipping_min_amount', 2000 );
	$enable_sticky_cart_btn   = get_option( 'bpm_enable_sticky_cart_btn', 'no' );
	$enable_bangla_converter  = get_option( 'bpm_enable_bangla_converter', 'no' );

	$field_definitions = bpm_get_all_checkout_field_definitions();
	?>
	<style>
		:root {
			--bpm-slate-900: #0f172a;
			--bpm-slate-800: #1e293b;
			--bpm-slate-700: #334155;
			--bpm-slate-600: #475569;
			--bpm-slate-400: #94a3b8;
			--bpm-slate-300: #cbd5e1;
			--bpm-slate-200: #e2e8f0;
			--bpm-slate-100: #f1f5f9;
			--bpm-slate-50:  #f8fafc;
			--bpm-blue-600:  #2563eb;
			--bpm-blue-700:  #1d4ed8;
			--bpm-blue-50:   #eff6ff;
			--bpm-blue-200:  #bfdbfe;
			--bpm-emerald-600: #059669;
			--bpm-emerald-50:  #ecfdf5;
			--bpm-amber-600:   #d97706;
			--bpm-amber-50:    #fffbeb;
			--bpm-indigo-600:  #4f46e5;
			--bpm-indigo-50:   #eef2ff;
		}

		/* Top Toast Notification */
		.bpm-top-toast-anchor {
			position: fixed;
			top: 48px;
			right: 32px;
			z-index: 9999999;
			pointer-events: none;
		}

		.bpm-top-toast {
			pointer-events: auto;
			min-width: 320px;
			max-width: 440px;
			background: #ffffff;
			border-radius: 14px;
			padding: 14px 18px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 14px;
			box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(15, 23, 42, 0.05);
			animation: bpmToastIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
			transition: all 0.3s ease;
		}

		@keyframes bpmToastIn {
			0% { opacity: 0; transform: translateY(-16px) scale(0.96); }
			100% { opacity: 1; transform: translateY(0) scale(1); }
		}

		.bpm-toast-success { border-left: 5px solid #10b981; }
		.bpm-toast-error { border-left: 5px solid #ef4444; }

		.bpm-toast-body { display: flex; align-items: center; gap: 12px; }

		.bpm-toast-icon-box {
			width: 34px; height: 34px; border-radius: 10px; display: flex;
			align-items: center; justify-content: center; flex-shrink: 0;
		}

		.bpm-toast-success .bpm-toast-icon-box { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
		.bpm-toast-error .bpm-toast-icon-box { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

		.bpm-toast-icon-box svg { width: 18px; height: 18px; stroke: currentColor; stroke-width: 2.4; fill: none; stroke-linecap: round; stroke-linejoin: round; }
		.bpm-toast-texts { display: flex; flex-direction: column; gap: 2px; }
		.bpm-toast-title { font-size: 13.5px; font-weight: 700; color: #0f172a; line-height: 1.2; }
		.bpm-toast-desc { font-size: 12px; color: #64748b; margin: 0; line-height: 1.35; }
		.bpm-toast-close { background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; border-radius: 6px; display: flex; align-items: center; justify-content: center; }
		.bpm-toast-close:hover { color: #0f172a; background: #f1f5f9; }
		.bpm-toast-close svg { width: 15px; height: 15px; stroke: currentColor; stroke-width: 2.2; fill: none; stroke-linecap: round; stroke-linejoin: round; }

		/* Shell & Layout */
		.bpm-checkout-shell {
			max-width: 1440px;
			margin: 10px 0 35px 0;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			color: var(--bpm-slate-800);
		}

		.bpm-settings-2col {
			display: grid;
			grid-template-columns: 1.2fr 0.8fr;
			gap: 24px;
			align-items: start;
		}

		@media (max-width: 1120px) {
			.bpm-settings-2col { grid-template-columns: 1fr; }
		}

		.bpm-setting-card {
			background: #ffffff;
			border: 1px solid var(--bpm-slate-200);
			border-radius: 14px;
			padding: 24px;
			margin-bottom: 24px;
			box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
			transition: all 0.2s ease;
		}

		.bpm-setting-card:hover {
			border-color: var(--bpm-slate-300);
			box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
		}

		.bpm-card-header {
			display: flex;
			align-items: center;
			gap: 14px;
			margin-bottom: 20px;
			padding-bottom: 16px;
			border-bottom: 1px solid var(--bpm-slate-100);
		}

		.bpm-icon-badge {
			width: 40px;
			height: 40px;
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}

		.bpm-icon-blue { background: var(--bpm-blue-50); color: var(--bpm-blue-600); }
		.bpm-icon-indigo { background: var(--bpm-indigo-50); color: var(--bpm-indigo-600); }
		.bpm-icon-amber { background: var(--bpm-amber-50); color: var(--bpm-amber-600); }
		.bpm-header-svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
		.bpm-card-header h2 { font-size: 15px; font-weight: 700; margin: 0; color: var(--bpm-slate-900); }
		.bpm-card-header p { margin: 3px 0 0; font-size: 12px; color: var(--bpm-slate-600); }

		/* Toggle Rows */
		.bpm-toggle-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 20px;
			padding: 12px 0;
			border-bottom: 1px dashed var(--bpm-slate-200);
		}

		.bpm-toggle-row:last-child { border-bottom: none; }
		.bpm-toggle-title { color: var(--bpm-slate-900); font-size: 13px; font-weight: 700; }
		.bpm-desc { font-size: 12px; color: var(--bpm-slate-600); margin: 3px 0 0; line-height: 1.4; }

		.bpm-switch {
			position: relative;
			display: inline-block;
			width: 44px;
			height: 24px;
			flex-shrink: 0;
		}

		.bpm-switch input { opacity: 0; width: 0; height: 0; }
		.bpm-slider {
			position: absolute;
			cursor: pointer;
			inset: 0;
			background-color: var(--bpm-slate-300);
			transition: .25s ease;
			border-radius: 25px;
		}

		.bpm-slider:before {
			position: absolute;
			content: "";
			height: 18px;
			width: 18px;
			left: 3px;
			bottom: 3px;
			background-color: #ffffff;
			transition: .25s ease;
			border-radius: 50%;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.18);
		}

		.bpm-switch input:checked + .bpm-slider { background-color: var(--bpm-blue-600); }
		.bpm-switch input:checked.bpm-amber-switch + .bpm-slider { background-color: var(--bpm-amber-600); }
		.bpm-switch input:checked + .bpm-slider:before { transform: translateX(20px); }

		/* Inputs & Fields */
		.bpm-form-group { margin-bottom: 16px; }
		.bpm-label { display: block; font-weight: 700; font-size: 11.5px; color: var(--bpm-slate-700); margin-bottom: 6px; text-transform: uppercase; }
		.bpm-input-field, .bpm-textarea-field {
			width: 100%;
			border-radius: 8px !important;
			border: 1px solid var(--bpm-slate-300) !important;
			padding: 8px 12px !important;
			font-size: 13px !important;
			color: var(--bpm-slate-800) !important;
			background: #ffffff !important;
			box-sizing: border-box;
		}

		.bpm-preset-tag {
			display: inline-flex;
			background: var(--bpm-slate-50);
			color: var(--bpm-slate-700);
			padding: 4px 10px;
			border-radius: 6px;
			font-size: 11px;
			font-weight: 600;
			margin-right: 6px;
			margin-top: 6px;
			cursor: pointer;
			border: 1px solid var(--bpm-slate-200);
		}

		.bpm-preset-tag:hover { background: var(--bpm-blue-50); color: var(--bpm-blue-600); border-color: var(--bpm-blue-200); }

		/* Granular Field Customizer Card Rows */
		.bpm-field-row-card {
			background: var(--bpm-slate-50);
			border: 1px solid var(--bpm-slate-200);
			border-radius: 10px;
			padding: 14px 16px;
			margin-bottom: 12px;
		}

		.bpm-field-row-top {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 10px;
		}

		.bpm-field-row-title {
			font-size: 13px;
			font-weight: 700;
			color: var(--bpm-slate-900);
		}

		.bpm-field-row-switches {
			display: flex;
			align-items: center;
			gap: 16px;
		}

		.bpm-mini-switch-label {
			display: flex;
			align-items: center;
			gap: 6px;
			font-size: 11.5px;
			font-weight: 600;
			color: var(--bpm-slate-600);
			cursor: pointer;
		}

		.bpm-field-inputs-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 10px;
		}

		.bpm-field-input-wrap label {
			display: block;
			font-size: 10.5px;
			font-weight: 600;
			color: var(--bpm-slate-500);
			margin-bottom: 4px;
			text-transform: uppercase;
		}

		.bpm-collapsible-box {
			background: var(--bpm-slate-50);
			border: 1px solid var(--bpm-slate-200);
			border-radius: 10px;
			padding: 16px;
			margin-top: 14px;
		}

		.bpm-collapsible-box.is-hidden { display: none; }

		.bpm-save-btn {
			background: linear-gradient(135deg, var(--bpm-blue-600) 0%, var(--bpm-blue-700) 100%) !important;
			border: none !important;
			padding: 0 32px !important;
			font-weight: 700 !important;
			height: 44px !important;
			border-radius: 9px !important;
			color: #ffffff !important;
			font-size: 13.5px !important;
			box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35) !important;
			cursor: pointer !important;
			display: inline-flex !important;
			align-items: center !important;
			gap: 8px !important;
		}

		.bpm-save-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37, 99, 235, 0.45); }
	</style>

	<div class="bpm-checkout-shell">
		<?php if ( ! empty( $save_notice ) ) : ?>
			<!-- Top-Anchored Floating Notification -->
			<div class="bpm-top-toast-anchor">
				<div class="bpm-top-toast bpm-toast-<?php echo esc_attr( $save_notice['type'] ); ?>" id="bpm_save_toast">
					<div class="bpm-toast-body">
						<div class="bpm-toast-icon-box">
							<?php if ( 'success' === $save_notice['type'] ) : ?>
								<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
							<?php else : ?>
								<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
							<?php endif; ?>
						</div>
						<div class="bpm-toast-texts">
							<strong class="bpm-toast-title"><?php echo esc_html( $save_notice['title'] ); ?></strong>
							<p class="bpm-toast-desc"><?php echo esc_html( $save_notice['message'] ); ?></p>
						</div>
					</div>
					<button type="button" class="bpm-toast-close" onclick="document.getElementById('bpm_save_toast').remove();" aria-label="<?php esc_attr_e( 'Close', 'bangladeshi-payments-mobile' ); ?>">
						<svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
					</button>
				</div>
			</div>
		<?php endif; ?>

		<form method="POST">
			<?php wp_nonce_field( 'bpm_save_checkout_settings_action', 'bpm_checkout_settings_nonce' ); ?>

			<div class="bpm-settings-2col">
				<!-- Left Column: Checkout Layout & All Checkout Fields -->
				<div>
					<div class="bpm-setting-card">
						<div class="bpm-card-header">
							<div class="bpm-icon-badge bpm-icon-blue">
								<svg class="bpm-header-svg" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="3" x2="12" y2="21"/></svg>
							</div>
							<div>
								<h2>1. Checkout Layout & Full Field Customizer</h2>
								<p>2-column checkout grid, field visibility, labels, placeholders & requirement controls</p>
							</div>
						</div>

						<!-- 2-Column Grid Toggle -->
						<div class="bpm-toggle-row">
							<div>
								<strong class="bpm-toggle-title"><?php esc_html_e( '2-Column Modern Grid Layout', 'bangladeshi-payments-mobile' ); ?></strong>
								<p class="bpm-desc"><?php esc_html_e( 'Displays Billing Info on the left and sticky Order Summary on the right.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" name="bpm_enable_2col_checkout" value="yes" <?php checked( 'yes', $enable_2col_layout ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<!-- All 12 Fields Customizer List -->
						<div style="margin-top: 22px;">
							<label class="bpm-label" style="margin-bottom: 14px;"><?php esc_html_e( 'All Checkout Fields Configuration', 'bangladeshi-payments-mobile' ); ?></label>

							<?php foreach ( $field_definitions as $key => $conf ) : 
								$val_enable   = get_option( "bpm_field_{$key}_enable", $conf['def_enable'] );
								$val_req      = get_option( "bpm_field_{$key}_required", $conf['def_req'] );
								$val_label    = get_option( "bpm_field_{$key}_label", '' );
								$val_ph       = get_option( "bpm_field_{$key}_placeholder", '' );
								?>
								<div class="bpm-field-row-card">
									<div class="bpm-field-row-top">
										<span class="bpm-field-row-title"><?php echo esc_html( $conf['title'] ); ?></span>
										<div class="bpm-field-row-switches">
											<label class="bpm-mini-switch-label">
												<input type="checkbox" name="bpm_field_<?php echo esc_attr( $key ); ?>_enable" value="yes" <?php checked( 'yes', $val_enable ); ?>>
												<span><?php esc_html_e( 'Show', 'bangladeshi-payments-mobile' ); ?></span>
											</label>
											<label class="bpm-mini-switch-label">
												<input type="checkbox" name="bpm_field_<?php echo esc_attr( $key ); ?>_required" value="yes" <?php checked( 'yes', $val_req ); ?>>
												<span><?php esc_html_e( 'Required', 'bangladeshi-payments-mobile' ); ?></span>
											</label>
										</div>
									</div>
									<div class="bpm-field-inputs-grid">
										<div class="bpm-field-input-wrap">
											<label><?php esc_html_e( 'Custom Label (Optional)', 'bangladeshi-payments-mobile' ); ?></label>
											<input type="text" name="bpm_field_<?php echo esc_attr( $key ); ?>_label" value="<?php echo esc_attr( $val_label ); ?>" class="bpm-input-field" placeholder="<?php echo esc_attr( $conf['def_label'] ); ?>">
										</div>
										<div class="bpm-field-input-wrap">
											<label><?php esc_html_e( 'Custom Placeholder (Optional)', 'bangladeshi-payments-mobile' ); ?></label>
											<input type="text" name="bpm_field_<?php echo esc_attr( $key ); ?>_placeholder" value="<?php echo esc_attr( $val_ph ); ?>" class="bpm-input-field" placeholder="<?php echo esc_attr( $conf['def_ph'] ); ?>">
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- Right Column: Buttons, Direct Checkout & Cart Enhancements -->
				<div>
					<!-- Section 2: Buttons & Direct Checkout -->
					<div class="bpm-setting-card">
						<div class="bpm-card-header">
							<div class="bpm-icon-badge bpm-icon-indigo">
								<svg class="bpm-header-svg" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
							</div>
							<div>
								<h2>2. Buttons & Direct Checkout</h2>
								<p>Custom button labels and 1-click cart bypass</p>
							</div>
						</div>

						<div class="bpm-form-group">
							<label class="bpm-label" for="bpm_custom_add_to_cart_text"><?php esc_html_e( '"Add to Cart" Button Text (Leave blank for theme default)', 'bangladeshi-payments-mobile' ); ?></label>
							<input type="text" name="bpm_custom_add_to_cart_text" id="bpm_custom_add_to_cart_text" value="<?php echo esc_attr( $add_to_cart_text ); ?>" class="bpm-input-field" placeholder="e.g. অর্ডার করুন">
							<div>
								<span class="bpm-preset-tag" data-target="bpm_custom_add_to_cart_text" data-value="অর্ডার করুন">অর্ডার করুন</span>
								<span class="bpm-preset-tag" data-target="bpm_custom_add_to_cart_text" data-value="এখনই কিনুন">এখনই কিনুন</span>
								<span class="bpm-preset-tag" data-target="bpm_custom_add_to_cart_text" data-value="কার্টে যোগ করুন">কার্টে যোগ করুন</span>
							</div>
						</div>

						<div class="bpm-form-group" style="margin-top: 14px;">
							<label class="bpm-label" for="bpm_custom_place_order_text"><?php esc_html_e( '"Place Order" Button Text (Leave blank for theme default)', 'bangladeshi-payments-mobile' ); ?></label>
							<input type="text" name="bpm_custom_place_order_text" id="bpm_custom_place_order_text" value="<?php echo esc_attr( $place_order_text ); ?>" class="bpm-input-field" placeholder="e.g. অর্ডার কনফার্ম করুন">
							<div>
								<span class="bpm-preset-tag" data-target="bpm_custom_place_order_text" data-value="অর্ডার কনফার্ম করুন">অর্ডার কনফার্ম করুন</span>
								<span class="bpm-preset-tag" data-target="bpm_custom_place_order_text" data-value="অর্ডার সম্পন্ন করুন">অর্ডার সম্পন্ন করুন</span>
							</div>
						</div>

						<div class="bpm-toggle-row" style="margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--bpm-slate-200);">
							<div>
								<strong class="bpm-toggle-title"><?php esc_html_e( 'Direct Checkout (Bypass Cart Page)', 'bangladeshi-payments-mobile' ); ?></strong>
								<p class="bpm-desc"><?php esc_html_e( 'Redirects buyer straight to Checkout after clicking Buy button.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" name="bpm_skip_cart_page" value="yes" <?php checked( 'yes', $skip_cart_page ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>
					</div>

					<!-- Section 3: Cart Optimizations -->
					<div class="bpm-setting-card">
						<div class="bpm-card-header">
							<div class="bpm-icon-badge bpm-icon-amber">
								<svg class="bpm-header-svg" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
							</div>
							<div>
								<h2>3. Cart Page Enhancements</h2>
								<p>Free shipping bar & mobile tools</p>
							</div>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<strong class="bpm-toggle-title"><?php esc_html_e( 'Free Shipping Progress Bar', 'bangladeshi-payments-mobile' ); ?></strong>
								<p class="bpm-desc"><?php esc_html_e( 'Shows "Add ৳XXX more for Free Shipping" indicator in cart.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" id="bpm_free_shipping_toggle" class="bpm-amber-switch" name="bpm_enable_free_shipping_bar" value="yes" <?php checked( 'yes', $enable_free_shipping_bar ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div id="bpm_free_shipping_wrap" class="bpm-collapsible-box <?php echo 'yes' === $enable_free_shipping_bar ? '' : 'is-hidden'; ?>">
							<div class="bpm-form-group" style="margin-bottom:0;">
								<label class="bpm-label" for="bpm_free_shipping_min_amount"><?php esc_html_e( 'Free Shipping Target Goal (BDT)', 'bangladeshi-payments-mobile' ); ?></label>
								<input type="number" name="bpm_free_shipping_min_amount" id="bpm_free_shipping_min_amount" value="<?php echo esc_attr( $free_shipping_min_amount ); ?>" class="bpm-input-field" placeholder="2000">
							</div>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<strong class="bpm-toggle-title"><?php esc_html_e( 'Mobile Sticky Checkout Button', 'bangladeshi-payments-mobile' ); ?></strong>
								<p class="bpm-desc"><?php esc_html_e( 'Fixed bottom floating checkout button for mobile visitors.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" class="bpm-amber-switch" name="bpm_enable_sticky_cart_btn" value="yes" <?php checked( 'yes', $enable_sticky_cart_btn ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<strong class="bpm-toggle-title"><?php esc_html_e( 'Bangla Digit Normalizer (০-৯ to 0-9)', 'bangladeshi-payments-mobile' ); ?></strong>
								<p class="bpm-desc"><?php esc_html_e( 'Automatically translates Bengali keyboard digits on checkout submit.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" class="bpm-amber-switch" name="bpm_enable_bangla_converter" value="yes" <?php checked( 'yes', $enable_bangla_converter ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>
					</div>
				</div>
			</div>

			<div style="margin-top:24px;">
				<button type="submit" class="bpm-save-btn">
					<svg style="width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2.5;" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
					<?php esc_html_e( 'Save All Settings', 'bangladeshi-payments-mobile' ); ?>
				</button>
			</div>
		</form>
	</div>

	<script>
	jQuery(document).ready(function($) {
		// Auto-dismiss toast
		var $topToast = $('#bpm_save_toast');
		if ($topToast.length) {
			setTimeout(function() {
				$topToast.css({ 'opacity': '0', 'transform': 'translateY(-16px) scale(0.96)' });
				setTimeout(function() { $topToast.parent().remove(); }, 320);
			}, 4500);
		}

		$('.bpm-preset-tag').on('click', function() {
			var targetId = $(this).data('target');
			$('#' + targetId).val($(this).data('value'));
		});

		$('#bpm_free_shipping_toggle').on('change', function() {
			$('#bpm_free_shipping_wrap').toggleClass('is-hidden', !$(this).is(':checked'));
		});
	});
	</script>
	<?php
}

/**
 * ========================================================================
 * FRONTEND: 2-COLUMN MODERN CHECKOUT LAYOUT
 * ========================================================================
 */
add_action( 'wp_head', 'bpm_enqueue_2column_checkout_styles' );
function bpm_enqueue_2column_checkout_styles() {
	if ( ! is_checkout() || is_order_received_page() ) {
		return;
	}

	if ( 'yes' !== get_option( 'bpm_enable_2col_checkout', 'no' ) ) {
		return;
	}
	?>
	<?php
}

/**
 * ========================================================================
 * FRONTEND: DIRECT CHECKOUT, BUTTONS & COMPREHENSIVE CHECKOUT FIELDS
 * ========================================================================
 */

// 1. Direct Checkout Redirect
add_filter( 'woocommerce_add_to_cart_redirect', function( $url ) {
	if ( 'yes' === get_option( 'bpm_skip_cart_page', 'no' ) ) {
		return wc_get_checkout_url();
	}
	return $url;
});

// 2. Button Labels (Only applies if user customized it)
add_filter( 'woocommerce_product_single_add_to_cart_text', 'bpm_apply_add_to_cart_text', 99 );
add_filter( 'woocommerce_product_add_to_cart_text', 'bpm_apply_add_to_cart_text', 99 );
function bpm_apply_add_to_cart_text( $text ) {
	$custom = get_option( 'bpm_custom_add_to_cart_text', '' );
	return ! empty( $custom ) ? esc_html( $custom ) : $text;
}

add_filter( 'woocommerce_order_button_text', function( $text ) {
	$custom = get_option( 'bpm_custom_place_order_text', '' );
	return ! empty( $custom ) ? esc_html( $custom ) : $text;
}, 99 );

// 3. Dynamic Field Customizer Application for All 12 Fields
add_filter( 'woocommerce_checkout_fields', 'bpm_apply_all_dynamic_checkout_fields', 999 );
function bpm_apply_all_dynamic_checkout_fields( $fields ) {
	$field_definitions = bpm_get_all_checkout_field_definitions();

	foreach ( $field_definitions as $opt_key => $conf ) {
		$section = $conf['section'];
		$f_name  = $conf['wc_key'];

		// Read option with 'no' as default
		$enabled  = get_option( "bpm_field_{$opt_key}_enable", 'no' );
		$required = get_option( "bpm_field_{$opt_key}_required", 'no' );
		$label    = get_option( "bpm_field_{$opt_key}_label", '' );
		$ph       = get_option( "bpm_field_{$opt_key}_placeholder", '' );

		// If user explicitly configured field override
		if ( 'no' === $enabled ) {
			// Field is hidden
			unset( $fields[ $section ][ $f_name ] );
			if ( 'order_notes' === $opt_key ) {
				add_filter( 'woocommerce_enable_order_comments', '__return_false' );
			}
			continue;
		}

		if ( isset( $fields[ $section ][ $f_name ] ) ) {
			$fields[ $section ][ $f_name ]['required'] = ( 'yes' === $required );

			if ( ! empty( $label ) ) {
				$fields[ $section ][ $f_name ]['label'] = esc_html( $label );
			}
			if ( ! empty( $ph ) ) {
				$fields[ $section ][ $f_name ]['placeholder'] = esc_attr( $ph );
			}
		}
	}

	// Auto expand first name full-width when last name is disabled
	if ( 'no' === get_option( 'bpm_field_last_name_enable', 'no' ) && isset( $fields['billing']['billing_first_name'] ) ) {
		$fields['billing']['billing_first_name']['class'] = array( 'form-row-wide' );
	}

	return $fields;
}

// Fallback guest email if email field is disabled
add_action( 'woocommerce_checkout_process', function() {
	if ( ! isset( $_POST['woocommerce-process-checkout-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' ) ) {
		return;
	}

	if ( 'no' === get_option( 'bpm_field_email_enable', 'no' ) && empty( $_POST['billing_email'] ) ) {
		$_POST['billing_email'] = 'guest-' . time() . '@checkout.local';
	}
});

// 4. Bangla Digits Normalizer
add_action( 'woocommerce_checkout_process', 'bpm_normalize_bangla_digits' );
function bpm_normalize_bangla_digits() {
	if ( ! isset( $_POST['woocommerce-process-checkout-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' ) ) {
		return;
	}

	if ( 'yes' !== get_option( 'bpm_enable_bangla_converter', 'no' ) ) {
		return;
	}

	$bangla  = array( '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' );
	$english = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );

	$numeric_fields = array( 'billing_phone', 'billing_postcode', 'billing_address_1', 'billing_address_2' );

	foreach ( $numeric_fields as $f ) {
		if ( isset( $_POST[ $f ] ) ) {
			$_POST[ $f ] = str_replace( $bangla, $english, sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) );
		}
	}
}

// 5. Free Shipping Progress Bar
add_action( 'woocommerce_before_cart_table', 'bpm_render_free_shipping_progress_bar' );
add_action( 'woocommerce_review_order_before_payment', 'bpm_render_free_shipping_progress_bar' );
function bpm_render_free_shipping_progress_bar() {
	if ( 'yes' !== get_option( 'bpm_enable_free_shipping_bar', 'no' ) || ! WC()->cart ) {
		return;
	}

	$min_target = (float) get_option( 'bpm_free_shipping_min_amount', 2000 );
	if ( $min_target <= 0 ) {
		return;
	}

	$subtotal  = (float) WC()->cart->get_subtotal();
	$percent   = min( 100, ( $subtotal / $min_target ) * 100 );
	$remaining = max( 0, $min_target - $subtotal );
	?>
	<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 18px;margin-bottom:18px;">
		<p style="font-size:13px;font-weight:700;color:#0f172a;margin:0 0 8px 0;">
			<?php if ( $remaining > 0 ) : ?>
				🚚 <?php
				/* translators: %s: remaining cart amount formatted with currency */
				printf( esc_html__( 'ফ্রি ডেলিভারি পেতে আরও %s এর পণ্য যুক্ত করুন!', 'bangladeshi-payments-mobile' ), wp_kses_post( wc_price( $remaining ) ) );
				?>
			<?php else : ?>
				🎉 <span style="color:#059669;"><?php esc_html_e( 'অভিনন্দন! আপনি ফ্রি ডেলিভারি পেয়েছেন!', 'bangladeshi-payments-mobile' ); ?></span>
			<?php endif; ?>
		</p>
		<div style="width:100%;height:8px;background:#e2e8f0;border-radius:20px;overflow:hidden;">
			<div style="height:100%;background:linear-gradient(90deg,#10b981,#059669);width:<?php echo esc_attr( $percent ); ?>%;transition:width .3s ease;"></div>
		</div>
	</div>
	<?php
}

// 6. Mobile Sticky Checkout Bar
add_action( 'wp_footer', function() {
	if ( 'yes' !== get_option( 'bpm_enable_sticky_cart_btn', 'no' ) || is_checkout() || is_cart() || is_admin() || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}

	$cart_count = WC()->cart->get_cart_contents_count();
	$cart_total = WC()->cart->get_cart_total();
	$target_url = ( 'yes' === get_option( 'bpm_skip_cart_page', 'no' ) ) ? wc_get_checkout_url() : wc_get_cart_url();
	?>
	<style>
		.bpm-sticky-mobile-bar {
			position: fixed; bottom: 0; left: 0; right: 0;
			background: #0f172a; color: #fff; padding: 12px 18px;
			z-index: 999998; box-shadow: 0 -4px 18px rgba(0,0,0,0.15);
			display: none; justify-content: space-between; align-items: center;
		}
		@media (max-width: 768px) {
			.bpm-sticky-mobile-bar { display: flex; }
			body { padding-bottom: 60px !important; }
		}
	</style>
	<div class="bpm-sticky-mobile-bar">
		<div>
			<div style="font-size:11px;color:#94a3b8;font-weight:600;">
				<?php
				/* translators: %d: Item count in cart */
				printf( esc_html__( '%d টি পণ্য কার্টে আছে', 'bangladeshi-payments-mobile' ), absint( $cart_count ) );
				?>
			</div>
			<div style="font-size:15px;font-weight:800;"><?php echo wp_kses_post( $cart_total ); ?></div>
		</div>
		<a href="<?php echo esc_url( $target_url ); ?>" style="background:#2563eb;color:#fff;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;">
			<?php esc_html_e( 'অর্ডার করুন &rarr;', 'bangladeshi-payments-mobile' ); ?>
		</a>
	</div>
	<?php
});