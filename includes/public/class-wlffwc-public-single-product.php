<?php
/**
 * WishList Feature for WooCommerce.
 *
 * @package WishListFFWC
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WLFFWC_Public_Single_Product' ) ) {
	/**
	 * To load on a single product page.
	 *
	 * @class WLFFWC_Public_Single_Product
	 */
	class WLFFWC_Public_Single_Product {

		/**
		 * The single instance of the class.
		 *
		 * @var WLFFWC_Public_Single_Product
		 */
		protected static $_instance = null;

		/**
		 * Main WLFFWC_Public_Single_Product Instance.
		 *
		 * Ensures only one instance of WLFFWC_Public_Single_Product is loaded or can be loaded.
		 *
		 * @static
		 * @return WLFFWC_Public_Single_Product - Main instance.
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
			add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'add_to_wishlist_button' ) );
		}

		/**
		 * Initiates the class functions.
		 *
		 * @static
		 * @return void.
		 */
		public static function init() {
			self::instance();
			WishListFFWC()->wishlist_handler();
		}

		/**
		 * Add to wishlist button on a single product page.
		 *
		 * @return void.
		 */
		public function add_to_wishlist_button() {
			global $product;

			/*
			|
			| Currently, we support only 'simple' type products.
			|
			 */
			$supported_product_types = apply_filters( 'wlffwc_supported_product_types', array( 'simple' ), $product );

			if ( in_array( $product->get_type(), $supported_product_types ) ) {

				$wlffwc_wishlist_handler = WLFFWC_Wishlist_Handler::instance();

				$is_in_wishlist = $wlffwc_wishlist_handler->is_product_in_wishlist( $product->get_id(), get_current_user_id() );

				$global_settings = WishListFFWC()->get_plugin_settings();

				if ( $is_in_wishlist ) {
					$browse_label = apply_filters( 'wlffwc_browse_label', __( 'Browse', 'wishlist-feature-for-woocommerce' ) . ' ' . stripslashes( $global_settings['wlffwc_wishlist_text'] ) );
					$browse_link = apply_filters( 'wlffwc_browse_link', get_permalink( $global_settings['wlffwc_wishlist_page'] ) );
					$pre_browse_text = apply_filters( 'wlffwc_pre_browse_text', __( 'This product is already in your', 'wishlist-feature-for-woocommerce' ) . ' ' . stripslashes( $global_settings['wlffwc_wishlist_text'] ) ) . '!';

					wc_get_template(
						'/public/wlffwc-browse-wishlist.template.php',
						array(
							'browse_label' => $browse_label,
							'browse_link' => $browse_link,
							'pre_browse_text' => $pre_browse_text,
						),
						'wishlist-feature-for-woocommerce',
						WLFFWC_TEMPLATE_PATH
					);
				} else {
					$link_label = apply_filters( 'wlffwc_add_to_label', __( 'Add to', 'wishlist-feature-for-woocommerce' ) . ' ' . stripslashes( $global_settings['wlffwc_wishlist_text'] ) );

					wc_get_template(
						'/public/wlffwc-add-to-wishlist-single.template.php',
						array(
							'product'   => $product,
							'link_label' => $link_label,
						),
						'wishlist-feature-for-woocommerce',
						WLFFWC_TEMPLATE_PATH
					);

					// add_filter( 'wlffwc_localize_wishlist', array( $this, 'localize_browse_template' ) );
				}

				wp_enqueue_script( 'wlffwc-wishlist-script' );

				wp_localize_script( 'wlffwc-wishlist-script', 'wlffwc_wishlist', WishListFFWC()->wishlist_handler()->localize_data() );
			}
		}

		/**
		 * Not in use. Initially decided to show browse wishlist template after adding the product in the wishlist. However, browse template has message that "the product is alread in the list", which does not make sense to show after adding a product.
		 *
		 * @param  array $data Data to localize.
		 * @return [type]       [description]
		 */
		public function localize_browse_template( $data ) {
			$data['browse_wishlist_template'] = 123;
			return $data;
		}
	}
}
