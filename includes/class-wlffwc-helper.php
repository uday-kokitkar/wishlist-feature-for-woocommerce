<?php
/**
 * This class for commin functions used in the plugin.
 *
 * @package WishListFFWC
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class for WishList plugin Settings page.
 */
class WLFFWC_Helper {

	/**
	 * To check whether script debug is set to true. If yes, then return '/dev' string to add in the asset URL.
	 *
	 * @static
	 * @return string Empty or '/dev'.
	 */
	public static function dev_script_debug() {
		$dev = '';
		if ( SCRIPT_DEBUG ) {
			$dev = 'dev/';
		}
		return apply_filters( 'wlffwc_dev_script_debug', $dev );
	}

	/**
	 * Default settings values of the plugin.
	 *
	 * @static
	 * @return array Default settings.
	 */
	public static function get_default_global_settings() {
		return array(
			'wlffwc_wishlist_text' => __( 'wishlist', 'wishlist-feature-for-woocommerce' ),
			'wlffwc_wishlist_page' => 0,
		);
	}

	/**
	 * Updated settings values of the plugin.
	 *
	 * @static
	 * @return array Updated settings.
	 */
	public static function get_global_settings() {

		$db_settings  = get_option( 'wlffwc_global_settings', array() );
		$def_settings = self::get_default_global_settings();

		$all_settings = array();

		if ( ! empty( $db_settings ) ) {

			// Let's find out the difference first.
			$diff = array_diff( $db_settings, $def_settings );

			// Merge diff with DB settings to override DB settings over default settings.
			$db_diff_settings = array_merge( $diff, $db_settings );

			// Now, merge db_diff_settings with default settings to add any missed setting from the default settings which is not present in the DB settings.
			$all_settings = array_merge( $def_settings, $db_diff_settings );

		} else {
			$all_settings = $def_settings;
		}

		return apply_filters( 'wlffwc_global_settings', $all_settings );
	}
}
