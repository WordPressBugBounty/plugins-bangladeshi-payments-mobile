<?php
/**
 * General Settings and Analytics Page (Enterprise Clean UI/UX Edition).
 *
 * @package Bangladeshi_Payments_Mobile
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return allowed tags for inline SVG escaping.
 *
 * @return array
 */
function bpm_allowed_svg_html() {
	return array(
		'svg'    => array(
			'xmlns'               => true,
			'viewbox'             => true,
			'preserveaspectratio' => true,
			'class'               => true,
			'style'               => true,
			'width'               => true,
			'height'              => true,
		),
		'path'   => array(
			'd'    => true,
			'fill' => true,
		),
		'rect'   => array(
			'width'  => true,
			'height' => true,
			'x'      => true,
			'y'      => true,
			'rx'     => true,
			'fill'   => true,
		),
		'circle' => array(
			'cx'   => true,
			'cy'   => true,
			'r'    => true,
			'fill' => true,
		),
	);
}

/**
 * Fetch total sales aggregated for all custom mobile gateways.
 * Accepts orders with 'completed' or 'wc-completed' status across both CPT and HPOS.
 *
 * @param array $gateway_ids List of mobile gateway IDs.
 * @return array
 */
function bpm_get_all_gateway_sales( $gateway_ids = array( 'bkash', 'nagad', 'rocket', 'upay' ) ) {
	$cache_key = 'bpm_sales_summary_totals_v4';
	$cached    = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	$totals = array_fill_keys( $gateway_ids, 0.0 );

	foreach ( $gateway_ids as $slug ) {
		$possible_methods = array( $slug, 'bpm_' . $slug );

		$orders = wc_get_orders( array(
			'payment_method' => $possible_methods,
			'status'         => array( 'completed', 'wc-completed' ),
			'limit'          => -1,
			'return'         => 'objects',
		) );

		if ( ! empty( $orders ) ) {
			$sum = 0.0;
			foreach ( $orders as $order ) {
				if ( $order instanceof \WC_Order ) {
					$sum += (float) $order->get_total();
				}
			}
			$totals[ $slug ] = $sum;
		}
	}

	set_transient( $cache_key, $totals, 5 * MINUTE_IN_SECONDS );
	return $totals;
}

/**
 * Render General Page UI.
 */
function bangladeshi_payments_render_general_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'bangladeshi-payments-mobile' ) );
	}

	$allowed_gateways = array( 'bkash', 'nagad', 'rocket', 'upay' );
	$all_gateways     = function_exists( 'WC' ) && WC()->payment_gateways ? WC()->payment_gateways->payment_gateways() : array();
	$sales_data       = bpm_get_all_gateway_sales( $allowed_gateways );
	$grand_total      = array_sum( $sales_data );

	$active_gateways_count = 0;
	foreach ( $allowed_gateways as $slug ) {
		if ( isset( $all_gateways[ $slug ] ) && 'yes' === $all_gateways[ $slug ]->enabled ) {
			$active_gateways_count++;
		}
	}

	$gateway_meta = array(
		'bkash'  => array(
			'color'  => '#E2136E',
			'bg'     => '#fdf2f8',
			'border' => '#fbcfe8',
			'label'  => 'bKash',
		),
		'nagad'  => array(
			'color'  => '#ED1C24',
			'bg'     => '#fef2f2',
			'border' => '#fecaca',
			'label'  => 'Nagad',
		),
		'rocket' => array(
			'color'  => '#8C3494',
			'bg'     => '#faf5ff',
			'border' => '#f3e8ff',
			'label'  => 'Rocket',
		),
		'upay'   => array(
			'color'  => '#004B87',
			'bg'     => '#f0f9ff',
			'border' => '#bae6fd',
			'label'  => 'Upay',
		),
	);

	$my_plugins = array(
		array(
			'name' => 'Awesome Blocks for Gutenberg',
			'desc' => 'High-conversion interactive blocks collection for WordPress Full Site Editing.',
			'url'  => 'https://wordpress.org/plugins/awesome-block/',
			'icon' => 'dashicons-block-default',
		),
		array(
			'name' => 'Awesome Invoice & Packing Slips',
			'desc' => 'Generate printable PDF invoices, delivery notes, and shipping slips for WooCommerce.',
			'url'  => 'https://wordpress.org/plugins/awesome-invoice-delivery-notes-packing-slips/',
			'icon' => 'dashicons-media-document',
		),
		array(
			'name' => 'Awesome Widgets for Elementor',
			'desc' => 'Comprehensive pack of creative addons, sliders, and showcase layouts for Elementor.',
			'url'  => 'https://wordpress.org/plugins/awesome-widgets/',
			'icon' => 'dashicons-welcome-widgets-menus',
		),
		array(
			'name' => 'Ultimate CF7 Integration & Analytics',
			'desc' => 'Database submissions viewer, conversion rate analytics, and CSV export for CF7.',
			'url'  => 'https://wordpress.org/plugins/nahian-ultimate-integration-for-contact-form-7-and-elementor/',
			'icon' => 'dashicons-chart-line',
		),
	);

	$plugin_version = defined( 'BPM_VERSION' ) ? BPM_VERSION : '1.0.0';
	?>

	<div class="wrap bpm-dashboard-shell">
		<!-- Hero Header -->
		<div class="bpm-hero-banner">
			<div class="bpm-hero-content">
				<h1 class="bpm-hero-title">
					<span><?php esc_html_e( 'Bangladeshi Payments Mobile', 'bangladeshi-payments-mobile' ); ?></span>
					<span class="bpm-hero-version">v<?php echo esc_html( $plugin_version ); ?></span>
				</h1>
				<p class="bpm-hero-desc">
					<?php esc_html_e( 'Enterprise manual mobile banking suite for bKash, Nagad, Rocket, and Upay. Complete with real-time fee calculation, QR code rendering, and automated TrxID audit reporting.', 'bangladeshi-payments-mobile' ); ?>
				</p>
			</div>
			<div class="bpm-hero-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ); ?>" class="bpm-hero-btn-light">
					<span class="dashicons dashicons-admin-generic bpm-hero-icon"></span>
					<span><?php esc_html_e( 'WooCommerce Settings', 'bangladeshi-payments-mobile' ); ?></span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=bangladeshi-payments-mobile&tab=reports' ) ); ?>" class="bpm-hero-btn-primary">
					<span class="dashicons dashicons-analytics bpm-hero-icon"></span>
					<span><?php esc_html_e( 'Transaction Reports', 'bangladeshi-payments-mobile' ); ?></span>
				</a>
			</div>
		</div>

		<!-- Metric Aggregate KPI Strip -->
		<div class="bpm-stats-grid">
			<div class="bpm-stat-tile">
				<div class="bpm-stat-icon-wrapper bpm-stat-icon-blue">
					<span class="dashicons dashicons-money-alt bpm-stat-dashicon"></span>
				</div>
				<div class="bpm-stat-meta">
					<small><?php esc_html_e( 'Aggregated Revenue (Mobile Wallets)', 'bangladeshi-payments-mobile' ); ?></small>
					<strong><?php echo wp_kses_post( wc_price( $grand_total ) ); ?></strong>
				</div>
			</div>
			<div class="bpm-stat-tile">
				<div class="bpm-stat-icon-wrapper bpm-stat-icon-emerald">
					<span class="dashicons dashicons-yes-alt bpm-stat-dashicon"></span>
				</div>
				<div class="bpm-stat-meta">
					<small><?php esc_html_e( 'Gateway Health Status', 'bangladeshi-payments-mobile' ); ?></small>
					<strong>
						<?php
						/* translators: %d: number of active gateways currently enabled */
						printf( esc_html__( '%d of 4 Online', 'bangladeshi-payments-mobile' ), absint( $active_gateways_count ) );
						?>
					</strong>
				</div>
			</div>
		</div>

		<!-- 4 Real-Brand Gateway Cards -->
		<div class="bpm-gateway-row">
			<?php 
			foreach ( $allowed_gateways as $slug ) :
				$info        = $gateway_meta[ $slug ];
				$wc_gateway  = isset( $all_gateways[ $slug ] ) ? $all_gateways[ $slug ] : null;
				$is_enabled  = $wc_gateway && 'yes' === $wc_gateway->enabled;
				$acc_number  = $wc_gateway ? $wc_gateway->get_option( 'account_number', '' ) : '';
				$acc_type    = $wc_gateway ? ucfirst( $wc_gateway->get_option( 'account_type', 'personal' ) ) : 'Personal';
				$volume      = isset( $sales_data[ $slug ] ) ? $sales_data[ $slug ] : 0.0;
				$manage_link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $slug );
				
				$logo_url = defined( 'BPM_PLUGIN_URL' ) 
					? BPM_PLUGIN_URL . 'assets/img/' . $slug . '.png' 
					: plugins_url( 'assets/img/' . $slug . '.png', dirname( __FILE__ ) );
				?>
				<div class="bpm-gateway-card">
					<div>
						<div class="bpm-gateway-header">
							<div class="bpm-gateway-logo-box" style="background: <?php echo esc_attr( $info['bg'] ); ?>; border-color: <?php echo esc_attr( $info['border'] ); ?>;">
								<img 
									src="<?php echo esc_url( $logo_url ); ?>" 
									alt="<?php echo esc_attr( $info['label'] ); ?>" 
									class="bpm-gateway-logo-img bpm-gateway-logo-<?php echo esc_attr( $slug ); ?>" 
									width="40" 
									height="40" 
									loading="lazy"
								/>
							</div>
							<span class="bpm-status-chip <?php echo $is_enabled ? 'bpm-status-active' : 'bpm-status-disabled'; ?>">
								<span class="bpm-status-dot"></span>
								<?php echo $is_enabled ? esc_html__( 'Active', 'bangladeshi-payments-mobile' ) : esc_html__( 'Disabled', 'bangladeshi-payments-mobile' ); ?>
							</span>
						</div>

						<div class="bpm-gateway-body">
							<h3><?php echo esc_html( $info['label'] ); ?></h3>
							<span class="bpm-gateway-account-tag"><?php echo esc_html( $acc_type ); ?> &bull; <?php echo $acc_number ? esc_html( $acc_number ) : esc_html__( 'No number set', 'bangladeshi-payments-mobile' ); ?></span>

							<div class="bpm-gateway-revenue-box">
								<span><?php esc_html_e( 'Total Revenue', 'bangladeshi-payments-mobile' ); ?></span>
								<strong><?php echo wp_kses_post( wc_price( $volume ) ); ?></strong>
							</div>
						</div>
					</div>

					<div class="bpm-gateway-footer">
						<span class="bpm-gateway-footer-label">
							<?php echo esc_html( $info['label'] ); ?> Core
						</span>
						<a href="<?php echo esc_url( $manage_link ); ?>" class="bpm-gateway-config-link">
							<span><?php esc_html_e( 'Configure', 'bangladeshi-payments-mobile' ); ?></span>
							<svg class="bpm-gateway-config-svg" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Main Content Split Deck -->
		<div class="bpm-split-deck">
			<!-- Left Column: Setup & Portfolio -->
			<div>
				<!-- Setup Guide -->
				<div class="bpm-panel-card">
					<div class="bpm-panel-header">
						<h2 class="bpm-panel-title">
							<span class="dashicons dashicons-flag bpm-panel-title-icon-blue"></span>
							<?php esc_html_e( 'Quick-Start Setup Guide', 'bangladeshi-payments-mobile' ); ?>
						</h2>
						<span class="bpm-panel-subtitle">
							<?php esc_html_e( 'Simple 3-step setup', 'bangladeshi-payments-mobile' ); ?>
						</span>
					</div>
					<div class="bpm-panel-body">
						<div class="bpm-steps-row">
							<div class="bpm-instruction-box">
								<div class="bpm-instruction-step">1</div>
								<h4><?php esc_html_e( 'Configure Wallet', 'bangladeshi-payments-mobile' ); ?></h4>
								<p><?php esc_html_e( 'Open gateway settings, set your account number, and choose Personal or Agent.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<div class="bpm-instruction-box">
								<div class="bpm-instruction-step">2</div>
								<h4><?php esc_html_e( 'Set Transfer Fees', 'bangladeshi-payments-mobile' ); ?></h4>
								<p><?php esc_html_e( 'Enable auto cash-out charges (e.g. 1.85%) so users pay the exact amount.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
							<div class="bpm-instruction-box">
								<div class="bpm-instruction-step">3</div>
								<h4><?php esc_html_e( 'Verify Orders', 'bangladeshi-payments-mobile' ); ?></h4>
								<p><?php esc_html_e( 'Verify TrxID from order details and approve processing status effortlessly.', 'bangladeshi-payments-mobile' ); ?></p>
							</div>
						</div>

						<div class="bpm-hpos-notice">
							<div class="bpm-hpos-notice-content">
								<span class="dashicons dashicons-info bpm-hpos-icon"></span>
								<span class="bpm-hpos-text">
									<?php esc_html_e( 'All 4 gateways fully support WooCommerce HPOS (High-Performance Order Storage).', 'bangladeshi-payments-mobile' ); ?>
								</span>
							</div>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=advanced&section=features' ) ); ?>" class="bpm-hpos-link">
								<?php esc_html_e( 'Check HPOS Status &rarr;', 'bangladeshi-payments-mobile' ); ?>
							</a>
						</div>
					</div>
				</div>

				<!-- Ecosystem Plugins Portfolio -->
				<div class="bpm-panel-card">
					<div class="bpm-panel-header">
						<h2 class="bpm-panel-title">
							<span class="dashicons dashicons-star-empty bpm-panel-title-icon-amber"></span>
							<?php esc_html_e( 'Other Free Plugins by Abdullah Nahian', 'bangladeshi-payments-mobile' ); ?>
						</h2>
						<a href="https://profiles.wordpress.org/nahian91/#content-plugins" target="_blank" rel="noopener noreferrer" class="bpm-hpos-link">
							<?php esc_html_e( 'View All on WP.org &rarr;', 'bangladeshi-payments-mobile' ); ?>
						</a>
					</div>
					<div class="bpm-panel-body">
						<?php foreach ( $my_plugins as $plugin ) : ?>
							<div class="bpm-addon-row">
								<div class="bpm-addon-left">
									<div class="bpm-addon-icon-box">
										<span class="dashicons <?php echo esc_attr( $plugin['icon'] ); ?>"></span>
									</div>
									<div class="bpm-addon-text">
										<strong><?php echo esc_html( $plugin['name'] ); ?></strong>
										<span><?php echo esc_html( $plugin['desc'] ); ?></span>
									</div>
								</div>
								<div>
									<a href="<?php echo esc_url( $plugin['url'] ); ?>" class="button button-secondary bpm-addon-btn" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'Install Free', 'bangladeshi-payments-mobile' ); ?>
									</a>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- Right Column: Donation, Rating & Support -->
			<div>
				<!-- SupportKori Featured Donation -->
				<div class="bpm-donation-panel">
					<div class="bpm-donation-heart">
						<span class="dashicons dashicons-heart"></span>
					</div>
					<h3 class="bpm-donation-title">
						<?php esc_html_e( 'Support Continued Development', 'bangladeshi-payments-mobile' ); ?>
					</h3>
					<p class="bpm-donation-desc">
						<?php esc_html_e( 'If this plugin helps your business collect payments securely, consider sending a small contribution via SupportKori to support regular updates.', 'bangladeshi-payments-mobile' ); ?>
					</p>
					<a href="https://www.supportkori.com/nahian28" target="_blank" rel="noopener noreferrer" class="bpm-donation-btn">
						<?php esc_html_e( 'Support on SupportKori', 'bangladeshi-payments-mobile' ); ?>
					</a>
				</div>

				<!-- Review Box -->
				<div class="bpm-review-panel">
					<div class="bpm-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
					<h3 class="bpm-review-title">
						<?php esc_html_e( 'Loving Bangladeshi Payments?', 'bangladeshi-payments-mobile' ); ?>
					</h3>
					<p class="bpm-review-desc">
						<?php esc_html_e( 'Reviews help other WooCommerce store owners discover this plugin and keep development active.', 'bangladeshi-payments-mobile' ); ?>
					</p>
					<a href="https://wordpress.org/support/view/plugin-reviews/bangladeshi-payments-mobile?rate=5#new-post" class="button button-primary bpm-review-btn" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Leave a 5-Star Review', 'bangladeshi-payments-mobile' ); ?>
					</a>
				</div>

				<!-- Direct Support & Channels -->
				<div class="bpm-panel-card">
					<div class="bpm-panel-header">
						<h2 class="bpm-panel-title">
							<span class="dashicons dashicons-format-chat bpm-panel-title-icon-blue"></span>
							<?php esc_html_e( 'Direct Support & Contact', 'bangladeshi-payments-mobile' ); ?>
						</h2>
					</div>
					<div class="bpm-panel-body" style="padding: 6px 20px;">
						<ul class="bpm-contact-list">
							<li class="bpm-contact-row">
								<span class="bpm-contact-info">
									<span class="dashicons dashicons-email bpm-contact-icon"></span>
									<span>nahiansylhet@gmail.com</span>
								</span>
								<button type="button" class="bpm-copy-button" onclick="bpmQuickCopy('nahiansylhet@gmail.com', this)">
									<?php esc_html_e( 'Copy', 'bangladeshi-payments-mobile' ); ?>
								</button>
							</li>
							<li class="bpm-contact-row">
								<span class="bpm-contact-info">
									<span class="dashicons dashicons-whatsapp bpm-contact-icon-whatsapp"></span>
									<span>+8801686195607</span>
								</span>
								<button type="button" class="bpm-copy-button" onclick="bpmQuickCopy('+8801686195607', this)">
									<?php esc_html_e( 'Copy', 'bangladeshi-payments-mobile' ); ?>
								</button>
							</li>
							<li class="bpm-contact-row">
								<span class="bpm-contact-info">
									<span class="dashicons dashicons-admin-site bpm-contact-icon"></span>
									<span>devnahian.com</span>
								</span>
								<a href="https://devnahian.com" class="bpm-copy-button bpm-visit-link" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Visit', 'bangladeshi-payments-mobile' ); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	function bpmQuickCopy(text, btnElement) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(function() {
				var original = btnElement.innerText;
				btnElement.innerText = "Copied!";
				btnElement.style.background = "#dcfce7";
				btnElement.style.color = "#15803d";
				btnElement.style.borderColor = "#86efac";
				setTimeout(function() {
					btnElement.innerText = original;
					btnElement.style.background = "";
					btnElement.style.color = "";
					btnElement.style.borderColor = "";
				}, 2000);
			});
		}
	}
	</script>
	<?php
}