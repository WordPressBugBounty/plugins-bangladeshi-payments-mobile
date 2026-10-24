<?php
/**
 * Invoice & Thermal Packing Slips Module (Enterprise Clean UI/UX Edition).
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch store core branding data from WordPress & WooCommerce Core settings.
 *
 * @return array
 */
function bpm_get_store_core_data() {
	$shop_name = get_bloginfo( 'name' );

	$custom_logo_id = get_theme_mod( 'custom_logo' );
	$shop_logo      = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'medium' ) : '';

	$address_1 = get_option( 'woocommerce_store_address', '' );
	$address_2 = get_option( 'woocommerce_store_address_2', '' );
	$city      = get_option( 'woocommerce_store_city', '' );
	$postcode  = get_option( 'woocommerce_store_postcode', '' );
	$country   = function_exists( 'WC' ) && WC()->countries && isset( WC()->countries->countries[ get_option( 'woocommerce_default_country', 'BD' ) ] )
		? WC()->countries->countries[ get_option( 'woocommerce_default_country', 'BD' ) ]
		: 'Bangladesh';

	$address_parts = array_filter( array( $address_1, $address_2, $city, $postcode, $country ) );
	$full_address  = ! empty( $address_parts ) ? implode( ', ', $address_parts ) : 'Dhaka, Bangladesh';

	$shop_phone = get_option( 'bpm_invoice_custom_phone', '' );
	$shop_email = get_option( 'admin_email', '' );

	return array(
		'name'    => $shop_name,
		'logo'    => $shop_logo,
		'address' => $full_address,
		'phone'   => $shop_phone,
		'email'   => $shop_email,
	);
}

/**
 * Generate lightweight standalone SVG Barcode (Code 128 subset simulated pattern).
 *
 * @param string $code Order number or string.
 * @return string SVG Markup
 */
function bpm_generate_inline_barcode_svg( $code ) {
	$code       = (string) $code;
	$len        = strlen( $code );
	$bar_width  = 2;
	$height     = 34;
	$total_bars = 40 + ( $len * 10 );
	$svg_width  = $total_bars * $bar_width;

	$svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . esc_attr( $svg_width ) . ' ' . esc_attr( $height + 14 ) . '" style="max-width:180px;height:auto;display:inline-block;vertical-align:middle;">';
	$x    = 6;
	$hash = crc32( $code );

	for ( $i = 0; $i < 32; $i++ ) {
		$w    = ( ( $hash >> ( $i % 31 ) ) & 1 ) ? 2 : 4;
		$svg .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . $height . '" fill="#0f172a" />';
		$x   += $w + 2;
	}

	$svg .= '<text x="50%" y="' . ( $height + 12 ) . '" font-family="ui-monospace, monospace" font-size="10" font-weight="700" fill="#475569" text-anchor="middle" letter-spacing="1">*' . esc_html( $code ) . '*</text>';
	$svg .= '</svg>';

	return $svg;
}

/**
 * Handle form submission for Invoice & Slips settings.
 *
 * @return array|null Notice array or null.
 */
function bpm_save_invoice_settings() {
	if ( ! isset( $_POST['bpm_invoice_settings_nonce'] ) ) {
		return null;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bpm_invoice_settings_nonce'] ) ), 'bpm_save_invoice_settings_action' ) ) {
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

	$enable_invoice   = isset( $_POST['bpm_enable_invoices'] ) ? 'yes' : 'no';
	$custom_phone     = isset( $_POST['bpm_invoice_custom_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['bpm_invoice_custom_phone'] ) ) : '';
	$footer_note      = isset( $_POST['bpm_invoice_footer_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bpm_invoice_footer_note'] ) ) : '';
	$slip_format      = ( isset( $_POST['bpm_slip_format'] ) && 'a4' === $_POST['bpm_slip_format'] ) ? 'a4' : '80mm';
	$show_trx_details = isset( $_POST['bpm_invoice_show_trx_details'] ) ? 'yes' : 'no';
	$show_barcode     = isset( $_POST['bpm_invoice_show_barcode'] ) ? 'yes' : 'no';
	$show_signature   = isset( $_POST['bpm_invoice_show_signature'] ) ? 'yes' : 'no';
	$auto_print       = isset( $_POST['bpm_invoice_autoprint'] ) ? 'yes' : 'no';

	update_option( 'bpm_enable_invoices', $enable_invoice );
	update_option( 'bpm_invoice_custom_phone', $custom_phone );
	update_option( 'bpm_invoice_footer_note', $footer_note );
	update_option( 'bpm_slip_format', $slip_format );
	update_option( 'bpm_invoice_show_trx_details', $show_trx_details );
	update_option( 'bpm_invoice_show_barcode', $show_barcode );
	update_option( 'bpm_invoice_show_signature', $show_signature );
	update_option( 'bpm_invoice_autoprint', $auto_print );

	return array(
		'type'    => 'success',
		'title'   => __( 'Settings Saved!', 'bangladeshi-payments-mobile' ),
		'message' => __( 'Invoicing & Thermal Printing configurations updated successfully.', 'bangladeshi-payments-mobile' ),
	);
}

/**
 * Render the Invoice & Slips Settings Tab with Interactive Preview.
 */
function bpm_render_invoice_settings_tab() {
	$save_notice = bpm_save_invoice_settings();

	$store_info = bpm_get_store_core_data();

	// All toggles default to 'no'
	$enable_invoice   = get_option( 'bpm_enable_invoices', 'no' );
	$custom_phone     = get_option( 'bpm_invoice_custom_phone', '' );
	$footer_note      = get_option( 'bpm_invoice_footer_note', 'ডেলিভারি ম্যানের সামনে প্রোডাক্টটি চেক করে রিসিভ করুন।' );
	$slip_format      = get_option( 'bpm_slip_format', '80mm' );
	$show_trx_details = get_option( 'bpm_invoice_show_trx_details', 'no' );
	$show_barcode     = get_option( 'bpm_invoice_show_barcode', 'no' );
	$show_signature   = get_option( 'bpm_invoice_show_signature', 'no' );
	$auto_print       = get_option( 'bpm_invoice_autoprint', 'no' );
	?>

	<div class="bpm-invoice-shell">
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
			<?php wp_nonce_field( 'bpm_save_invoice_settings_action', 'bpm_invoice_settings_nonce' ); ?>

			<div class="bpm-split-grid">
				<!-- Left: Configuration Form -->
				<div>
					<!-- Section 1: Store Core Data -->
					<div class="bpm-setting-card">
						<div class="bpm-card-header">
							<div class="bpm-icon-badge">
								<span class="dashicons dashicons-admin-site"></span>
							</div>
							<div>
								<h2>1. Store Identity & Core Data</h2>
								<p>Synced with WordPress & WooCommerce Core configurations</p>
							</div>
						</div>

						<div class="bpm-inherited-box">
							<div class="bpm-inherited-item">
								<span>Store Name (WP Title):</span>
								<strong><?php echo esc_html( $store_info['name'] ); ?></strong>
							</div>
							<div class="bpm-inherited-item">
								<span>Physical Address (WC):</span>
								<strong><?php echo esc_html( $store_info['address'] ); ?></strong>
							</div>
							<div class="bpm-inherited-item">
								<span>Custom Logo:</span>
								<strong><?php echo ! empty( $store_info['logo'] ) ? 'Inherited from Customizer' : 'Default Text Header'; ?></strong>
							</div>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<span class="bpm-toggle-title">Enable Invoicing & Print Actions</span>
								<p class="bpm-toggle-desc">Adds 1-click print buttons to WooCommerce orders table & details view.</p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" name="bpm_enable_invoices" value="yes" <?php checked( 'yes', $enable_invoice ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div class="bpm-form-group bpm-form-group-mt">
							<label class="bpm-label" for="bpm_invoice_custom_phone">Support Hotline (For Invoices)</label>
							<input 
								type="text" 
								name="bpm_invoice_custom_phone" 
								id="bpm_invoice_custom_phone" 
								value="<?php echo esc_attr( $custom_phone ); ?>" 
								class="bpm-input-field bpm-live-input" 
								placeholder="+8801700000000"
								data-target="#live_shop_phone"
							>
							<p class="bpm-field-hint">Printed as the primary helpline on packaging slips and invoices.</p>
						</div>
					</div>

					<!-- Section 2: Format & Visual Modules -->
					<div class="bpm-setting-card">
						<div class="bpm-card-header">
							<div class="bpm-icon-badge">
								<span class="dashicons dashicons-printer"></span>
							</div>
							<div>
								<h2>2. Format, Barcode & Delivery Modules</h2>
								<p>Paper format, scannable order barcodes, and delivery signatures</p>
							</div>
						</div>

						<div class="bpm-form-group">
							<label class="bpm-label" for="bpm_slip_format">Default Thermal POS Printer Format</label>
							<select name="bpm_slip_format" id="bpm_slip_format" class="bpm-input-field">
								<option value="80mm" <?php selected( '80mm', $slip_format ); ?>>80mm Standard POS Thermal Receipt (Recommended)</option>
								<option value="a4" <?php selected( 'a4', $slip_format ); ?>>A4 Full Page Document</option>
							</select>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<span class="bpm-toggle-title">Show MFS TrxID & Verification Badge</span>
								<p class="bpm-toggle-desc">Print bKash/Nagad/Rocket TrxID, payer number, and Paid/Due badge.</p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" id="bpm_live_trx_toggle" name="bpm_invoice_show_trx_details" value="yes" <?php checked( 'yes', $show_trx_details ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<span class="bpm-toggle-title">Print Scannable Order Barcode</span>
								<p class="bpm-toggle-desc">Renders an inline Code128 vector barcode for courier hub sorting.</p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" id="bpm_live_barcode_toggle" name="bpm_invoice_show_barcode" value="yes" <?php checked( 'yes', $show_barcode ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<span class="bpm-toggle-title">Rider & Customer Signature Line</span>
								<p class="bpm-toggle-desc">Physical proof of delivery signature slots for Cash on Delivery couriers.</p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" id="bpm_live_signature_toggle" name="bpm_invoice_show_signature" value="yes" <?php checked( 'yes', $show_signature ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div class="bpm-toggle-row">
							<div>
								<span class="bpm-toggle-title">Auto-Open Print Dialog</span>
								<p class="bpm-toggle-desc">Automatically triggers browser print popup upon opening document.</p>
							</div>
							<label class="bpm-switch">
								<input type="checkbox" name="bpm_invoice_autoprint" value="yes" <?php checked( 'yes', $auto_print ); ?>>
								<span class="bpm-slider"></span>
							</label>
						</div>

						<div class="bpm-form-group bpm-form-group-mt">
							<label class="bpm-label" for="bpm_invoice_footer_note">Parcel Notice / Return Policy</label>
							<textarea 
								name="bpm_invoice_footer_note" 
								id="bpm_invoice_footer_note" 
								rows="2" 
								class="bpm-textarea-field bpm-live-input" 
								data-target="#live_footer_note"
							><?php echo esc_textarea( $footer_note ); ?></textarea>
						</div>
					</div>

					<button type="submit" class="bpm-save-btn">
						<svg class="bpm-btn-svg" viewBox="0 0 24 24">
							<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
							<polyline points="17 21 17 13 7 13 7 21"></polyline>
							<polyline points="7 3 7 8 15 8"></polyline>
						</svg>
						<?php esc_html_e( 'Save All Settings', 'bangladeshi-payments-mobile' ); ?>
					</button>
				</div>

				<!-- Right: Interactive Live Preview Pane -->
				<div class="bpm-preview-pane">
					<div class="bpm-preview-nav">
						<span class="bpm-preview-heading">Interactive Document Preview</span>
						<div class="bpm-preview-switch">
							<button type="button" class="bpm-preview-btn active" id="btn_preview_a4">A4 Tax Invoice</button>
							<button type="button" class="bpm-preview-btn" id="btn_preview_80">80mm POS Slip</button>
						</div>
					</div>

					<div id="bpm_preview_canvas" class="bpm-preview-canvas bpm-view-a4">
						<div class="bpm-preview-header">
							<div>
								<?php if ( ! empty( $store_info['logo'] ) ) : ?>
									<img src="<?php echo esc_url( $store_info['logo'] ); ?>" class="bpm-preview-logo" alt="Logo"><br>
								<?php endif; ?>
								<h3 id="live_shop_name" class="bpm-preview-brand-title">
									<?php echo esc_html( $store_info['name'] ); ?>
								</h3>
								<p class="bpm-preview-subtext">
									<?php echo esc_html( $store_info['address'] ); ?>
								</p>
								<p class="bpm-preview-subtext">
									Hotline: <span id="live_shop_phone"><?php echo esc_html( $custom_phone ? $custom_phone : '+8801700000000' ); ?></span>
								</p>
							</div>
							<div class="bpm-preview-header-right">
								<strong id="live_doc_title" class="bpm-preview-doc-title">TAX INVOICE</strong>
								<span class="bpm-preview-order-number">#SAMPLE-1082</span>
								<span class="bpm-preview-date">Date: <?php echo esc_html( gmdate( 'd M, Y' ) ); ?></span>
							</div>
						</div>

						<div class="bpm-preview-info-row">
							<div>
								<span class="bpm-preview-label">Delivered To:</span>
								<strong class="bpm-preview-cust-name">Kazi Tanvir Hasan</strong>
								Phone: 01711223344<br>
								Dhanmondi, Dhaka - 1209
							</div>
							<div id="live_trx_box" class="bpm-preview-trx-box <?php echo 'yes' === $show_trx_details ? '' : 'is-hidden'; ?>">
								<span class="bpm-preview-label">Payment Breakdown:</span>
								Method: <strong>bKash Manual</strong><br>
								TrxID: <strong>9K28JA7L</strong><br>
								<span class="bpm-preview-paid-badge">PAID IN FULL</span>
							</div>
						</div>

						<table class="bpm-preview-table">
							<thead>
								<tr class="bpm-preview-thead-tr">
									<th class="bpm-preview-th">Item Description</th>
									<th class="bpm-preview-th-center">Qty</th>
									<th class="bpm-preview-th-right">Total</th>
								</tr>
							</thead>
							<tbody>
								<tr class="bpm-preview-tr">
									<td class="bpm-preview-td">Premium Cotton Panjabi</td>
									<td class="bpm-preview-td-center">1</td>
									<td class="bpm-preview-td-right">৳1,850</td>
								</tr>
								<tr class="bpm-preview-tr">
									<td class="bpm-preview-td">Delivery Fee (Inside Dhaka)</td>
									<td class="bpm-preview-td-center">1</td>
									<td class="bpm-preview-td-right">৳70</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="2" class="bpm-preview-total-label">Subtotal:</td>
									<td class="bpm-preview-total-val">৳1,850</td>
								</tr>
								<tr>
									<td colspan="2" class="bpm-preview-total-label">Delivery Fee:</td>
									<td class="bpm-preview-total-val">৳70</td>
								</tr>
								<tr>
									<td colspan="2" class="bpm-preview-total-label bpm-preview-grand-total">Grand Total:</td>
									<td class="bpm-preview-total-val bpm-preview-grand-total-val">৳1,920</td>
								</tr>
							</tfoot>
						</table>

						<div id="live_barcode_box" class="bpm-preview-barcode-wrap <?php echo 'yes' === $show_barcode ? '' : 'is-hidden'; ?>">
							<?php echo bpm_generate_inline_barcode_svg( 'SAMPLE-1082' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>

						<div id="live_signature_box" class="bpm-preview-sig-wrap <?php echo 'yes' === $show_signature ? '' : 'is-hidden'; ?>">
							<div class="bpm-preview-sig-col">Customer Signature</div>
							<div class="bpm-preview-sig-col">Authorized Officer</div>
						</div>

						<div id="live_footer_note" class="bpm-preview-footer-note">
							<?php echo esc_html( $footer_note ); ?>
						</div>
					</div>
				</div>
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

		$('.bpm-live-input').on('input', function() {
			var target = $(this).data('target');
			var val = $(this).val();
			if (target === '#live_shop_phone') {
				$(target).text(val ? val : '+8801700000000');
			} else {
				$(target).text(val);
			}
		});

		$('#bpm_live_trx_toggle').on('change', function() {
			$('#live_trx_box').toggleClass('is-hidden', !$(this).is(':checked'));
		});

		$('#bpm_live_barcode_toggle').on('change', function() {
			$('#live_barcode_box').toggleClass('is-hidden', !$(this).is(':checked'));
		});

		$('#bpm_live_signature_toggle').on('change', function() {
			$('#live_signature_box').toggleClass('is-hidden', !$(this).is(':checked'));
		});

		$('#btn_preview_a4').on('click', function() {
			$('.bpm-preview-btn').removeClass('active');
			$(this).addClass('active');
			$('#bpm_preview_canvas').removeClass('bpm-view-thermal-80').addClass('bpm-view-a4');
			$('#live_doc_title').text('TAX INVOICE');
		});

		$('#btn_preview_80').on('click', function() {
			$('.bpm-preview-btn').removeClass('active');
			$(this).addClass('active');
			$('#bpm_preview_canvas').removeClass('bpm-view-a4').addClass('bpm-view-thermal-80');
			$('#live_doc_title').text('POS SLIP (80mm)');
		});
	});
	</script>
	<?php
}

/**
 * Register dedicated "Print" column in WooCommerce Orders List.
 */
add_filter( 'manage_woocommerce_page_wc-orders_columns', 'bpm_register_order_print_column' );
add_filter( 'manage_edit-shop_order_columns', 'bpm_register_order_print_column' );
function bpm_register_order_print_column( $columns ) {
	if ( 'yes' !== get_option( 'bpm_enable_invoices', 'no' ) ) {
		return $columns;
	}

	$new_columns = array();
	foreach ( $columns as $key => $column ) {
		$new_columns[ $key ] = $column;
		if ( 'order_status' === $key ) {
			$new_columns['bpm_print_docs'] = __( 'Print Documents', 'bangladeshi-payments-mobile' );
		}
	}

	if ( ! isset( $new_columns['bpm_print_docs'] ) ) {
		$new_columns['bpm_print_docs'] = __( 'Print Documents', 'bangladeshi-payments-mobile' );
	}

	return $new_columns;
}

/**
 * Render Print Buttons inside the Orders Table Column.
 */
add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'bpm_render_order_print_column_content', 10, 2 );
add_action( 'manage_shop_order_posts_custom_column', 'bpm_render_order_print_column_content', 10, 2 );
function bpm_render_order_print_column_content( $column, $order_or_id ) {
	if ( 'bpm_print_docs' !== $column ) {
		return;
	}

	$order = is_numeric( $order_or_id ) ? wc_get_order( $order_or_id ) : $order_or_id;
	if ( ! $order ) {
		return;
	}

	$order_id    = $order->get_id();
	$invoice_url = wp_nonce_url( admin_url( 'admin.php?bpm_action=print_doc&doc_type=invoice&order_id=' . $order_id ), 'bpm_print_doc_action', 'bpm_print_nonce' );
	$slip_url    = wp_nonce_url( admin_url( 'admin.php?bpm_action=print_doc&doc_type=slip&order_id=' . $order_id ), 'bpm_print_doc_action', 'bpm_print_nonce' );
	?>
	<div class="bpm-order-actions-wrap">
		<a href="<?php echo esc_url( $slip_url ); ?>" target="_blank" title="<?php esc_attr_e( 'Print 80mm POS Thermal Slip', 'bangladeshi-payments-mobile' ); ?>" class="bpm-btn-pos-slip">
			<svg class="bpm-action-btn-svg" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
			<span><?php esc_html_e( 'Slip', 'bangladeshi-payments-mobile' ); ?></span>
		</a>

		<a href="<?php echo esc_url( $invoice_url ); ?>" target="_blank" title="<?php esc_attr_e( 'Print A4 Tax Invoice', 'bangladeshi-payments-mobile' ); ?>" class="bpm-btn-a4-invoice">
			<svg class="bpm-action-btn-svg" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
			<span><?php esc_html_e( 'A4', 'bangladeshi-payments-mobile' ); ?></span>
		</a>
	</div>
	<?php
}

/**
 * Add Meta Box on Single Edit Order Page (HPOS & CPT Compatible).
 */
add_action( 'add_meta_boxes', 'bpm_register_edit_order_print_metabox' );
function bpm_register_edit_order_print_metabox() {
	if ( 'yes' !== get_option( 'bpm_enable_invoices', 'no' ) ) {
		return;
	}

	$screen = class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop_order' )
		: 'shop_order';

	add_meta_box(
		'bpm_order_print_metabox',
		__( 'Print Documents (BD Payments)', 'bangladeshi-payments-mobile' ),
		'bpm_render_edit_order_print_metabox',
		$screen,
		'side',
		'high'
	);
}

/**
 * Render Meta Box Content with 1-Click Print Actions.
 */
function bpm_render_edit_order_print_metabox( $post_or_order_object ) {
	$order = ( $post_or_order_object instanceof \WC_Order ) ? $post_or_order_object : wc_get_order( $post_or_order_object->ID );
	if ( ! $order ) {
		return;
	}

	$order_id    = $order->get_id();
	$invoice_url = wp_nonce_url( admin_url( 'admin.php?bpm_action=print_doc&doc_type=invoice&order_id=' . $order_id ), 'bpm_print_doc_action', 'bpm_print_nonce' );
	$slip_url    = wp_nonce_url( admin_url( 'admin.php?bpm_action=print_doc&doc_type=slip&order_id=' . $order_id ), 'bpm_print_doc_action', 'bpm_print_nonce' );

	$method_slug = $order->get_payment_method();
	$trx_id      = $order->get_meta( "_{$method_slug}_transaction_id", true );
	?>
	<div class="bpm-metabox-stack">
		<?php if ( $trx_id ) : ?>
			<div class="bpm-metabox-trx-chip">
				<strong>TrxID:</strong> <code class="bpm-metabox-trx-code"><?php echo esc_html( $trx_id ); ?></code>
			</div>
		<?php endif; ?>

		<a href="<?php echo esc_url( $slip_url ); ?>" target="_blank" class="button button-primary bpm-metabox-btn-primary">
			<svg class="bpm-metabox-svg" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
			<?php esc_html_e( 'Print Thermal Slip (80mm)', 'bangladeshi-payments-mobile' ); ?>
		</a>

		<a href="<?php echo esc_url( $invoice_url ); ?>" target="_blank" class="button bpm-metabox-btn-secondary">
			<svg class="bpm-metabox-svg" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
			<?php esc_html_e( 'Print A4 Tax Invoice', 'bangladeshi-payments-mobile' ); ?>
		</a>
	</div>
	<?php
}

/**
 * Handle Document Print Engine (A4 & 80mm POS Thermal Slips).
 */
add_action( 'admin_init', 'bpm_handle_print_invoice_slip' );
function bpm_handle_print_invoice_slip() {
	if ( ! isset( $_GET['bpm_action'] ) || 'print_doc' !== $_GET['bpm_action'] ) {
		return;
	}

	if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized access.', 'bangladeshi-payments-mobile' ) );
	}

	check_admin_referer( 'bpm_print_doc_action', 'bpm_print_nonce' );

	$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
	$doc_type = isset( $_GET['doc_type'] ) && 'slip' === $_GET['doc_type'] ? 'slip' : 'invoice';

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		wp_die( esc_html__( 'Invalid Order ID.', 'bangladeshi-payments-mobile' ) );
	}

	$store_info       = bpm_get_store_core_data();
	$shop_name        = $store_info['name'];
	$shop_phone       = $store_info['phone'];
	$shop_address     = $store_info['address'];
	$shop_logo        = $store_info['logo'];
	$footer_note      = get_option( 'bpm_invoice_footer_note', '' );
	$slip_format      = get_option( 'bpm_slip_format', '80mm' );
	$show_trx_details = get_option( 'bpm_invoice_show_trx_details', 'no' );
	$show_barcode     = get_option( 'bpm_invoice_show_barcode', 'no' );
	$show_signature   = get_option( 'bpm_invoice_show_signature', 'no' );
	$auto_print       = get_option( 'bpm_invoice_autoprint', 'no' );

	$method_slug  = $order->get_payment_method();
	$method_title = $order->get_payment_method_title();
	$trx_id       = $order->get_meta( "_{$method_slug}_transaction_id", true );
	$payer_phone  = $order->get_meta( "_{$method_slug}_phone", true );
	$is_paid      = $order->is_paid();

	$doc_title = ( 'slip' === $doc_type ) ? 'Slip-#' . $order_id : 'Invoice-#' . $order_id;
	?>
	<!DOCTYPE html>
	<html lang="bn">
	<head>
		<meta charset="UTF-8">
		<title><?php echo esc_html( $doc_title ); ?></title>
		<style>
			* {
				box-sizing: border-box;
			}

			body {
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Hind Siliguri", "Arial", sans-serif;
				margin: 0;
				color: #0f172a;
				background: #f8fafc;
				-webkit-font-smoothing: antialiased;
			}

			@media print {
				body {
					background: #ffffff !important;
				}
				.bpm-no-print {
					display: none !important;
				}
				.bpm-a4-canvas {
					margin: 0 !important;
					border: none !important;
					box-shadow: none !important;
					padding: 0 !important;
					max-width: 100% !important;
				}
				.bpm-pos-canvas {
					margin: 0 !important;
					border: none !important;
					box-shadow: none !important;
					padding: 2px !important;
				}
			}

			.bpm-no-print {
				background: #0f172a;
				color: #ffffff;
				padding: 12px 24px;
				display: flex;
				align-items: center;
				justify-content: space-between;
				position: sticky;
				top: 0;
				z-index: 999;
				box-shadow: 0 2px 10px rgba(0,0,0,0.15);
			}

			.bpm-no-print-title {
				font-weight: 600;
				font-size: 13px;
			}

			.bpm-btn-print {
				background: #2563eb;
				color: #ffffff;
				border: none;
				padding: 8px 18px;
				border-radius: 6px;
				font-weight: 700;
				font-size: 13px;
				cursor: pointer;
				display: inline-flex;
				align-items: center;
				gap: 6px;
				transition: background 0.15s ease;
			}

			.bpm-btn-print:hover {
				background: #1d4ed8;
			}

			.bpm-print-btn-svg {
				width: 15px;
				height: 15px;
				fill: none;
				stroke: currentColor;
				stroke-width: 2.5;
				stroke-linecap: round;
				stroke-linejoin: round;
			}

			/* A4 Canvas */
			.bpm-a4-canvas {
				max-width: 820px;
				margin: 30px auto;
				background: #ffffff;
				padding: 45px 50px;
				border: 1px solid #e2e8f0;
				border-radius: 12px;
				box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
			}

			.bpm-a4-header {
				display: flex;
				justify-content: space-between;
				align-items: flex-start;
				border-bottom: 2px solid #0f172a;
				padding-bottom: 20px;
				margin-bottom: 24px;
				gap: 20px;
			}

			.bpm-a4-logo {
				max-height: 52px;
				max-width: 180px;
				object-fit: contain;
				margin-bottom: 8px;
			}

			.bpm-a4-brand {
				margin: 0;
				font-size: 22px;
				font-weight: 800;
				color: #0f172a;
				text-transform: uppercase;
				letter-spacing: -0.3px;
			}

			.bpm-a4-subtext {
				font-size: 13px;
				color: #64748b;
				line-height: 1.4;
			}

			.bpm-a4-header-right {
				text-align: right;
				flex-shrink: 0;
			}

			.bpm-a4-title {
				margin: 0;
				font-size: 24px;
				color: #0f172a;
				text-transform: uppercase;
				letter-spacing: -0.3px;
			}

			.bpm-a4-order-id {
				font-size: 15px;
				font-weight: 700;
				color: #2563eb;
				display: block;
				margin-top: 3px;
			}

			.bpm-a4-date {
				font-size: 12px;
				color: #64748b;
				display: block;
				margin-top: 2px;
			}

			.bpm-a4-meta-row {
				display: flex;
				justify-content: space-between;
				align-items: flex-start;
				margin-bottom: 26px;
				gap: 24px;
			}

			.bpm-a4-section-label {
				font-size: 11px;
				text-transform: uppercase;
				color: #64748b;
				letter-spacing: 0.5px;
				display: block;
			}

			.bpm-a4-cust-box {
				font-size: 13.5px;
				margin-top: 6px;
				line-height: 1.45;
			}

			.bpm-a4-cust-name {
				color: #0f172a;
				font-size: 15px;
				display: block;
			}

			.bpm-a4-payment-box {
				font-size: 13px;
				margin-top: 6px;
				line-height: 1.5;
			}

			.bpm-a4-trx-code {
				font-weight: 700;
				color: #2563eb;
			}

			.bpm-a4-table {
				width: 100%;
				border-collapse: collapse;
				margin: 20px 0;
			}

			.bpm-a4-table th {
				background: #f8fafc;
				padding: 10px 12px;
				text-align: left;
				font-size: 11px;
				font-weight: 700;
				text-transform: uppercase;
				color: #475569;
				border-bottom: 1px solid #e2e8f0;
				border-top: 1px solid #e2e8f0;
			}

			.bpm-a4-table td {
				padding: 12px;
				border-bottom: 1px solid #f1f5f9;
				color: #1e293b;
				font-size: 13px;
			}

			.bpm-a4-td-center {
				text-align: center;
			}

			.bpm-a4-td-right {
				text-align: right;
			}

			.bpm-a4-total-label {
				text-align: right;
				font-weight: 700;
				padding-top: 10px;
				color: #475569;
			}

			.bpm-a4-total-val {
				text-align: right;
				font-weight: 700;
				padding-top: 10px;
			}

			.bpm-a4-grand-total {
				font-size: 16px;
				font-weight: 800;
				border-top: 2px solid #0f172a;
				padding-top: 10px;
			}

			.bpm-a4-grand-total-val {
				font-size: 16px;
				font-weight: 800;
				color: #2563eb;
				border-top: 2px solid #0f172a;
				padding-top: 10px;
			}

			.bpm-a4-barcode-wrap {
				text-align: center;
				margin: 24px 0 10px;
			}

			.bpm-a4-sig-wrap {
				display: flex;
				justify-content: space-between;
				margin-top: 40px;
				padding-top: 10px;
			}

			.bpm-a4-sig-col {
				border-top: 1px solid #94a3b8;
				width: 200px;
				text-align: center;
				padding-top: 6px;
				font-size: 12px;
				color: #64748b;
				font-weight: 600;
			}

			.bpm-a4-footer-note {
				background: #f8fafc;
				border: 1px dashed #cbd5e1;
				border-radius: 8px;
				padding: 14px;
				margin-top: 24px;
				text-align: center;
				font-size: 12px;
				color: #475569;
				line-height: 1.45;
			}

			/* POS Thermal Canvas (80mm) */
			.bpm-pos-canvas {
				margin: 25px auto;
				background: #ffffff;
				padding: 18px 16px;
				border: 1px dashed #94a3b8;
				box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
				font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
				width: 320px;
				font-size: 11.5px;
			}

			.bpm-pos-header {
				text-align: center;
				border-bottom: 1px dashed #0f172a;
				padding-bottom: 8px;
				margin-bottom: 10px;
			}

			.bpm-pos-logo {
				max-height: 42px;
				max-width: 120px;
				object-fit: contain;
				margin-bottom: 4px;
			}

			.bpm-pos-brand {
				font-size: 14px;
				text-transform: uppercase;
				display: block;
				font-weight: 800;
			}

			.bpm-pos-hotline {
				font-size: 11px;
				display: block;
				margin-top: 2px;
			}

			.bpm-pos-info {
				margin-bottom: 10px;
				line-height: 1.4;
			}

			.bpm-pos-info-row {
				display: flex;
				justify-content: space-between;
				align-items: center;
			}

			.bpm-pos-ship-box {
				margin-top: 6px;
				line-height: 1.4;
			}

			.bpm-pos-table {
				width: 100%;
				border-collapse: collapse;
				border-top: 1px dashed #0f172a;
				border-bottom: 1px dashed #0f172a;
				margin-bottom: 8px;
			}

			.bpm-pos-table th {
				padding: 5px 0;
			}

			.bpm-pos-table td {
				padding: 5px 0;
			}

			.bpm-pos-totals {
				line-height: 1.4;
				margin-bottom: 8px;
			}

			.bpm-pos-totals-row {
				display: flex;
				justify-content: space-between;
				align-items: center;
			}

			.bpm-pos-amount-due {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-top: 8px;
				padding: 6px 8px;
				background: #0f172a;
				color: #ffffff;
				border-radius: 4px;
				font-size: 12px;
			}

			.bpm-pos-barcode-wrap {
				text-align: center;
				margin: 10px 0;
				padding: 6px 0;
				border-top: 1px dashed #cbd5e1;
			}

			.bpm-pos-sig-wrap {
				display: flex;
				justify-content: space-between;
				margin-top: 20px;
				padding-top: 15px;
				border-top: 1px dashed #94a3b8;
				font-size: 9.5px;
			}

			.bpm-pos-sig-col {
				border-top: 1px solid #0f172a;
				width: 45%;
				text-align: center;
				padding-top: 4px;
				font-weight: 600;
			}

			.bpm-pos-footer-note {
				text-align: center;
				font-size: 10px;
				margin: 8px 0 0;
				color: #475569;
				line-height: 1.4;
			}

			.bpm-badge-paid {
				background: #ecfdf5;
				color: #047857;
				border: 1px solid #a7f3d0;
				padding: 2px 7px;
				border-radius: 4px;
				font-weight: 700;
				font-size: 10px;
				display: inline-block;
			}

			.bpm-badge-due {
				background: #fef2f2;
				color: #b91c1c;
				border: 1px solid #fecaca;
				padding: 2px 7px;
				border-radius: 4px;
				font-weight: 700;
				font-size: 10px;
				display: inline-block;
			}
		</style>
	</head>
	<body <?php echo ( 'yes' === $auto_print ) ? 'onload="window.print()"' : ''; ?>>
		<div class="bpm-no-print">
			<span class="bpm-no-print-title"><?php echo esc_html( $shop_name ); ?> &bull; <?php echo ( 'slip' === $doc_type ) ? '80mm Thermal Packing Slip' : 'Customer Tax Invoice'; ?></span>
			<button type="button" class="bpm-btn-print" onclick="window.print()">
				<svg class="bpm-print-btn-svg" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
				<span><?php esc_html_e( 'Print Document', 'bangladeshi-payments-mobile' ); ?></span>
			</button>
		</div>

		<?php if ( 'slip' === $doc_type && 'a4' !== $slip_format ) : ?>
			<!-- Thermal POS Receipt (80mm) -->
			<div class="bpm-pos-canvas">
				<div class="bpm-pos-header">
					<?php if ( $shop_logo ) : ?>
						<img src="<?php echo esc_url( $shop_logo ); ?>" class="bpm-pos-logo" alt="Logo"><br>
					<?php endif; ?>
					<strong class="bpm-pos-brand"><?php echo esc_html( $shop_name ); ?></strong>
					<?php if ( $shop_phone ) : ?>
						<span class="bpm-pos-hotline">Hotline: <?php echo esc_html( $shop_phone ); ?></span><br>
					<?php endif; ?>
				</div>

				<div class="bpm-pos-info">
					<div class="bpm-pos-info-row">
						<strong>Order: #<?php echo esc_html( $order_id ); ?></strong>
						<span><?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date( 'd/m/Y' ) : '' ); ?></span>
					</div>
					<div class="bpm-pos-ship-box">
						<strong>Ship To:</strong><br>
						<?php echo esc_html( $order->get_formatted_billing_full_name() ); ?><br>
						Phone: <strong><?php echo esc_html( $order->get_billing_phone() ); ?></strong><br>
						<?php echo esc_html( $order->get_billing_address_1() ); ?>, <?php echo esc_html( $order->get_billing_city() ); ?>
					</div>
				</div>

				<table class="bpm-pos-table">
					<thead>
						<tr>
							<th style="text-align: left;">Item</th>
							<th style="text-align: center;">Qty</th>
							<th style="text-align: right;">Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $order->get_items() as $item ) : ?>
							<tr>
								<td><?php echo esc_html( $item->get_name() ); ?></td>
								<td style="text-align: center;"><?php echo esc_html( $item->get_quantity() ); ?></td>
								<td style="text-align: right;"><?php echo wp_kses_post( wc_price( $item->get_total() ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<div class="bpm-pos-totals">
					<div class="bpm-pos-totals-row">
						<span>Subtotal:</span>
						<span><?php echo wp_kses_post( wc_price( $order->get_subtotal() ) ); ?></span>
					</div>
					<div class="bpm-pos-totals-row">
						<span>Delivery:</span>
						<span><?php echo wp_kses_post( wc_price( $order->get_shipping_total() ) ); ?></span>
					</div>
					<div class="bpm-pos-totals-row" style="font-weight: 700; border-top: 1px dotted #cbd5e1; padding-top: 4px; margin-top: 4px;">
						<span>Total:</span>
						<strong><?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?></strong>
					</div>

					<?php if ( $show_trx_details ) : ?>
						<div class="bpm-pos-totals-row" style="margin-top: 6px; padding-top: 4px; border-top: 1px dashed #cbd5e1;">
							<span>Payment:</span>
							<span><?php echo esc_html( $method_title ); ?></span>
						</div>
						<?php if ( $trx_id ) : ?>
							<div class="bpm-pos-totals-row">
								<span>TrxID:</span>
								<strong><?php echo esc_html( $trx_id ); ?></strong>
							</div>
						<?php endif; ?>
					<?php endif; ?>

					<div class="bpm-pos-amount-due">
						<span>DUE AMOUNT:</span>
						<strong><?php echo $is_paid ? '৳0.00 (PAID)' : wp_kses_post( wc_price( $order->get_total() ) ); ?></strong>
					</div>
				</div>

				<?php if ( 'yes' === $show_barcode ) : ?>
					<div class="bpm-pos-barcode-wrap">
						<?php echo bpm_generate_inline_barcode_svg( (string) $order_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $show_signature ) : ?>
					<div class="bpm-pos-sig-wrap">
						<div class="bpm-pos-sig-col">Customer Signature</div>
						<div class="bpm-pos-sig-col">Delivery Rider</div>
					</div>
				<?php endif; ?>

				<?php if ( $footer_note ) : ?>
					<p class="bpm-pos-footer-note"><?php echo esc_html( $footer_note ); ?></p>
				<?php endif; ?>
			</div>

		<?php else : ?>
			<!-- A4 Full Page Invoice -->
			<div class="bpm-a4-canvas">
				<div class="bpm-a4-header">
					<div>
						<?php if ( $shop_logo ) : ?>
							<img src="<?php echo esc_url( $shop_logo ); ?>" class="bpm-a4-logo" alt="Logo"><br>
						<?php endif; ?>
						<h2 class="bpm-a4-brand"><?php echo esc_html( $shop_name ); ?></h2>
						<span class="bpm-a4-subtext"><?php echo esc_html( $shop_address ); ?></span><br>
						<?php if ( $shop_phone ) : ?>
							<span class="bpm-a4-subtext">Hotline: <?php echo esc_html( $shop_phone ); ?></span>
						<?php endif; ?>
					</div>
					<div class="bpm-a4-header-right">
						<h1 class="bpm-a4-title"><?php echo ( 'slip' === $doc_type ) ? 'PACKING SLIP' : 'TAX INVOICE'; ?></h1>
						<span class="bpm-a4-order-id">#<?php echo esc_html( $order_id ); ?></span>
						<span class="bpm-a4-date">Date: <?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date( 'F j, Y' ) : '' ); ?></span>
					</div>
				</div>

				<div class="bpm-a4-meta-row">
					<div>
						<strong class="bpm-a4-section-label">Customer & Delivery Info:</strong>
						<div class="bpm-a4-cust-box">
							<strong class="bpm-a4-cust-name"><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></strong>
							<?php echo esc_html( $order->get_billing_address_1() ); ?><br>
							<?php echo esc_html( $order->get_billing_city() ); ?> - <?php echo esc_html( $order->get_billing_postcode() ); ?><br>
							Phone: <strong><?php echo esc_html( $order->get_billing_phone() ); ?></strong>
						</div>
					</div>

					<?php if ( $show_trx_details ) : ?>
					<div class="bpm-a4-header-right">
						<strong class="bpm-a4-section-label">Payment Breakdown:</strong>
						<div class="bpm-a4-payment-box">
							Gateway: <strong><?php echo esc_html( $method_title ); ?></strong><br>
							<?php if ( $trx_id ) : ?>
								TrxID: <code class="bpm-a4-trx-code"><?php echo esc_html( $trx_id ); ?></code><br>
							<?php endif; ?>
							<?php if ( $payer_phone ) : ?>
								Sender: <strong><?php echo esc_html( $payer_phone ); ?></strong><br>
							<?php endif; ?>
							Verification: <span class="<?php echo $is_paid ? 'bpm-badge-paid' : 'bpm-badge-due'; ?>">
								<?php echo $is_paid ? 'PAID IN FULL' : 'CASH ON DELIVERY (DUE)'; ?>
							</span>
						</div>
					</div>
					<?php endif; ?>
				</div>

				<table class="bpm-a4-table">
					<thead>
						<tr>
							<th>Item Description</th>
							<th style="width: 80px; text-align: center;">Qty</th>
							<th style="width: 120px; text-align: right;">Unit Price</th>
							<th style="width: 120px; text-align: right;">Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $order->get_items() as $item ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $item->get_name() ); ?></strong></td>
								<td class="bpm-a4-td-center"><?php echo esc_html( $item->get_quantity() ); ?></td>
								<td class="bpm-a4-td-right"><?php echo wp_kses_post( wc_price( $order->get_item_subtotal( $item ) ) ); ?></td>
								<td class="bpm-a4-td-right"><strong><?php echo wp_kses_post( wc_price( $item->get_total() ) ); ?></strong></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="3" class="bpm-a4-total-label">Subtotal:</td>
							<td class="bpm-a4-total-val"><?php echo wp_kses_post( wc_price( $order->get_subtotal() ) ); ?></td>
						</tr>
						<tr>
							<td colspan="3" class="bpm-a4-total-label">Delivery Fee:</td>
							<td class="bpm-a4-total-val"><?php echo wp_kses_post( wc_price( $order->get_shipping_total() ) ); ?></td>
						</tr>
						<tr>
							<td colspan="3" class="bpm-a4-grand-total bpm-a4-total-label">Grand Total:</td>
							<td class="bpm-a4-grand-total bpm-a4-grand-total-val"><?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?></td>
						</tr>
					</tfoot>
				</table>

				<?php if ( 'yes' === $show_barcode ) : ?>
					<div class="bpm-a4-barcode-wrap">
						<?php echo bpm_generate_inline_barcode_svg( (string) $order_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $show_signature ) : ?>
					<div class="bpm-a4-sig-wrap">
						<div class="bpm-a4-sig-col">Customer Signature</div>
						<div class="bpm-a4-sig-col">Authorized Signatory</div>
					</div>
				<?php endif; ?>

				<?php if ( $footer_note ) : ?>
					<div class="bpm-a4-footer-note">
						<?php echo esc_html( $footer_note ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</body>
	</html>
	<?php
	exit;
}