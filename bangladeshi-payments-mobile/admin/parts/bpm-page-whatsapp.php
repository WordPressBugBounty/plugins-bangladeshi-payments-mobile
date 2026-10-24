<?php
/**
 * WhatsApp Quick Order, Floating Live Chat & Tools Settings Tab.
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle form save for WhatsApp & Tools settings tab.
 * Returns notice array if an action occurred.
 */
function bpm_save_whatsapp_settings() {
	if ( ! isset( $_POST['bpm_whatsapp_settings_nonce'] ) ) {
		return null;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bpm_whatsapp_settings_nonce'] ) ), 'bpm_save_whatsapp_settings_action' ) ) {
		return array(
			'type'    => 'error',
			'title'   => __( 'Verification Failed', 'bangladeshi-payments-mobile' ),
			'message' => __( 'Security check failed. Please refresh and try again.', 'bangladeshi-payments-mobile' ),
		);
	}

	if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
		return array(
			'type'    => 'error',
			'title'   => __( 'Access Denied', 'bangladeshi-payments-mobile' ),
			'message' => __( 'Unauthorized user permissions.', 'bangladeshi-payments-mobile' ),
		);
	}

	// 1. WhatsApp Main Business Number.
	$whatsapp_number = isset( $_POST['bpm_whatsapp_number'] ) ? sanitize_text_field( wp_unslash( $_POST['bpm_whatsapp_number'] ) ) : '';
	$whatsapp_number = preg_replace( '/[^0-9]/', '', $whatsapp_number );

	// 2. Direct Product Order Button & Placement Switches + Custom Styles.
	$enable_direct_order = isset( $_POST['bpm_enable_whatsapp_direct_order'] ) ? 'yes' : 'no';
	$show_on_single      = isset( $_POST['bpm_whatsapp_show_on_single'] ) ? 'yes' : 'no';
	$show_on_archive     = isset( $_POST['bpm_whatsapp_show_on_archive'] ) ? 'yes' : 'no';
	$direct_order_label  = isset( $_POST['bpm_whatsapp_direct_order_label'] ) ? sanitize_text_field( wp_unslash( $_POST['bpm_whatsapp_direct_order_label'] ) ) : '';

	// Preserve line breaks cleanly.
	$raw_direct_msg   = isset( $_POST['bpm_whatsapp_direct_order_msg'] ) ? wp_unslash( $_POST['bpm_whatsapp_direct_order_msg'] ) : '';
	$direct_order_msg = sanitize_textarea_field( str_replace( array( "\r\n", "\r" ), "\n", $raw_direct_msg ) );

	$btn_bg_color    = isset( $_POST['bpm_whatsapp_btn_bg'] ) ? sanitize_hex_color( wp_unslash( $_POST['bpm_whatsapp_btn_bg'] ) ) : '#25D366';
	$btn_hover_color = isset( $_POST['bpm_whatsapp_btn_hover'] ) ? sanitize_hex_color( wp_unslash( $_POST['bpm_whatsapp_btn_hover'] ) ) : '#1ebd5a';
	$btn_text_color  = isset( $_POST['bpm_whatsapp_btn_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['bpm_whatsapp_btn_text_color'] ) ) : '#ffffff';
	$btn_radius      = isset( $_POST['bpm_whatsapp_btn_radius'] ) ? absint( $_POST['bpm_whatsapp_btn_radius'] ) : 8;
	$btn_font_size   = isset( $_POST['bpm_whatsapp_btn_font_size'] ) ? absint( $_POST['bpm_whatsapp_btn_font_size'] ) : 14;
	$btn_padding_y   = isset( $_POST['bpm_whatsapp_btn_padding_y'] ) ? absint( $_POST['bpm_whatsapp_btn_padding_y'] ) : 12;

	// 3. Floating Live Chat Widget.
	$enable_chat_widget = isset( $_POST['bpm_enable_whatsapp_chat_widget'] ) ? 'yes' : 'no';
	$chat_widget_number = isset( $_POST['bpm_whatsapp_chat_widget_number'] ) ? sanitize_text_field( wp_unslash( $_POST['bpm_whatsapp_chat_widget_number'] ) ) : '';

	$raw_chat_msg    = isset( $_POST['bpm_whatsapp_chat_widget_msg'] ) ? wp_unslash( $_POST['bpm_whatsapp_chat_widget_msg'] ) : '';
	$chat_widget_msg = sanitize_textarea_field( str_replace( array( "\r\n", "\r" ), "\n", $raw_chat_msg ) );

	$chat_position    = isset( $_POST['bpm_whatsapp_chat_position'] ) && in_array( $_POST['bpm_whatsapp_chat_position'], array( 'left', 'right' ), true ) ? sanitize_text_field( wp_unslash( $_POST['bpm_whatsapp_chat_position'] ) ) : 'right';
	$chat_device_view = isset( $_POST['bpm_whatsapp_chat_device_view'] ) && in_array( $_POST['bpm_whatsapp_chat_device_view'], array( 'all', 'mobile_only', 'desktop_only' ), true ) ? sanitize_text_field( wp_unslash( $_POST['bpm_whatsapp_chat_device_view'] ) ) : 'all';

	$chat_widget_number = preg_replace( '/[^0-9]/', '', $chat_widget_number );

	// 4. Auto Cancel Timer.
	$enable_autocancel = isset( $_POST['bpm_enable_unpaid_autocancel'] ) ? 'yes' : 'no';
	$cancel_hours      = isset( $_POST['bpm_unpaid_autocancel_hours'] ) ? absint( $_POST['bpm_unpaid_autocancel_hours'] ) : 12;

	update_option( 'bpm_whatsapp_number', $whatsapp_number );

	update_option( 'bpm_enable_whatsapp_direct_order', $enable_direct_order );
	update_option( 'bpm_whatsapp_show_on_single', $show_on_single );
	update_option( 'bpm_whatsapp_show_on_archive', $show_on_archive );
	update_option( 'bpm_whatsapp_direct_order_label', $direct_order_label );
	update_option( 'bpm_whatsapp_direct_order_msg', $direct_order_msg );

	update_option( 'bpm_whatsapp_btn_bg', $btn_bg_color ? $btn_bg_color : '#25D366' );
	update_option( 'bpm_whatsapp_btn_hover', $btn_hover_color ? $btn_hover_color : '#1ebd5a' );
	update_option( 'bpm_whatsapp_btn_text_color', $btn_text_color ? $btn_text_color : '#ffffff' );
	update_option( 'bpm_whatsapp_btn_radius', $btn_radius );
	update_option( 'bpm_whatsapp_btn_font_size', $btn_font_size );
	update_option( 'bpm_whatsapp_btn_padding_y', $btn_padding_y );

	update_option( 'bpm_enable_whatsapp_chat_widget', $enable_chat_widget );
	update_option( 'bpm_whatsapp_chat_widget_number', $chat_widget_number );
	update_option( 'bpm_whatsapp_chat_widget_msg', $chat_widget_msg );
	update_option( 'bpm_whatsapp_chat_position', $chat_position );
	update_option( 'bpm_whatsapp_chat_device_view', $chat_device_view );

	update_option( 'bpm_enable_unpaid_autocancel', $enable_autocancel );
	update_option( 'bpm_unpaid_autocancel_hours', $cancel_hours );

	return array(
		'type'    => 'success',
		'title'   => __( 'Settings Saved!', 'bangladeshi-payments-mobile' ),
		'message' => __( 'Your WhatsApp integration & tools options are now active.', 'bangladeshi-payments-mobile' ),
	);
}

/**
 * Render the WhatsApp & Tools Settings Tab.
 */
function bpm_render_whatsapp_settings_tab() {
	$save_notice = bpm_save_whatsapp_settings();

	$whatsapp_number = get_option( 'bpm_whatsapp_number', '' );

	// Default all switches and checkboxes to 'no'
	$enable_direct_order = get_option( 'bpm_enable_whatsapp_direct_order', 'no' );
	$show_on_single      = get_option( 'bpm_whatsapp_show_on_single', 'no' );
	$show_on_archive     = get_option( 'bpm_whatsapp_show_on_archive', 'no' );
	$direct_order_label  = get_option( 'bpm_whatsapp_direct_order_label', 'হোয়াটসঅ্যাপে সরাসরি অর্ডার করুন' );
	$direct_order_msg    = get_option( 'bpm_whatsapp_direct_order_msg', "হ্যালো, আমি এই প্রোডাক্টটি অর্ডার করতে চাই:\n\nপ্রোডাক্ট: {product_name}\nদাম: {product_price}\nলিঙ্ক: {product_url}\n\nআমার নাম:\nঠিকানা:\nমোবাইল নম্বর:" );

	$btn_bg_color    = get_option( 'bpm_whatsapp_btn_bg', '#25D366' );
	$btn_hover_color = get_option( 'bpm_whatsapp_btn_hover', '#1ebd5a' );
	$btn_text_color  = get_option( 'bpm_whatsapp_btn_text_color', '#ffffff' );
	$btn_radius      = get_option( 'bpm_whatsapp_btn_radius', 8 );
	$btn_font_size   = get_option( 'bpm_whatsapp_btn_font_size', 14 );
	$btn_padding_y   = get_option( 'bpm_whatsapp_btn_padding_y', 12 );

	$enable_chat_widget = get_option( 'bpm_enable_whatsapp_chat_widget', 'no' );
	$chat_widget_number = get_option( 'bpm_whatsapp_chat_widget_number', '' );
	$chat_widget_msg    = get_option( 'bpm_whatsapp_chat_widget_msg', 'হ্যালো, অর্ডার বা পেমেন্ট সম্পর্কে জানতে চাই।' );
	$chat_position      = get_option( 'bpm_whatsapp_chat_position', 'right' );
	$device_view        = get_option( 'bpm_whatsapp_chat_device_view', 'all' );

	$enable_autocancel  = get_option( 'bpm_enable_unpaid_autocancel', 'no' );
	$cancel_hours       = get_option( 'bpm_unpaid_autocancel_hours', 12 );
	?>

	<div class="bpm-tools-shell">
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
			<?php wp_nonce_field( 'bpm_save_whatsapp_settings_action', 'bpm_whatsapp_settings_nonce' ); ?>

			<div class="bpm-settings-2col">
				<!-- Left Column: Business Contact & Quick Order -->
				<div>
					<!-- Section 1: WhatsApp Business Contact -->
					<div class="bpm-setting-card">
						<div class="bpm-card-header">
							<div class="bpm-icon-badge bpm-icon-emerald">
								<svg class="bpm-header-svg" viewBox="0 0 24 24">
									<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
								</svg>
							</div>
							<div>
								<h2>1. WhatsApp Business Number</h2>
								<p>Main contact number for direct product orders and customer chats</p>
							</div>
						</div>

						<div class="bpm-form-group bpm-form-group-reset">
							<label class="bpm-label" for="bpm_whatsapp_number"><?php esc_html_e( 'Main Business Number', 'bangladeshi-payments-mobile' ); ?></label>
							<div class="bpm-prefix-box">
								<span>+880</span>
								<input
									type="text"
									name="bpm_whatsapp_number"
									id="bpm_whatsapp_number"
									value="<?php echo esc_attr( preg_replace( '/^880/', '', $whatsapp_number ) ); ?>"
									placeholder="1700000000"
								>
							</div>
							<p class="bpm-desc"><?php esc_html_e( '10-digit mobile number used for product ordering and store support.', 'bangladeshi-payments-mobile' ); ?></p>
						</div>
					</div>

					<!-- Section 2: Direct Product Order Button with Styling Controls & Live Preview -->
					<div class="bpm-setting-card">
						<div class="bpm-card-header">
							<div class="bpm-icon-badge bpm-icon-blue">
								<svg class="bpm-header-svg" viewBox="0 0 24 24">
									<circle cx="9" cy="21" r="1"></circle>
									<circle cx="20" cy="21" r="1"></circle>
									<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
								</svg>
							</div>
							<div>
								<h2>2. Product Quick Order (WhatsApp)</h2>
								<p>Enable customers to order directly with dynamic details</p>
							</div>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<strong class="bpm-toggle-title"><?php esc_html_e( 'Enable Direct Order Feature', 'bangladeshi-payments-mobile' ); ?></strong>
								<p class="bpm-desc"><?php esc_html_e( 'Master switch to activate WhatsApp direct order buttons on your catalog.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" id="bpm_direct_order_toggle" class="bpm-blue-switch" name="bpm_enable_whatsapp_direct_order" value="yes" <?php checked( 'yes', $enable_direct_order ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div id="bpm_direct_order_wrap" class="bpm-collapsible-box <?php echo 'yes' === $enable_direct_order ? '' : 'is-hidden'; ?>">
							<!-- Live Button Preview -->
							<div class="bpm-preview-pane">
								<span class="bpm-nested-heading" style="margin-bottom: 12px;"><?php esc_html_e( 'Live Button Preview', 'bangladeshi-payments-mobile' ); ?></span>
								<div
									id="bpm_button_live_preview"
									class="bpm-preview-btn-mock"
									style="background-color: <?php echo esc_attr( $btn_bg_color ); ?>; color: <?php echo esc_attr( $btn_text_color ); ?>; border-radius: <?php echo esc_attr( $btn_radius ); ?>px; font-size: <?php echo esc_attr( $btn_font_size ); ?>px; padding: <?php echo esc_attr( $btn_padding_y ); ?>px 20px;"
								>
									<svg style="width: 18px; height: 18px; fill: currentColor; flex-shrink: 0;" viewBox="0 0 448 512">
										<path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
									</svg>
									<span id="bpm_preview_text"><?php echo esc_html( $direct_order_label ); ?></span>
								</div>
							</div>

							<!-- Button Style Controls -->
							<div class="bpm-nested-box">
								<span class="bpm-nested-heading"><?php esc_html_e( 'Button Style & Theme Controls', 'bangladeshi-payments-mobile' ); ?></span>
								
								<div class="bpm-style-grid">
									<div class="bpm-form-group">
										<label class="bpm-label"><?php esc_html_e( 'Background Color', 'bangladeshi-payments-mobile' ); ?></label>
										<div class="bpm-color-picker-wrap">
											<input type="color" id="bpm_color_bg" value="<?php echo esc_attr( $btn_bg_color ); ?>">
											<input type="text" name="bpm_whatsapp_btn_bg" id="bpm_input_bg" value="<?php echo esc_attr( $btn_bg_color ); ?>">
										</div>
									</div>

									<div class="bpm-form-group">
										<label class="bpm-label"><?php esc_html_e( 'Hover Color', 'bangladeshi-payments-mobile' ); ?></label>
										<div class="bpm-color-picker-wrap">
											<input type="color" id="bpm_color_hover" value="<?php echo esc_attr( $btn_hover_color ); ?>">
											<input type="text" name="bpm_whatsapp_btn_hover" id="bpm_input_hover" value="<?php echo esc_attr( $btn_hover_color ); ?>">
										</div>
									</div>

									<div class="bpm-form-group">
										<label class="bpm-label"><?php esc_html_e( 'Text & Icon Color', 'bangladeshi-payments-mobile' ); ?></label>
										<div class="bpm-color-picker-wrap">
											<input type="color" id="bpm_color_text" value="<?php echo esc_attr( $btn_text_color ); ?>">
											<input type="text" name="bpm_whatsapp_btn_text_color" id="bpm_input_text" value="<?php echo esc_attr( $btn_text_color ); ?>">
										</div>
									</div>

									<div class="bpm-form-group">
										<label class="bpm-label"><?php esc_html_e( 'Border Radius (px)', 'bangladeshi-payments-mobile' ); ?></label>
										<input type="number" min="0" max="40" name="bpm_whatsapp_btn_radius" id="bpm_input_radius" value="<?php echo esc_attr( $btn_radius ); ?>" class="bpm-input-field">
									</div>

									<div class="bpm-form-group bpm-form-group-reset">
										<label class="bpm-label"><?php esc_html_e( 'Font Size (px)', 'bangladeshi-payments-mobile' ); ?></label>
										<input type="number" min="11" max="22" name="bpm_whatsapp_btn_font_size" id="bpm_input_font_size" value="<?php echo esc_attr( $btn_font_size ); ?>" class="bpm-input-field">
									</div>

									<div class="bpm-form-group bpm-form-group-reset">
										<label class="bpm-label"><?php esc_html_e( 'Vertical Padding (px)', 'bangladeshi-payments-mobile' ); ?></label>
										<input type="number" min="6" max="24" name="bpm_whatsapp_btn_padding_y" id="bpm_input_padding_y" value="<?php echo esc_attr( $btn_padding_y ); ?>" class="bpm-input-field">
									</div>
								</div>
							</div>

							<!-- Placement Locations -->
							<div class="bpm-nested-box">
								<span class="bpm-nested-heading"><?php esc_html_e( 'Display Locations', 'bangladeshi-payments-mobile' ); ?></span>
								
								<div class="bpm-toggle-row" style="padding-top: 4px;">
									<div>
										<strong class="bpm-toggle-title" style="font-size: 12.5px;"><?php esc_html_e( 'Single Product Pages', 'bangladeshi-payments-mobile' ); ?></strong>
										<p class="bpm-desc"><?php esc_html_e( 'Render button beside/below the Add to Cart button.', 'bangladeshi-payments-mobile' ); ?></p>
									</div>
									<label class="bpm-switch">
										<input type="checkbox" class="bpm-blue-switch" name="bpm_whatsapp_show_on_single" value="yes" <?php checked( 'yes', $show_on_single ); ?>>
										<span class="bpm-slider"></span>
									</label>
								</div>

								<div class="bpm-toggle-row" style="padding-bottom: 0;">
									<div>
										<strong class="bpm-toggle-title" style="font-size: 12.5px;"><?php esc_html_e( 'Shop & Category Archive Pages', 'bangladeshi-payments-mobile' ); ?></strong>
										<p class="bpm-desc"><?php esc_html_e( 'Render button on catalog cards below the product loop.', 'bangladeshi-payments-mobile' ); ?></p>
									</div>
									<label class="bpm-switch">
										<input type="checkbox" class="bpm-blue-switch" name="bpm_whatsapp_show_on_archive" value="yes" <?php checked( 'yes', $show_on_archive ); ?>>
										<span class="bpm-slider"></span>
									</label>
								</div>
							</div>

							<div class="bpm-form-group">
								<label class="bpm-label" for="bpm_whatsapp_direct_order_label"><?php esc_html_e( 'Button Label Text', 'bangladeshi-payments-mobile' ); ?></label>
								<input
									type="text"
									name="bpm_whatsapp_direct_order_label"
									id="bpm_whatsapp_direct_order_label"
									value="<?php echo esc_attr( $direct_order_label ); ?>"
									class="bpm-input-field"
								>
							</div>

							<div class="bpm-form-group bpm-form-group-reset">
								<label class="bpm-label" for="bpm_whatsapp_direct_order_msg"><?php esc_html_e( 'Pre-filled Product Order Message', 'bangladeshi-payments-mobile' ); ?></label>
								<textarea
									name="bpm_whatsapp_direct_order_msg"
									id="bpm_whatsapp_direct_order_msg"
									rows="6"
									class="bpm-textarea-field"
								><?php echo esc_textarea( $direct_order_msg ); ?></textarea>

								<div style="margin-top: 8px;">
									<span style="font-size: 11px; color: var(--bpm-slate-400); font-weight: 600; display: block; margin-bottom: 2px;">
										<?php esc_html_e( 'Click tag to insert:', 'bangladeshi-payments-mobile' ); ?>
									</span>
									<span class="bpm-tag" data-target="bpm_whatsapp_direct_order_msg" data-tag="{product_name}">{product_name}</span>
									<span class="bpm-tag" data-target="bpm_whatsapp_direct_order_msg" data-tag="{product_price}">{product_price}</span>
									<span class="bpm-tag" data-target="bpm_whatsapp_direct_order_msg" data-tag="{product_url}">{product_url}</span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Right Column: Live Chat Widget & Auto-Cancel Timer -->
				<div>
					<!-- Section 3: Global Floating Live Chat Widget -->
					<div class="bpm-setting-card">
						<div class="bpm-card-header">
							<div class="bpm-icon-badge bpm-icon-emerald">
								<svg class="bpm-header-svg" viewBox="0 0 24 24">
									<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
								</svg>
							</div>
							<div>
								<h2>3. Floating WhatsApp Live Chat</h2>
								<p>Clean floating WhatsApp icon on store frontend</p>
							</div>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<strong class="bpm-toggle-title"><?php esc_html_e( 'Enable Floating Chat Icon', 'bangladeshi-payments-mobile' ); ?></strong>
								<p class="bpm-desc"><?php esc_html_e( 'Shows a pure WhatsApp SVG button at the corner of your website.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" id="bpm_chat_widget_toggle" name="bpm_enable_whatsapp_chat_widget" value="yes" <?php checked( 'yes', $enable_chat_widget ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div id="bpm_chat_widget_wrap" class="bpm-collapsible-box <?php echo 'yes' === $enable_chat_widget ? '' : 'is-hidden'; ?>">
							<div class="bpm-form-group">
								<label class="bpm-label" for="bpm_whatsapp_chat_widget_number"><?php esc_html_e( 'Dedicated WhatsApp Number (Optional)', 'bangladeshi-payments-mobile' ); ?></label>
								<div class="bpm-prefix-box">
									<span>+880</span>
									<input
										type="text"
										name="bpm_whatsapp_chat_widget_number"
										id="bpm_whatsapp_chat_widget_number"
										value="<?php echo esc_attr( preg_replace( '/^880/', '', $chat_widget_number ) ); ?>"
										placeholder="Uses main number if blank"
									>
								</div>
								<p class="bpm-desc"><?php esc_html_e( 'Leave blank to use the main business number.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>

							<div class="bpm-form-group">
								<label class="bpm-label" for="bpm_whatsapp_chat_widget_msg"><?php esc_html_e( 'Default Chat Message', 'bangladeshi-payments-mobile' ); ?></label>
								<textarea
									name="bpm_whatsapp_chat_widget_msg"
									id="bpm_whatsapp_chat_widget_msg"
									rows="3"
									class="bpm-textarea-field"
								><?php echo esc_textarea( $chat_widget_msg ); ?></textarea>
							</div>

							<div style="border-top: 1px dashed var(--bpm-slate-200); padding-top: 14px; margin-top: 14px;">
								<div class="bpm-form-group">
									<label class="bpm-label" for="bpm_whatsapp_chat_position"><?php esc_html_e( 'Widget Screen Position', 'bangladeshi-payments-mobile' ); ?></label>
									<select name="bpm_whatsapp_chat_position" id="bpm_whatsapp_chat_position" class="bpm-input-field">
										<option value="right" <?php selected( 'right', $chat_position ); ?>><?php esc_html_e( 'Bottom Right Corner (Default)', 'bangladeshi-payments-mobile' ); ?></option>
										<option value="left" <?php selected( 'left', $chat_position ); ?>><?php esc_html_e( 'Bottom Left Corner', 'bangladeshi-payments-mobile' ); ?></option>
									</select>
								</div>

								<div class="bpm-form-group bpm-form-group-reset">
									<label class="bpm-label" for="bpm_whatsapp_chat_device_view"><?php esc_html_e( 'Device Visibility', 'bangladeshi-payments-mobile' ); ?></label>
									<select name="bpm_whatsapp_chat_device_view" id="bpm_whatsapp_chat_device_view" class="bpm-input-field">
										<option value="all" <?php selected( 'all', $chat_device_view ); ?>><?php esc_html_e( 'Show On Both Desktop & Mobile', 'bangladeshi-payments-mobile' ); ?></option>
										<option value="mobile_only" <?php selected( 'mobile_only', $chat_device_view ); ?>><?php esc_html_e( 'Show On Mobile Only', 'bangladeshi-payments-mobile' ); ?></option>
										<option value="desktop_only" <?php selected( 'desktop_only', $chat_device_view ); ?>><?php esc_html_e( 'Show On Desktop Only', 'bangladeshi-payments-mobile' ); ?></option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<!-- Section 4: Unpaid Order Auto-Cancel Timer -->
					<div class="bpm-setting-card">
						<div class="bpm-card-header">
							<div class="bpm-icon-badge bpm-icon-amber">
								<svg class="bpm-header-svg" viewBox="0 0 24 24">
									<circle cx="12" cy="12" r="10"></circle>
									<polyline points="12 6 12 12 16 14"></polyline>
								</svg>
							</div>
							<div>
								<h2>4. Unpaid Order Auto-Cancel Timer</h2>
								<p>Release reserved inventory by cancelling pending orders automatically</p>
							</div>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<strong class="bpm-toggle-title"><?php esc_html_e( 'Auto-Cancel Pending Orders', 'bangladeshi-payments-mobile' ); ?></strong>
								<p class="bpm-desc"><?php esc_html_e( 'Automatically marks unpaid mobile gateway orders as cancelled.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" id="bpm_autocancel_toggle" class="bpm-amber-switch" name="bpm_enable_unpaid_autocancel" value="yes" <?php checked( 'yes', $enable_autocancel ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div id="bpm_autocancel_wrap" class="bpm-collapsible-box <?php echo 'yes' === $enable_autocancel ? '' : 'is-hidden'; ?>">
							<div class="bpm-form-group bpm-form-group-reset">
								<label class="bpm-label" for="bpm_unpaid_autocancel_hours"><?php esc_html_e( 'Cancel After Time Threshold', 'bangladeshi-payments-mobile' ); ?></label>
								<div style="display: flex; align-items: center; gap: 10px;">
									<input
										type="number"
										name="bpm_unpaid_autocancel_hours"
										id="bpm_unpaid_autocancel_hours"
										value="<?php echo esc_attr( $cancel_hours ); ?>"
										class="bpm-input-field"
										min="1"
										max="168"
										style="width: 100px;"
									>
									<span style="font-size: 13px; color: var(--bpm-slate-600); font-weight: 500;"><?php esc_html_e( 'Hours (Standard: 12 Hours)', 'bangladeshi-payments-mobile' ); ?></span>
								</div>
							</div>
						</div>
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
					<?php esc_html_e( 'Save All Settings', 'bangladeshi-payments-mobile' ); ?>
				</button>
			</div>
		</form>
	</div>

	<script>
	jQuery(document).ready(function($) {
		// Auto-dismiss top toast notification smoothly after 4.5 seconds
		var $topToast = $('#bpm_save_toast');
		if ($topToast.length) {
			setTimeout(function() {
				$topToast.css({
					'opacity': '0',
					'transform': 'translateY(-16px) scale(0.96)'
				});
				setTimeout(function() {
					$topToast.parent().remove();
				}, 320);
			}, 4500);
		}

		// Live Preview Update Function.
		function bpmUpdatePreview() {
			var label   = $('#bpm_whatsapp_direct_order_label').val() || 'হোয়াটসঅ্যাপে সরাসরি অর্ডার করুন';
			var bg      = $('#bpm_input_bg').val() || '#25D366';
			var text    = $('#bpm_input_text').val() || '#ffffff';
			var radius  = $('#bpm_input_radius').val() || '8';
			var size    = $('#bpm_input_font_size').val() || '14';
			var padding = $('#bpm_input_padding_y').val() || '12';

			$('#bpm_preview_text').text(label);
			$('#bpm_button_live_preview').css({
				'background-color': bg,
				'color': text,
				'border-radius': radius + 'px',
				'font-size': size + 'px',
				'padding-top': padding + 'px',
				'padding-bottom': padding + 'px'
			});
		}

		// Color & text inputs synchronization.
		$('#bpm_color_bg').on('input', function() {
			$('#bpm_input_bg').val($(this).val());
			bpmUpdatePreview();
		});
		$('#bpm_input_bg').on('input', function() {
			$('#bpm_color_bg').val($(this).val());
			bpmUpdatePreview();
		});

		$('#bpm_color_hover').on('input', function() {
			$('#bpm_input_hover').val($(this).val());
		});
		$('#bpm_input_hover').on('input', function() {
			$('#bpm_color_hover').val($(this).val());
		});

		$('#bpm_color_text').on('input', function() {
			$('#bpm_input_text').val($(this).val());
			bpmUpdatePreview();
		});
		$('#bpm_input_text').on('input', function() {
			$('#bpm_color_text').val($(this).val());
			bpmUpdatePreview();
		});

		// Numeric & text input triggers.
		$('#bpm_whatsapp_direct_order_label, #bpm_input_radius, #bpm_input_font_size, #bpm_input_padding_y').on('input', bpmUpdatePreview);

		// Hover demonstration on live preview.
		$('#bpm_button_live_preview').on('mouseenter', function() {
			var hoverBg = $('#bpm_input_hover').val();
			if (hoverBg) {
				$(this).css('background-color', hoverBg);
			}
		}).on('mouseleave', function() {
			var normalBg = $('#bpm_input_bg').val();
			if (normalBg) {
				$(this).css('background-color', normalBg);
			}
		});

		// Tag Insertion Logic.
		$('.bpm-tag').on('click', function() {
			var tag = $(this).data('tag');
			var targetId = $(this).data('target');
			var $field = $('#' + targetId);

			if ($field.length) {
				var pos = $field[0].selectionStart;
				var val = $field.val();
				$field.val(val.substring(0, pos) + ' ' + tag + ' ' + val.substring(pos));
			}
		});

		// Dynamic collapsible toggles.
		$('#bpm_direct_order_toggle').on('change', function() {
			var $wrap = $('#bpm_direct_order_wrap');
			if ($(this).is(':checked')) {
				$wrap.removeClass('is-hidden').hide().slideDown(200);
				bpmUpdatePreview();
			} else {
				$wrap.slideUp(200, function() {
					$(this).addClass('is-hidden');
				});
			}
		});

		$('#bpm_chat_widget_toggle').on('change', function() {
			var $wrap = $('#bpm_chat_widget_wrap');
			if ($(this).is(':checked')) {
				$wrap.removeClass('is-hidden').hide().slideDown(200);
			} else {
				$wrap.slideUp(200, function() {
					$(this).addClass('is-hidden');
				});
			}
		});

		$('#bpm_autocancel_toggle').on('change', function() {
			var $wrap = $('#bpm_autocancel_wrap');
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
 * ------------------------------------------------------------------------
 * Helper: Output Dynamic Frontend CSS for Direct Order Buttons
 * ------------------------------------------------------------------------
 */
add_action( 'wp_head', 'bpm_output_quick_order_button_styles' );
function bpm_output_quick_order_button_styles() {
	if ( 'yes' !== get_option( 'bpm_enable_whatsapp_direct_order', 'no' ) ) {
		return;
	}

	$bg_color    = get_option( 'bpm_whatsapp_btn_bg', '#25D366' );
	$hover_color = get_option( 'bpm_whatsapp_btn_hover', '#1ebd5a' );
	$text_color  = get_option( 'bpm_whatsapp_btn_text_color', '#ffffff' );
	$radius      = absint( get_option( 'bpm_whatsapp_btn_radius', 8 ) );
	$font_size   = absint( get_option( 'bpm_whatsapp_btn_font_size', 14 ) );
	$padding_y   = absint( get_option( 'bpm_whatsapp_btn_padding_y', 12 ) );
	?>
	<style id="bpm-quick-order-dynamic-css">
		.bpm-quick-order-btn-single {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			width: 100%;
			margin-top: 10px;
			background-color: <?php echo esc_attr( $bg_color ); ?> !important;
			color: <?php echo esc_attr( $text_color ); ?> !important;
			padding: <?php echo esc_attr( $padding_y ); ?>px 20px !important;
			font-size: <?php echo esc_attr( $font_size ); ?>px !important;
			font-weight: 700 !important;
			border-radius: <?php echo esc_attr( $radius ); ?>px !important;
			text-decoration: none !important;
			box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
			transition: all 0.2s ease !important;
			box-sizing: border-box;
			line-height: 1.2;
		}
		.bpm-quick-order-btn-single:hover {
			background-color: <?php echo esc_attr( $hover_color ); ?> !important;
			color: <?php echo esc_attr( $text_color ); ?> !important;
			transform: translateY(-1px);
			box-shadow: 0 6px 16px rgba(37, 211, 102, 0.35);
		}
		.bpm-quick-order-btn-loop {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 6px;
			width: 100%;
			margin-top: 6px;
			background-color: <?php echo esc_attr( $bg_color ); ?> !important;
			color: <?php echo esc_attr( $text_color ); ?> !important;
			padding: <?php echo esc_attr( max( 6, round( $padding_y * 0.75 ) ) ); ?>px 12px !important;
			font-size: <?php echo esc_attr( max( 11, $font_size - 2 ) ); ?>px !important;
			font-weight: 700 !important;
			border-radius: <?php echo esc_attr( max( 4, round( $radius * 0.75 ) ) ); ?>px !important;
			text-decoration: none !important;
			box-shadow: 0 2px 6px rgba(37, 211, 102, 0.2);
			transition: all 0.2s ease !important;
			box-sizing: border-box;
			line-height: 1.2;
			text-align: center;
		}
		.bpm-quick-order-btn-loop:hover {
			background-color: <?php echo esc_attr( $hover_color ); ?> !important;
			color: <?php echo esc_attr( $text_color ); ?> !important;
			transform: translateY(-1px);
			box-shadow: 0 4px 10px rgba(37, 211, 102, 0.35);
		}
		.bpm-quick-order-svg {
			width: 18px;
			height: 18px;
			fill: currentColor;
			flex-shrink: 0;
		}
		.bpm-quick-order-loop-svg {
			width: 14px;
			height: 14px;
			fill: currentColor;
			flex-shrink: 0;
		}
	</style>
	<?php
}

/**
 * ------------------------------------------------------------------------
 * Helper: Build Dynamic Product WhatsApp Link
 * ------------------------------------------------------------------------
 */
function bpm_get_product_whatsapp_url( $product ) {
	$whatsapp_num = get_option( 'bpm_whatsapp_number', '' );
	if ( empty( $whatsapp_num ) ) {
		return '';
	}

	$clean_num = preg_replace( '/[^0-9]/', '', $whatsapp_num );
	if ( ! preg_match( '/^880/', $clean_num ) ) {
		$clean_num = '880' . ltrim( $clean_num, '0' );
	}

	$product_name  = $product->get_name();
	$product_price = html_entity_decode( wp_strip_all_tags( wc_price( $product->get_price() ) ), ENT_QUOTES, 'UTF-8' );
	$product_url   = get_permalink( $product->get_id() );

	$default_template = "হ্যালো, আমি এই প্রোডাক্টটি সরাসরি অর্ডার করতে চাই:\n\nপ্রোডাক্ট: {product_name}\nদাম: {product_price}\nলিঙ্ক: {product_url}\n\nআমার নাম:\nঠিকানা:\nমোবাইল নম্বর:";
	$raw_template     = get_option( 'bpm_whatsapp_direct_order_msg', $default_template );

	// Normalize all line endings (CRLF / CR) into single LF (\n).
	$raw_template = str_replace( array( "\r\n", "\r" ), "\n", $raw_template );

	$message = str_replace(
		array( '{product_name}', '{product_price}', '{product_url}' ),
		array( $product_name, $product_price, $product_url ),
		$raw_template
	);

	// Force explicit %0A encoding so newlines are preserved in WhatsApp Web & mobile clients.
	$encoded_message = str_replace( array( '%0D%0A', '%0D', '%0A' ), '%0A', rawurlencode( $message ) );

	return 'https://api.whatsapp.com/send?phone=' . $clean_num . '&text=' . $encoded_message;
}

/**
 * ------------------------------------------------------------------------
 * 1. FRONTEND: Direct WhatsApp Order CTA on Single Product Page
 * ------------------------------------------------------------------------
 */
add_action( 'woocommerce_after_add_to_cart_button', 'bpm_render_single_product_quick_order_button', 25 );
function bpm_render_single_product_quick_order_button() {
	if ( 'yes' !== get_option( 'bpm_enable_whatsapp_direct_order', 'no' ) ) {
		return;
	}

	if ( 'yes' !== get_option( 'bpm_whatsapp_show_on_single', 'no' ) ) {
		return;
	}

	global $product;
	if ( ! $product ) {
		return;
	}

	$whatsapp_url = bpm_get_product_whatsapp_url( $product );
	if ( empty( $whatsapp_url ) ) {
		return;
	}

	$button_label = get_option( 'bpm_whatsapp_direct_order_label', __( 'হোয়াটসঅ্যাপে সরাসরি অর্ডার করুন', 'bangladeshi-payments-mobile' ) );
	?>
	<!-- Use esc_attr() so WordPress does not sanitize away %0A newline parameters -->
	<a href="<?php echo esc_attr( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="bpm-quick-order-btn-single">
		<svg class="bpm-quick-order-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
			<path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
		</svg>
		<span><?php echo esc_html( $button_label ); ?></span>
	</a>
	<?php
}

/**
 * ------------------------------------------------------------------------
 * 2. FRONTEND: Direct WhatsApp Order CTA on Shop & Archive Loops
 * ------------------------------------------------------------------------
 */
add_action( 'woocommerce_after_shop_loop_item', 'bpm_render_loop_product_quick_order_button', 15 );
function bpm_render_loop_product_quick_order_button() {
	if ( 'yes' !== get_option( 'bpm_enable_whatsapp_direct_order', 'no' ) ) {
		return;
	}

	if ( 'yes' !== get_option( 'bpm_whatsapp_show_on_archive', 'no' ) ) {
		return;
	}

	global $product;
	if ( ! $product ) {
		return;
	}

	$whatsapp_url = bpm_get_product_whatsapp_url( $product );
	if ( empty( $whatsapp_url ) ) {
		return;
	}

	$button_label = get_option( 'bpm_whatsapp_direct_order_label', __( 'হোয়াটসঅ্যাপে সরাসরি অর্ডার করুন', 'bangladeshi-payments-mobile' ) );
	?>
	<!-- Use esc_attr() so WordPress does not sanitize away %0A newline parameters -->
	<a href="<?php echo esc_attr( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="bpm-quick-order-btn-loop">
		<svg class="bpm-quick-order-loop-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
			<path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
		</svg>
		<span><?php echo esc_html( $button_label ); ?></span>
	</a>
	<?php
}

/**
 * ------------------------------------------------------------------------
 * 3. FRONTEND: Clean Floating WhatsApp Live Chat Widget
 * ------------------------------------------------------------------------
 */
add_action( 'wp_footer', 'bpm_render_frontend_whatsapp_chat_bubble' );
function bpm_render_frontend_whatsapp_chat_bubble() {
	if ( is_admin() || 'yes' !== get_option( 'bpm_enable_whatsapp_chat_widget', 'no' ) ) {
		return;
	}

	$chat_number = get_option( 'bpm_whatsapp_chat_widget_number', '' );
	if ( empty( $chat_number ) ) {
		$chat_number = get_option( 'bpm_whatsapp_number', '' );
	}

	$chat_number = preg_replace( '/[^0-9]/', '', $chat_number );
	if ( empty( $chat_number ) ) {
		return;
	}

	if ( ! preg_match( '/^880/', $chat_number ) ) {
		$chat_number = '880' . ltrim( $chat_number, '0' );
	}

	$default_msg = get_option( 'bpm_whatsapp_chat_widget_msg', 'হ্যালো, অর্ডার বা পেমেন্ট সম্পর্কে জানতে চাই।' );
	$position    = get_option( 'bpm_whatsapp_chat_position', 'right' );
	$device_view = get_option( 'bpm_whatsapp_chat_device_view', 'all' );

	$whatsapp_url = 'https://api.whatsapp.com/send?phone=' . $chat_number;
	if ( ! empty( $default_msg ) ) {
		$normalized_msg = str_replace( array( "\r\n", "\r" ), "\n", $default_msg );
		$whatsapp_url  .= '&text=' . str_replace( array( '%0D%0A', '%0D', '%0A' ), '%0A', rawurlencode( $normalized_msg ) );
	}

	$pos_css = ( 'left' === $position ) ? 'left: 24px;' : 'right: 24px;';
	?>
	<style>
		.bpm-floating-wa-btn {
			position: fixed;
			bottom: 24px;
			<?php echo esc_attr( $pos_css ); ?>
			width: 56px;
			height: 56px;
			background-color: #25D366;
			color: #ffffff !important;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0 4px 14px rgba(37, 211, 102, 0.45);
			z-index: 999999;
			transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
			text-decoration: none;
		}

		.bpm-floating-wa-btn:hover {
			transform: scale(1.08) translateY(-2px);
			box-shadow: 0 6px 20px rgba(37, 211, 102, 0.55);
			color: #ffffff !important;
		}

		.bpm-floating-wa-btn svg {
			width: 32px;
			height: 32px;
			fill: currentColor;
			display: block;
		}

		<?php if ( 'mobile_only' === $device_view ) : ?>
			@media (min-width: 769px) {
				.bpm-floating-wa-btn {
					display: none !important;
				}
			}
		<?php elseif ( 'desktop_only' === $device_view ) : ?>
			@media (max-width: 768px) {
				.bpm-floating-wa-btn {
					display: none !important;
				}
			}
		<?php endif; ?>
	</style>

	<!-- Use esc_attr() so WordPress does not sanitize away %0A newline parameters -->
	<a href="<?php echo esc_attr( $whatsapp_url ); ?>" class="bpm-floating-wa-btn" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'bangladeshi-payments-mobile' ); ?>">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
			<path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
		</svg>
	</a>
	<?php
}

/**
 * ------------------------------------------------------------------------
 * 4. CRON: Automated Unpaid MFS Orders Auto-Cancel Scheduler
 * ------------------------------------------------------------------------
 */
add_action( 'init', 'bpm_register_unpaid_order_cancellation_cron' );
function bpm_register_unpaid_order_cancellation_cron() {
	if ( ! wp_next_scheduled( 'bpm_hourly_unpaid_order_cancellation' ) ) {
		wp_schedule_event( time(), 'hourly', 'bpm_hourly_unpaid_order_cancellation' );
	}
}

add_action( 'bpm_hourly_unpaid_order_cancellation', 'bpm_execute_unpaid_order_cancellation' );
function bpm_execute_unpaid_order_cancellation() {
	if ( 'yes' !== get_option( 'bpm_enable_unpaid_autocancel', 'no' ) ) {
		return;
	}

	$hours = absint( get_option( 'bpm_unpaid_autocancel_hours', 12 ) );
	if ( $hours <= 0 ) {
		return;
	}

	$time_threshold = gmdate( 'Y-m-d H:i:s', time() - ( $hours * HOUR_IN_SECONDS ) );

	$unpaid_orders = wc_get_orders( array(
		'status'         => array( 'pending', 'on-hold' ),
		'payment_method' => array( 'bkash', 'nagad', 'rocket', 'upay' ),
		'date_created'   => '<' . $time_threshold,
		'limit'          => 50,
		'return'         => 'objects',
	) );

	if ( ! empty( $unpaid_orders ) ) {
		foreach ( $unpaid_orders as $order ) {
			$order->update_status(
				'cancelled',
				sprintf(
					/* translators: %d: number of hours threshold */
					__( 'Cancelled automatically: Mobile banking payment not received within %d hours.', 'bangladeshi-payments-mobile' ),
					$hours
				)
			);
		}
	}
}