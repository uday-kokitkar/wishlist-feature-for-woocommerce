<?php
/**
 * WishList Feature for WooCommerce.
 *
 * @package WishListFFWC
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WLFFWC_Shortcode_Wishlist' ) ) {
	/**
	 * Shortcode to list down the products added in the wishlist.
	 *
	 * @class WLFFWC_Shortcode_Wishlist
	 */
	class WLFFWC_Shortcode_Wishlist {

		/**
		 * The single instance of the class.
		 *
		 * @var WLFFWC_Shortcode_Wishlist
		 */
		protected static $_instance = null;

		/**
		 * Main WLFFWC_Shortcode_Wishlist Instance.
		 *
		 * Ensures only one instance of WLFFWC_Shortcode_Wishlist is loaded or can be loaded.
		 *
		 * @static
		 * @return WLFFWC_Shortcode_Wishlist - Main instance.
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
			WishListFFWC()->wishlist_handler();
			add_shortcode( 'wlffwc_wishlist', array( $this, 'wishlist_callback' ) );
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
		 * Callback function for [wlffwc_wishlist].
		 *
		 * @param  array $atts Shortcode attributes.
		 * @return string       Output for [wlffwc_wishlist].
		 */
		public function wishlist_callback( $atts ) {

			$atts = shortcode_atts(
				array(
					'wishlist_id' => get_query_var( 'wishlist_id', false ),
				),
				$atts
			);

			/**
			 * Shortcode attributes.
			 *
			 * @var array.
			 */
			extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			if ( ! $wishlist_id ) {
				$wishlist_handler = WLFFWC_Wishlist_Handler::instance();
				$wishlist_id = $wishlist_handler->get_wishlist_id( get_current_user_id() );
			}

			if ( $wishlist_id ) {
				$wishlist_items = $wishlist_handler->get_wishlist_items( $wishlist_id );

				if ( ! empty( $wishlist_items ) ) {

					$supported_post_status = apply_filters( 'wlffwc_supported_post_status', array( 'publish' ) );

					foreach ( $wishlist_items as $key => $w_item ) {
						$product_obj = wc_get_product( $w_item['product_id'] );

						if ( ! in_array( $product_obj->get_status(), $supported_post_status ) ) {
							unset( $wishlist_items[ $key ] );
						} else {
							$wishlist_items[ $key ]['product_object'] = $product_obj;
						}
					}
				}

				$global_settings = WishListFFWC()->get_plugin_settings();
			}

			wp_enqueue_style( 'wlffwc-wishlist-style' );
			wp_enqueue_script( 'wlffwc-wishlist-script' );

			wp_localize_script( 'wlffwc-wishlist-script', 'wlffwc_wishlist', WishListFFWC()->wishlist_handler()->localize_data() );

			$userdata = get_userdata( get_current_user_id() );

			$wishlist_shortcode_data = apply_filters(
				'wlffwc_wishlist_shortcode_data',
				array(
					'wishlist_title' => ucfirst( $global_settings['wlffwc_wishlist_text'] ) . ' ' . __( 'of' ) . ' ' . $userdata->display_name,
					'wishlist_items' => $wishlist_items,
					'no_products_text' => __( 'No products added to the', 'wishlist-feature-for-woocommerce' ) . ' ' . stripslashes( $global_settings['wlffwc_wishlist_text'] ),
					'hide_no_data_row' => ( count( $wishlist_items ) > 0 ) ? 'hide' : '',
				)
			);

			return wc_get_template(
				'/public/wlffwc-wishlist.template.php',
				$wishlist_shortcode_data,
				'wishlist-feature-for-woocommerce',
				WLFFWC_TEMPLATE_PATH
			);
		}
	}
}
