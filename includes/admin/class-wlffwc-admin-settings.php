<?php
/**
 * Wishlist Feature for WooCommerce.
 *
 * @package WishListFFWC
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class for WishList plugin Settings page.
 */
class WLFFWC_Admin_Settings {

	/**
	 * The single instance of the class.
	 *
	 * @var WLFFWC_Admin_Settings
	 */
	protected static $_instance = null;

	/**
	 * WLFFWC_Admin_Settings Instance.
	 *
	 * @static
	 * @return WLFFWC_Admin_Settings.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Initialize the actions.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu_page' ), 10 );

		wp_register_style( 'wlffwc-global-settings-style', WLFFWC_PLUGIN_URL . 'assets/css/' . WLFFWC_Helper::dev_script_debug() . 'admin/wlffwc-admin-main.css', array(), WishListFFWC()->version, 'all' );

		// add_action( 'woocommerce_flush_rewrite_rules', array( $this, 'trigger_flush' ) );
		add_action( 'after_switch_theme', array( $this, 'trigger_flush' ) );
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
	 * Adds settings menu page.
	 *
	 * @return void
	 */
	public function menu_page() {
		add_menu_page( 'WishList', 'WishList', 'manage_options', 'wishlist-ffwc-settings', array( $this, 'process_settings' ), WLFFWC_PLUGIN_URL . 'assets/images/wishlist-icon.png', 56 );
	} // menu_page()

	/**
	 * Process plugin settings.
	 *
	 * @return void
	 */
	public function process_settings() {
		$this->save_settings();

		$this->show_settings();
	} // process_settings()

	/**
	 * Display plugin settings.
	 *
	 * @return void.
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function show_settings() {

		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_style( 'wlffwc-global-settings-style' );
		?>
		<div class="wrap woocommerce">
			<div id="poststuffIE">
			<?php
				/*
				|
				| Variables to be used in the template file.
				|
				 */

				$default_settings   = WLFFWC_Helper::get_default_global_settings();
				$global_settings    = WLFFWC_Helper::get_global_settings();

				$no_page = new stdClass();
				$no_page->ID            = 0;
				$no_page->post_title    = __( '-- Select --', 'wishlist-feature-for-woocommerce' );
				$wp_pages = array(
					'-1' => $no_page,
				)
							+
							get_pages(
								array(
									'number' => 50,
								)
							);

				include WLFFWC_INCLUDES_PATH . 'templates/admin/wlffwc-global-settings.template.php';
			?>
			</div>
		</div>
		<?php
	} // show_settings()

	/**
	 * Save plugin settings.
	 *
	 * @return void
	 */
	public function save_settings() {

		if ( isset( $_POST['wlffwc_global_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wlffwc_global_settings_nonce'] ) ), 'wlffwc-global-settings-nonce' ) ) {

			$def_settings = WLFFWC_Helper::get_default_global_settings();

			$updated_settings = wp_parse_args( $_POST, $def_settings );

			/*
			|
			| Take out our settings only.
			|
			 */
			$updated_settings = array_filter(
				$updated_settings,
				function( $key ) {
					return strpos( $key, 'wlffwc_' ) === 0;
				},
				ARRAY_FILTER_USE_KEY
			);

			// We don't want to store nonce value.
			unset( $updated_settings['wlffwc_global_settings_nonce'] );

			/*
			|
			| We don't accept 'blank' values. 0 is not a blank value. Blank != PHP empty. Empty could be much more.
			|
			 */
			$updated_settings = array_filter(
				$updated_settings,
				function( $value ) {
					return ! is_null( $value ) && '' !== $value;
				}
			);

			update_option( 'wlffwc_global_settings', $updated_settings );

			update_option( 'wlffwc_settings_changed', true );

			wp_safe_redirect( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) );
			exit;
		}
	} // save_settings()

	/**
	 * We want to trigger rewrite rules flush because on WooCommerce deactivation and activation, our rules are not getting flushed.
	 *
	 * @return void.
	 */
	public function trigger_flush() {
		update_option( 'wlffwc_settings_changed', true );
	}
}
