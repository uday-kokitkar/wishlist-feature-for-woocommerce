<?php
/**
 * Wishlist Feature for WooCommerce.
 *
 * @package WishListFFWC
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WLFFWC_Wishlist_Handler' ) ) {
	/**
	 * Wishlist actions handler.
	 *
	 * @since 1.0.0
	 */
	class WLFFWC_Wishlist_Handler {
		/**
		 * The single instance of the class.
		 *
		 * @var WLFFWC_Wishlist_Handler
		 */
		protected static $_instance = null;

		/**
		 * WC Product.
		 *
		 * @var WC_Product
		 */
		protected $product = null;

		/**
		 * Current wishlist ID for the user.
		 *
		 * @var int
		 */
		protected $wishlist_id = 0;

		/**
		 * Main WLFFWC_Wishlist_Handler Instance.
		 *
		 * Ensures only one instance of WLFFWC_Wishlist_Handler is loaded or can be loaded.
		 *
		 * @static
		 * @return WLFFWC_Wishlist_Handler - Main instance.
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

			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

			add_action( 'wp_ajax_wlffwc_add_to_wishlist', array( $this, 'add' ) );
			add_action( 'wp_ajax_wlffwc_remove_from_wishlist', array( $this, 'remove' ) );

			add_action( 'woocommerce_add_to_cart', array( $this, 'remove_from_wishlist_after_add_to_cart' ) );
		}

		/**
		 * Load frontend scripts.
		 *
		 * @since 1.0.0
		 * @return void.
		 */
		public function load_scripts() {
			wp_register_style( 'wlffwc-wishlist-style', WLFFWC_PLUGIN_URL . 'assets/css/' . WLFFWC_Helper::dev_script_debug() . 'public/wlffwc-wishlist.css', array(), WishListFFWC()->version, 'all' );

			wp_register_script( 'wlffwc-wishlist-script', WLFFWC_PLUGIN_URL . 'assets/js/' . WLFFWC_Helper::dev_script_debug() . 'public/wlffwc-wishlist.js', array( 'jquery' ), WishListFFWC()->version, true );
		}

		/**
		 * Data to be localized in JS.
		 *
		 * @since 1.0.0
		 * @return array Localize data.
		 */
		public function localize_data() {

			$plugin_settings = WishListFFWC()->get_plugin_settings();

			return apply_filters(
				'wlffwc_localize_wishlist',
				array(
					'ajax_url'            => admin_url( 'admin-ajax.php', 'relative' ),
					'ajax_loader'         => WLFFWC_PLUGIN_URL . 'assets/images/ajax-loader.svg',
					'labels'              => array(
						'added_to_cart_message' => sprintf( '<div class="woocommerce-message" role="alert"><a href="%s" class="button wc-forward">%s</a> %s</div>', esc_url( wc_get_cart_url() ), __( 'View cart', 'wishlist-feature-for-woocommerce' ), __( 'A product has been added to your cart.', 'wishlist-feature-for-woocommerce' ) ),
					),
					'wlffwc_add_nonce'    => wp_create_nonce( 'wlffwc-add-ajax-' . get_current_user_id() ),
					'wlffwc_remove_nonce' => wp_create_nonce( 'wlffwc-remove-ajax-' . get_current_user_id() ),
					'actions'             => array(
						'add_to_wishlist_action'      => 'wlffwc_add_to_wishlist',
						'remove_from_wishlist_action' => 'wlffwc_remove_from_wishlist',
					),
					'plugin_settings'     => array(
						'browse_url'          => get_permalink( $plugin_settings['wlffwc_wishlist_page'] ),
						'wishlist_alt_string' => $plugin_settings['wlffwc_wishlist_text'],
					),
				)
			);
		}


		/**
		 * Makes an entry in the database for given user, if already not exists.
		 *
		 * @param  int $user_id User ID.
		 * @since  1.0.0
		 * @return int Wishlist ID. Returns an existing one or newly created.
		 */
		private function create_wishlist( $user_id = 0 ) {

			if ( 0 === $user_id ) {
				$user_id = get_current_user_id();
			}

			/*
			|
			| Default data.
			|
			 */
			$default_data = apply_filters(
				'wlffwc_default_wishlist_data',
				array(
					'user_id'       => $user_id,
					'wishlist_name' => __( 'My wishlist', 'wishlist-feature-for-woocommerce' ),
					'privacy'       => 0, // 0 is private. 1 for public. Currently, supports only two status.
				)
			);

			/*
			|
			| Let's validate the data.
			|
			 */
			$error_in_data = false;
			if ( ! (int) $default_data['user_id'] ) {
				$error_in_data = true;
			}

			$default_data['wishlist_name'] = ( ! empty( $default_data['wishlist_name'] ) ) ? strip_tags( $default_data['wishlist_name'] ) : __( 'My wishlist', 'wishlist-feature-for-woocommerce' );

			$default_data['privacy'] = ( in_array( $default_data['privacy'], array( 0, 1 ), true ) ) ? $default_data['privacy'] : 0;

			if ( $error_in_data ) {
				return new WP_Error( 'wlffwc', __( 'Wishlist data is not valid.', 'wishlist-feature-for-woocommerce' ) );
			}

			/*
			|
			| Now, we have validated the data. Time to insert in the database.
			|
			 */
			global $wpdb;

			$res = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO 
								{$wpdb->prefix}wlffwc_list 
									( `user_id`, `wishlist_name`, `privacy` ) 
								VALUES 
									( %d, %s, %d )",
					$default_data['user_id'],
					$default_data['wishlist_name'],
					$default_data['privacy']
				)
			);

			if ( $res ) {
				$this->wishlist_id = (int) $wpdb->insert_id;

				do_action( 'wlffwc_wishlist_created', $this->wishlist_id, $default_data );
			}

			return $this->wishlist_id;
		}

		/* === ADD METHODS === */

		/**
		 * Add a product in the wishlist.  Default call is from Ajax request.
		 *
		 * @since 1.0.0
		 * @param array $atts An array of parameters.
		 * @return array|int If ajax request, then returns response with the message. For non ajax requests, it is just a inserted row ID.
		 */
		public function add( $atts = array() ) {

			$row_id        = 0;
			$ajax_response = array(
				'success' => false,
				'message' => __( 'Something went wrong. Please refresh a page and try again.', 'wishlist-feature-for-woocommerce' ),
			);

			if ( empty( $atts ) ) {
				if ( isset( $_POST['wlffwc_add_nonce'] )
					&&
					wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wlffwc_add_nonce'] ) ), 'wlffwc-add-ajax-' . get_current_user_id() )
				) {
					$atts = $_POST;
				} else {
					// The request is not authorized.
					wp_send_json(
						$ajax_response
					);
				}
			}

			if ( empty( $atts ) ) {
				wp_send_json_error();
			}

			if ( ! get_current_user_id() ) {
				$ajax_response['message'] = __( 'Can not process the request for non logged users.', 'wishlist-feature-for-woocommerce' );
			} else {

				$product_id = $atts['product_id'];

				/*
				|
				| Let's check if the user has wishlist created, if not, create a new one.
				|
				 */
				$this->wishlist_id = $this->get_wishlist_id( get_current_user_id() );

				if ( ! $this->wishlist_id ) {
					$this->wishlist_id = $this->create_wishlist( get_current_user_id() );
				}

				/*
				|
				| Add the product in the wishlist.
				|
				 */

				$row_id = $this->add_product_to_wishlist( wc_get_product( $product_id ), $this->wishlist_id );

				if ( $row_id ) {

					$plugin_settings = WishListFFWC()->get_plugin_settings();

					$ajax_response['success'] = true;
					$ajax_response['message'] = sprintf(
						/* translators: Plugin settings page, wishlist text */
						__(
							'A product is successfully added! <a href="%1$s">Browse %2$s</a>'
						),
						get_permalink( $plugin_settings['wlffwc_wishlist_page'] ),
						$plugin_settings['wlffwc_wishlist_text']
					);
				}
			}

			// Response to ajax request.
			if ( ! empty( $_POST ) ) {
				wp_send_json(
					$ajax_response
				);
			} else {
				return $row_id;
			}
		}

		/**
		 * Makes an entry of product in the 'wlffwc_list_details' table.
		 *
		 * @param WC_Product $product  Product ID to be added. Does not support multiple products as of now.
		 * @param int        $wishlist_id Wishlist ID where products to be added.
		 *
		 * @return int Row ID.
		 */
		private function add_product_to_wishlist( WC_Product $product, $wishlist_id ) {

			global $wpdb;

			$res = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO 
								{$wpdb->prefix}wlffwc_list_details 
									( `product_id`, `wishlist_id`, `price` ) 
								VALUES 
									( %d, %d, %d )",
					$product->get_id(),
					$wishlist_id,
					wc_get_price_to_display( $product )
				)
			);

			if ( $res ) {
				do_action( 'wlffwc_wishlist_item_added', $wishlist_id, $product, (int) $wpdb->insert_id );
				return (int) $wpdb->insert_id;
			}
			return 0;
		}

		/* === REMOVE METHODS === */

		/**
		 * Remove an entry from the wishlist.
		 *
		 * @param array   $atts    Array of parameters; when not passed, parameters will be retrieved from $_REQUEST.
		 * @param boolean $is_ajax To check if this is the ajax request. Default is true.
		 *
		 * @since 1.0.0
		 * @return boolean True if removed.
		 */
		public function remove( $atts = array(), $is_ajax = true ) {
			$row_id        = 0;
			$ajax_response = array(
				'success' => false,
				'message' => __( 'Something went wrong. Please refresh a page and try again.', 'wishlist-feature-for-woocommerce' ),
			);

			if ( empty( $atts ) ) {
				if ( isset( $_POST['wlffwc_remove_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wlffwc_remove_nonce'] ) ), 'wlffwc-remove-ajax-' . get_current_user_id() ) ) {
					$atts = $_POST;
				} else {
					error_log( 'nonce failed' );
					// The request is not authorized.
					wp_send_json(
						$ajax_response
					);
				}
			}

			if ( empty( $atts ) ) {
				if ( $is_ajax ) {
					wp_send_json_error();
				} else {
					return $ajax_response;
				}
			}

			if ( ! get_current_user_id() ) {
				$ajax_response['message'] = __( 'Can not process the request for non logged users.', 'wishlist-feature-for-woocommerce' );
			} else {

				$product_id = $atts['product_id'];

				/*
				|
				| Let's check if the user has wishlist created.
				|
				 */
				$wishlist_id = $this->get_wishlist_id( get_current_user_id() );

				/*
				|
				| Remove product from the wishlist.
				|
				 */

				$res = $this->remove_product_from_wishlist( $product_id, $wishlist_id );

				if ( $res ) {

					$ajax_response['success'] = true;
					$ajax_response['message'] = __( 'A product is removed!' );
				}
			}

			// Response to ajax request.
			if ( $is_ajax ) {
				wp_send_json(
					$ajax_response
				);
			} else {
				return $row_id;
			}
		}

		/**
		 * Removes product from the wishlist.
		 *
		 * @param  int $product_id  An item to be removed.
		 * @param  int $wishlist_id Wishlist to be updated.
		 * @return boolean             True on success.
		 */
		private function remove_product_from_wishlist( $product_id, $wishlist_id ) {
			global $wpdb;

			$res = $wpdb->delete(
				$wpdb->prefix . 'wlffwc_list_details',
				array(
					'product_id'  => $product_id,
					'wishlist_id' => $wishlist_id,
				)
			);

			if ( $res ) {
				do_action( 'wlffwc_wishlist_item_removed', $product_id, $wishlist_id );
				return true;
			}
			return false;
		}

		/**
		 * Removes product from the wishlist after adding it to the cart from the wishlist shortcode.
		 *
		 * @return void.
		 */
		public function remove_from_wishlist_after_add_to_cart() {

			if ( isset( $_REQUEST['wlffwc_remove_after_adding_cart'] ) ) {

				$product_id = sanitize_text_field( wp_unslash( $_REQUEST['wlffwc_remove_after_adding_cart'] ) );

				// To confirm that we are going to remove the product that is added in the cart.
				if ( isset( $_REQUEST['product_id'] ) && $product_id === $_REQUEST['product_id'] ) {
					$this->remove( array( 'product_id' => $product_id ), false );
				}
			}
		}

		/* === GENERAL METHODS === */

		/**
		 * Returns wishlist ID of the user.
		 *
		 * @param  int $user_id User ID.
		 * @return int          Wishlist ID from the database, empty if not found.
		 */
		public function get_wishlist_id( $user_id = 0 ) {
			if ( 0 === $user_id ) {
				return 0;
			}
			global $wpdb;
			return $wpdb->get_var(
				$wpdb->prepare(
					"SELECT
						ID 
					FROM
						{$wpdb->prefix}wlffwc_list
					WHERE
						`user_id` = %d",
					$user_id
				)
			);
		}

		/**
		 * Check if the product exists in the wishlist.
		 *
		 * @since 1.0.0
		 *
		 * @param int $product_id Product id to check.
		 * @param int $user_id User ID to check the product against.
		 * @param int $wishlist_id Wishlist ID of the user.
		 *
		 * @return bool
		 */
		public function is_product_in_wishlist( $product_id, $user_id, $wishlist_id = 0 ) {

			$response = false;

			if ( empty( $wishlist_id ) ) {
				$wishlist_id = $this->get_wishlist_id( $user_id );
			}

			if ( ! empty( $wishlist_id ) ) {
				global $wpdb;
				$res = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT
							ID 
						FROM
							{$wpdb->prefix}wlffwc_list_details
						WHERE
							`wishlist_id` = %d
						AND 
							`product_id` = %d",
						$wishlist_id,
						$product_id
					)
				);

				if ( $res ) {
					$response = true;
				}
			}

			return apply_filters( 'wlffwc_is_product_in_wishlist', $response, $product_id, $user_id, $wishlist_id );
		}

		/**
		 * Items from the wishlist.
		 *
		 * @since 1.0.0
		 * @param  int $wishlist_id Wishlist ID to get the items.
		 * @return array            Items from the wishlist.
		 */
		public function get_wishlist_items( $wishlist_id ) {

			global $wpdb;

			$products = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT 
						product_id, price, added
					FROM
						{$wpdb->prefix}wlffwc_list_details
					WHERE
						`wishlist_id` = %d",
					$wishlist_id
				),
				ARRAY_A
			);

			return apply_filters( 'wlffwc_get_wishlist_items', $products, $wishlist_id );
		}
	}
}
