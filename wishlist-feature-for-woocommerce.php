<?php
/**
 * Plugin Name: WishList Feature For WooCommerce
 * Plugin URI: https://example.com/
 * Description: The users can create wishlist of their favorite products. Currently, supports one wishlist per user and simple products only.
 * Version: 1.0.0
 * Author: Uday Kokitkar
 * Author URI: https://example.com
 * Text Domain: wishlist-feature-for-woocommerce
 * Domain Path: /languages/
 * License: GPL
 * WC tested up to: 3.9.2
 *
 * @package WishListFFWC
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WLFFWC_PLUGIN_FILE' ) ) {
	define( 'WLFFWC_PLUGIN_FILE', __FILE__ );
}

// Include the main plugin class.
if ( ! class_exists( 'Wishlist_Feature_For_Woocommerce', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wishlist-feature-for-woocommerce.php';
}

/**
 * Returns the main instance of the plugin.
 *
 * @since  1.0.0
 * @return WishListFFWC
 */
function WishListFFWC() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return Wishlist_Feature_For_Woocommerce::instance();
}

/*
|
| We are not initiating the plugin instance on 'plugins_loaded' hook because register activation hook is not getting triggered on plugin activation if initiated on plugins_loaded. We have a plan to separate out registration hook in the upcoming releases.
|
 */
// add_action(
// 'plugins_loaded',
// function() {
// Let's start. Who let the dogs out. Woof, woof, woof, woof, woof.
// $GLOBALS['wlffwc_instance'] = WishListFFWC();
// }
// );

/**
 * Load autoloader.
 *
 * The new packages and autoloader require PHP 5.6+.
 */
if ( version_compare( PHP_VERSION, '5.6.0', '>=' ) && function_exists( 'spl_autoload_register' ) ) {
	require_once __DIR__ . '/includes/class-wlffwc-autoloader.php';

	// Let's start. Who let the dogs out. Woof, woof, woof, woof, woof.
	$GLOBALS['wlffwc_instance'] = WishListFFWC();
}
