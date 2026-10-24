<?php
/**
 * Admin Menu and UI Part Loader.
 *
 * @package Bangladeshi_Payments_Mobile
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Core Admin Menus & Layout Wrapper (Tabs controller)
require_once BPM_PLUGIN_DIR . 'admin/parts/bpm-main-menu.php';

// 2. Tab Contents
require_once BPM_PLUGIN_DIR . 'admin/parts/bpm-page-general.php';       // Tab 1: Overview & Analytics
require_once BPM_PLUGIN_DIR . 'admin/parts/bpm-invoice-settings.php';       // Tab 1: Overview & Analytics
require_once BPM_PLUGIN_DIR . 'admin/parts/bpm-page-checkout.php';      // Tab 2: Checkout, Buttons & Zilla
require_once BPM_PLUGIN_DIR . 'admin/parts/bpm-page-security.php';      // Tab 3: Duplicate TrxID & Advance COD
require_once BPM_PLUGIN_DIR . 'admin/parts/bpm-page-whatsapp.php';      // Tab 4: WhatsApp Confirmation & Tools

// 3. Separate Pages & Custom Columns
require_once BPM_PLUGIN_DIR . 'admin/parts/bpm-page-transaction.php';  // Submenu: Transaction Reports
require_once BPM_PLUGIN_DIR . 'admin/parts/bpm-menu-column.php';       // WooCommerce Order Table Columns