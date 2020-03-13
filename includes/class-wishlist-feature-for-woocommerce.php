<?php
/**
 * Wishlist Feature for WooCommerce.
 *
 * @package WishListFFWC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main WishListFFWC Class.
 *
 * @class Wishlist_Feature_For_Woocommerce
 */
final class Wishlist_Feature_For_Woocommerce {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Plugin's name in the dashboard.
	 *
	 * @var string
	 */
	public $plugin_name = 'WishList Feature For WooCommerce';


	/**
	 * Plugin settings to be applied site wide.
	 *
	 * @var array
	 */
	private $global_settings = array();

	/**
	 * The single instance of the class.
	 *
	 * @var WishListFFWC
	 */
	protected static $_instance = null;

	/**
	 * Main WishListFFWC Instance.
	 *
	 * Ensures only one instance of WishListFFWC is loaded or can be loaded.
	 *
	 * @static
	 * @see WishListFFWC()
	 * @return WishListFFWC - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		if ( function_exists( 'wc_doing_it_wrong' ) ) {
			wc_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cloning is forbidden.', 'wishlist-feature-for-woocommerce' ) ), '1.0.0' );
		}
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		if ( function_exists( 'wc_doing_it_wrong' ) ) {
			wc_doing_it_wrong( __FUNCTION__, esc_html( __( 'Unserializing instances of this class is forbidden.', 'wishlist-feature-for-woocommerce' ) ), '1.0.0' );
		}
	}

	/**
	 * WishListFFWC Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {

		$this->define( 'WLFFWC_ABSPATH', dirname( WLFFWC_PLUGIN_FILE ) . '/' );
		$this->define( 'WLFFWC_INCLUDES_PATH', dirname( WLFFWC_PLUGIN_FILE ) . '/includes/' );
		$this->define( 'WLFFWC_PLUGIN_BASENAME', plugin_basename( WLFFWC_PLUGIN_FILE ) );
		$this->define( 'WLFFWC_VERSION', $this->version );
		$this->define( 'WLFFWC_NOTICE_MIN_PHP_VERSION', '5.6.20' );
		$this->define( 'WLFFWC_NOTICE_MIN_WP_VERSION', '5.0' );
		$this->define( 'WLFFWC_NOTICE_MIN_WC_VERSION', '3.8' );
		$this->define( 'WLFFWC_PLUGIN_URL', plugin_dir_url( WLFFWC_PLUGIN_FILE ) );
		$this->define( 'WLFFWC_TEMPLATE_PATH', untrailingslashit( WLFFWC_INCLUDES_PATH ) . '/templates' );
		$this->define( 'WLFFWC_LANG_DIR', WLFFWC_ABSPATH . '/languages/' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		/**
		 * Class autoloader.
		 */
		include_once WLFFWC_INCLUDES_PATH . 'class-wlffwc-autoloader.php';

		/**
		 * Class Helper functions..
		 */
		include_once WLFFWC_INCLUDES_PATH . 'class-wlffwc-helper.php';

		/**
		 * Class for installation purpose.
		 */
		include_once WLFFWC_INCLUDES_PATH . 'class-wlffwc-install.php';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		register_activation_hook( WLFFWC_PLUGIN_FILE, array( 'WLFFWC_Install', 'install' ) );

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), -1 );

		add_action( 'admin_notices', array( $this, 'build_dependencies_notice' ) );

		add_action( 'init', array( $this, 'init' ), 0 );

	}

	/**
	 * Init WishListFFWC when WordPress Initialises.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Before init action.
		do_action( 'before_wlffwc_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();

		// We are dependent on WooCommerce. If either of them is not active, do not run our code.
		if ( ! defined( 'WC_PLUGIN_FILE' ) ) {
			return;
		}

		/**
		|
		| Classes loaded for the dashboard only.
		|
		 */
		if ( $this->is_request( 'admin' ) ) {
			$this->admin_init();
		}

		/**
		|
		| Classes loaded for the frontend and for ajax requests.
		|
		 */
		if ( $this->is_request( 'frontend' ) ) {

			/**
			|
			| Classes loaded for Shortcodes.
			|
			 */
			WLFFWC_Shortcode_Wishlist::init();

			$this->frontend_init();
		}

		// Init action.
		do_action( 'wlffwc_init' );
	}

	/**
	 * Initiate classes required for dashboard.
	 */
	public function admin_init() {
		WLFFWC_Admin_Settings::init();
		WLFFWC_Admin_Plugins_Links::init();
	}

	/**
	 * Initiate classes required for frontend and ajax.
	 */
	public function frontend_init() {
		WLFFWC_Public_Single_Product::init();
		WLFFWC_Public_Wishlist_Wc_Menu_Item::init();
	}

	/**
	 * When WP has loaded all plugins, trigger the `wlffwc_loaded` hook.
	 *
	 * This ensures `wlffwc_loaded` is called only after all other plugins
	 * are loaded.
	 */
	public function on_plugins_loaded() {
		do_action( 'wlffwc_loaded' );
	}

	/**
	 * Show admin notices for dependencies.
	 *
	 * @return void.
	 */
	public function build_dependencies_notice() {

		if ( ! version_compare( PHP_VERSION, WLFFWC_NOTICE_MIN_PHP_VERSION, '>=' ) ) {
			// Show notice if php version is less than required.
			$current = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
			?>
			<div class="notice notice-error">
				<h3>
				<?php
				printf(
					/* translators: Plugin name, Minimum PHP version, Current PHP version, Plugin name */
					__(
						'The <strong>%1$s</strong> requires PHP version %2$s or higher. Because you are using an unsupported version of PHP (%3$s), the <strong>%4$s</strong> plugin will not initialize. Please contact your hosting company to upgrade to PHP.'
					),
					$this->plugin_name,
					WLFFWC_NOTICE_MIN_PHP_VERSION,
					$current,
					$this->plugin_name
				);
				?>
				</h3>
			</div>
			<?php
		} elseif ( ! defined( 'WC_PLUGIN_FILE' ) ) {
			// Show notice if WooCommerce is not active.
			?>
			<div class="notice notice-error">
				<p>
				<?php
				$install_wc_url = admin_url( 'plugin-install.php?s=woocommerce&tab=search' );
				printf(
					/* translators: plugin name, Installation URL. */
					__(
						'The <strong>%1$s</strong> requires WooCommerce to be activated ! <a href="%2$s">Install / Activate WooCommerce</a>'
					),
					$this->plugin_name,
					$install_wc_url
				);
				?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {

		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! $this->is_rest_api_request();
			// case 'frontend_only':
			// return ( ! is_admin() && ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_CRON' )  && ! $this->is_rest_api_request() );
			// case 'frontend_and_ajax':
			// return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! $this->is_rest_api_request();
		}
	}



	/**
	 * Load Localisation files.
	 * Locales found in:
	 *      - WLFFWC_LANG_DIR/wishlist-feature-for-woocommerce-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wishlist-feature-for-woocommerce', false, plugin_basename( dirname( WLFFWC_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Returns true if the request is a REST API request.
	 *
	 * @return bool
	 */
	public function is_rest_api_request() {

		// Considering this discussion, using constant here. We are also keeping further logic to check REST request in case REST_REQUEST is not defined.
		// https://github.com/WP-API/WP-API/issues/926
		// if ( ! defined( 'REST_REQUEST' ) ) {
		// return false;
		// }

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$r_prefix            = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $r_prefix ) );

		return $is_rest_api_request;
	}

	/**
	 * A global settings for this plugin.
	 *
	 * @return array The plugin settings.
	 */
	public function get_plugin_settings() {
		if ( empty( $this->global_settings ) ) {
			$this->global_settings = WLFFWC_Helper::get_global_settings();
		}
		return $this->global_settings;
	}

	/**
	 * Wishlist handler is one of the important class we have. To access its instance, we have created a function in the plugin class.
	 *
	 * @return WLFFWC_Wishlist_Handler WLFFWC_Wishlist_Handler class instance.
	 */
	public function wishlist_handler() {
		return WLFFWC_Wishlist_Handler::instance();
	}
}
