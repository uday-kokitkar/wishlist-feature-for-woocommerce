<?php
/**
 * Wishlist Feature for WooCommerce.
 *
 * @package WishListFFWC
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class to add extra links on plugins page.
 */
class WLFFWC_Admin_Plugins_Links {


	/**
	 * The single instance of the class.
	 *
	 * @var WLFFWC_Admin_Plugins_Links
	 */
	protected static $_instance = null;

	/**
	 * WLFFWC_Admin_Plugins_Links Instance.
	 *
	 * @static
	 * @return WLFFWC_Admin_Plugins_Links.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'plugin_action_links_' . WLFFWC_PLUGIN_BASENAME, array( $this, 'add_links' ) );
	}

	/**
	 * Initiates the class functions.
	 *
	 * @static
	 * @return void.
	 */
	public static function init() {
		self::instance();
	}

	/**
	 * Adds additional links on plugins listing page.
	 *
	 * @since 1.0.0
	 * @param array $links Array of links.
	 */
	public function add_links( $links ) {

		$links['settings'] = '<a href="' . esc_url( admin_url( '/admin.php?page=wishlist-ffwc-settings' ) ) . '">' . __( 'Settings', 'wishlist-feature-for-woocommerce' ) . '</a>';

		return $links;
	}
}
