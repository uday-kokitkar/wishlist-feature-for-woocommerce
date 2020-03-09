<?php
/**
 * Wishlist Feature for WooCommerce.
 *
 * @package WishListFFWC
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * A woocommerce my account menu items.
 */
class WLFFWC_Public_Wishlist_Wc_Menu_Item {


	/**
	 * The single instance of the class.
	 *
	 * @var WLFFWC_Public_Wishlist_Wc_Menu_Item
	 */
	protected static $_instance = null;

	/**
	 * Wishlist endpoint name.
	 *
	 * @var string
	 */
	protected static $endpoint = 'wl-';

	/**
	 * WLFFWC_Public_Wishlist_Wc_Menu_Item Instance.
	 *
	 * @static
	 * @return WLFFWC_Public_Wishlist_Wc_Menu_Item.
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
	public function __construct() {

		$plugin_settings = WishListFFWC()->get_plugin_settings();

		// Endpoint should consist the string being used as 'wishlist' text.
		self::$endpoint .= sanitize_title( $plugin_settings['wlffwc_wishlist_text'] );

		// Actions used to insert a new endpoint.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		add_filter( 'woocommerce_account_menu_items', array( $this, 'wishlist_menu_item' ), 11, 1 );

		add_action( 'woocommerce_account_' . self::$endpoint . '_endpoint', array( $this, 'wishlist_item_content' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'wishlist_menu_item_icon' ) );

	}

	/**
	 * Heart icon to wishlist item menu in Strofront theme.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public function wishlist_menu_item_icon() {

		$menu_item_icon = 'body.theme-storefront ul li.woocommerce-MyAccount-navigation-link--' . self::$endpoint . ' a:before{
			content: "\f004"
			}';
		wp_add_inline_style( 'wlffwc-wishlist-style', $menu_item_icon );
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
	 * Registers a new endpoint for wishlist content.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );

		// This option is updated on update of global plugin settings. Refer WLFFWC_Admin_Settings class.
		if ( get_option( 'wlffwc_settings_changed' ) == true ) {
			// We are flushing rules here because 'wishlist' text is being used on wc account dashboard as well.
			flush_rewrite_rules();
			update_option( 'wlffwc_settings_changed', false );
		}
	}

	/**
	 * A new query var for wishlist item.
	 *
	 * @param array $vars Endpoints.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::$endpoint;

		return $vars;
	}

	/**
	 * Adds wishlist menu item tab on the WooCommerce account page.
	 *
	 * @since 1.0.0
	 * @param array $items Array of items.
	 */
	public function wishlist_menu_item( $items ) {

		$plugin_settings = WishListFFWC()->get_plugin_settings();

		// Remove the logout menu item.
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );

		// Insert our endpoint.
		$items[ self::$endpoint ] = ucfirst( stripslashes( $plugin_settings['wlffwc_wishlist_text'] ) );

		// Insert back the logout item.
		$items['customer-logout'] = $logout;

		return $items;
	}

	/**
	 * Wishlist item content. Used original wishlist shortcode.
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	public function wishlist_item_content() {
		echo apply_filters( 'wlffwc_wishlist_item_content', do_shortcode( '[wlffwc_wishlist]' ) );
	}
}
