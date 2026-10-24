=== Bangladeshi Payments Mobile – MFS Gateway, QR Code, Advance COD Protection, Thermal POS Invoicing & WhatsApp Suite ===
Contributors: nahian91
Donate link: https://www.supportkori.com/nahian28
Tags: woocommerce, bkash, nagad, rocket, bangladesh
Requires at least: 5.6
Tested up to: 7.1
Stable tag: 1.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept bKash, Nagad, Rocket, and Upay in WooCommerce with offline QR codes, Advance COD fraud shield, 80mm thermal POS slips, and WhatsApp tools.

== Description ==
**Bangladeshi Payments Mobile** is an advanced, production-tested E-Commerce payment and operations plugin crafted specifically for **Bangladeshi merchants, F-commerce shops, and online retailers**. It bridges the gap between manual Mobile Financial Services (MFS) collections and automated store management.

Instead of dealing with unverified payments, manual address errors, and high parcel return rates (COD fraud), this plugin equips your store with enterprise-grade defense mechanisms, automated fee calculations, and streamlined order fulfillment tools.

### Support the Development
Building and maintaining comprehensive plugins takes extensive time and dedicated support. If this plugin protects your business from fraud or accelerates your workflow, please consider supporting its ongoing maintenance!

* [Support my work on SupportKori](https://www.supportkori.com/nahian28)
* Leave a 5-star rating on WordPress.org — it helps other store owners discover this tool!

---

### Key Features & Enterprise Modules

#### 1. 4-in-1 Universal MFS Integration
* **Multi-Gateway Support**: Accept manual payments from **bKash**, **Nagad**, **Rocket**, and **Upay** simultaneously.
* **Account Type Configuration**: Define each gateway explicitly as *Personal (Send Money)*, *Agent (Cash Out)*, or *Merchant (Payment)* with tailored user instructions.
* **Dynamic Fee Calculation**: Automatically compute and add cash-out/transfer service charges directly to the cart total upon gateway selection.

#### 2. Offline QR Code Generator
* **Instant Scannable SVGs**: Automatically generates clean, high-resolution vector QR codes right inside the payment gateway block during checkout.
* **Error-Free Transactions**: Allows customers to scan the code using their mobile wallet apps to instantly transfer the exact amount without manual typing mistakes.

#### 3. Advance COD (Delivery Prepayment) Engine
* **Parcel Return Mitigation**: Combat fake Cash on Delivery (COD) orders by requiring customers to prepay delivery fees via MFS before confirming the order.
* **Tiered Shipping Rules**: Configure separate prepayment rates for **Inside Dhaka** and **Outside Dhaka** fulfillment zones.
* **Conditional Thresholds**: Enforce advance delivery collection only when the cart subtotal exceeds a custom target amount (e.g., orders above ৳1,000).

#### 4. Fraud Shield & Rate Limiting Suite
* **Duplicate TrxID Blocker**: Verifies submitted Transaction IDs in real-time against database order metadata to instantly block reused or fake TrxID submissions.
* **Phone & IP Blacklist**: Maintain an automated blacklist of fraudulent buyers, serial cancelers, and spam bots based on phone numbers and IP addresses.
* **Order Velocity Rate-Limiter**: Prevent automated bot floods by limiting the maximum number of orders a single customer phone number can place within an hourly window.

#### 5. Checkout & Address Optimization
* **Granular Field Toggles**: Instantly show or hide unnecessary fields like Company Name, Postcode, Address Line 2, Email, and Order Notes.
* **64 Zilla Dropdown & Auto-Shipping**: Replaces standard states with an accurate administrative list of all 64 districts in Bangladesh and auto-selects inside/outside Dhaka shipping.
* **Strict 11-Digit BD Phone Validation**: Enforces strict validation ensuring customer numbers start with valid operator prefixes (`013`-`019`) and contain exactly 11 numeric digits.

#### 6. WhatsApp Suite & Live Chat
* **Thank You Page Action Button**: Instantly renders a 1-tap WhatsApp chat button after checkout, prompting buyers to share their payment confirmation screenshot.
* **Dynamic Message Shortcodes**: Personalize chats automatically using `{order_id}`, `{order_total}`, `{trx_id}`, and `{payment_method}` tags.
* **Product Page Quick Order CTA**: Embeds a direct "Order via WhatsApp" button on single product pages and catalog archives, pre-loading item details and quantities.
* **Persistent Floating Widget**: Displays a clean floating live chat bubble across store pages with responsive device display toggles.

#### 7. Thermal POS Invoicing & Order Management
* **Print-Ready Documents**: Generate professional A4 tax invoices and standardized 80mm POS thermal slips directly from order action buttons.
* **Custom Order Columns**: Adds a brand-tinted *Mobile Payment* column to the WooCommerce orders list table with gateway badges, payer phone numbers, and 1-click TrxID copy pills.
* **Automated Order Cancellation**: Schedules automatic cleanup of unpaid pending MFS orders after a designated time threshold to restore held inventory.

== Technical Architecture ==
* **Pure Internal CSS**: Zero external stylesheet dependencies, ensuring completely conflict-free styling across all WordPress themes.
* **HPOS Compatible**: Fully optimized for WooCommerce High-Performance Order Storage for lightning-fast database performance.

== More Plugins by Abdullah Nahian ==
Enhance your WordPress experience with other popular plugins by [nahian91](https://profiles.wordpress.org/nahian91/#content-plugins):

* [Awesome Widgets for Elementor](https://wordpress.org/plugins/awesome-widgets-for-elementor/) – Add stunning widgets and professional design elements to Elementor.
* [Awesome Car Rental & Booking](https://wordpress.org/plugins/awesome-car-rental-booking/) – A complete solution for car rentals, fleet management, and bookings.
* [Awesome Invoice, Delivery Notes & Packing Slips](https://wordpress.org/plugins/awesome-invoice/) – Professional invoicing and shipping document generator for WooCommerce orders.
* [Awesome Progress Bar](https://wordpress.org/plugins/awesome-progress-bar/) – Sleek and customizable progress bar elements for WordPress sites.
* [Awesome Food Ordering, Delivery & Pickup](https://wordpress.org/plugins/awesome-food-ordering-delivery-pickup-solution/) – Professional system for restaurants and food delivery services.
* [Awesome Team Widgets](https://wordpress.org/plugins/awesome-team-widgets/) – Showcase your team with flexible and stylish Elementor widgets.
* [Awesome Blocks for Gutenberg](https://wordpress.org/plugins/awesome-block/) – Create interactive FAQs with accordions, schema markup, and search functionality.
* [Awesome Delivery Scheduler for WooCommerce](https://wordpress.org/plugins/awesome-delivery-scheduler/) – Allows customers to select preferred delivery dates and time slots during checkout.
* [Infinity FAQ Schema & Accordion](https://wordpress.org/plugins/infinity-simple-faq/) – Simple accordion FAQs powered by clean SEO schema markup.
* [Ultimate CF7 Integration with Analytics, Reports & Export](https://wordpress.org/plugins/ultimate-cf7-for-elementor/) – Advanced Contact Form 7 integration featuring data analytics, tracking reports, and CSV exports.

Visit [https://devnahian.com](https://devnahian.com) for more plugins and tutorials.

== Support & Contact ==
For technical support or bug reports, feel free to reach out:
* Website: [devnahian.com](https://devnahian.com)
* Email: nahiansylhet@gmail.com
* WhatsApp/Phone: +8801686195607

== Installation ==
1. Upload the plugin files to `/wp-content/plugins/bangladeshi-payments-mobile/` or install via WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen.
3. Go to the **BD Payments** menu in your WordPress admin to configure your gateways and security rules.

== Frequently Asked Questions ==
= Which payment methods are supported? =
Supports **bKash**, **Nagad**, **Rocket**, and **Upay**.

= Can I require advance delivery fees for COD? =
Yes, the Advance COD module allows you to collect delivery charges upfront via mobile wallet.

= Are duplicate Transaction IDs prevented? =
Yes, the Fraud Shield module rejects reused TrxID submissions in real-time.

= Can I print thermal POS receipts? =
Yes, built-in thermal invoice engine supports 80mm POS receipt printers as well as full-page A4 tax invoices.

= Is WooCommerce HPOS supported? =
Yes, the plugin is fully compatible with WooCommerce High-Performance Order Storage.

== Changelog ==
= 1.6 =
* Bumped tested compatibility up to WordPress 7.1.
* Enhanced 2-column checkout layout styling and responsiveness.
* Added granular display switches for WhatsApp direct ordering on single and archive pages.
* Refactored TrxID duplicate checks to use cached WooCommerce order queries.
* Full WPCS and Plugin Check compliance overhaul.

= 1.5.1 =
* Refactored entire codebase with Pure Internal CSS architecture.
* Introduced Advance COD delivery prepayment module & Fraud Shield.
* Added 64 District dropdowns, thermal POS invoicing, and a modern 6-tab dashboard.

= 1.0.0 =
* Initial release with QR code support and transaction report features.

== License ==
GPLv2 or later. See [License URI](https://www.gnu.org/licenses/gpl-2.0.html).