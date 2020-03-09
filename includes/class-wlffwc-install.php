<?php
/**
 * Wishlist Feature for WooCommerce.
 *
 * @package WishListFFWC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Installation actions.
 *
 * @since 1.0.0
 */
class WLFFWC_Install {

	/**
	 * Init.
	 */
	public static function init() {
		// Currently, not in use.
		// add_action( 'admin_init', array( __CLASS__, 'install' ) );
	}

	/**
	 * Install actions.
	 */
	public static function install() {
		delete_transient( 'wlffwc_installing' );
		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'wlffwc_installing' ) ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'wlffwc_installing', 'yes', MINUTE_IN_SECONDS * 10 );

		self::create_tables();
		self::update_plugin_version();
		self::update_db_version();
		self::create_pages();
		// flush_rewrite_rules();

		// To flush rewrite rules on wc my account page once.
		update_option( 'wlffwc_settings_changed', true );

		delete_transient( 'wlffwc_installing' );

		do_action( 'wlffwc_installed' );
	} // install()

	/**
	 * Update plugin version to current.
	 *
	 * @static
	 * @since 1.0.0
	 */
	private static function update_plugin_version() {
		update_option( 'wlffwc_version', WishListFFWC()->version );
	}

	/**
	 * Update DB version to current.
	 *
	 * @static
	 * @since 1.0.0
	 *
	 * @param string|null $version New DB version or null.
	 */
	private static function update_db_version( $version = null ) {
		update_option( 'wlffwc_db_version', is_null( $version ) ? WishListFFWC()->version : $version );
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * @static
	 * @since 1.0.0
	 *
	 * Tables:
	 *      wlffwc_list - A table for storing list name, privacy and other related meta.
	 *      wlffwc_list_details - A table with all the products added in the wishlist.
	 */
	private static function create_tables() {

		global $wpdb;

		$collate = self::get_wp_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$wpdb->hide_errors();

		/*
		|
		| wlffwc_list table
		|
		*/
		$wlffwc_list = $wpdb->prefix . 'wlffwc_list';
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$wlffwc_list';" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wlffwc_list_tbl_q  = "
			CREATE TABLE IF NOT EXISTS {$wlffwc_list} (
								ID int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
								user_id int(11) NOT NULL,
								wishlist_name text,
								privacy tinyint(1) default 0,
								added timestamp default CURRENT_TIMESTAMP,
								INDEX user_id (user_id)
							) $collate;
							";
			@dbDelta( $wlffwc_list_tbl_q );
		}

		/*
		|
		| wlffwc_list_details table
		|
		*/
		$wlffwc_list_details = $wpdb->prefix . 'wlffwc_list_details';
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$wlffwc_list_details';" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wlffwc_list_details_tbl_q  = "
			CREATE TABLE IF NOT EXISTS {$wlffwc_list_details} (
								ID int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
								product_id int(11) NOT NULL,
								wishlist_id int(11) NOT NULL,
								price decimal(9,3) default NULL,
								added timestamp default CURRENT_TIMESTAMP,
								UNIQUE wishlist_product (wishlist_id, product_id)
							) $collate;
							";
			@dbDelta( $wlffwc_list_details_tbl_q );
		}
	} // create_tables()

	/**
	 * Gets the default charset and collate for the MySQL database.
	 *
	 * @static
	 * @since 1.0.0
	 *
	 * @return string $charset_collate charset and collate for the MySQL
	 * database.
	 */
	protected static function get_wp_charset_collate() {
		global $wpdb;
		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		return $charset_collate;
	} // get_wp_charset_collate()

	/**
	 * Creates wishlist listing page.
	 *
	 * @static
	 * @since 1.0.0
	 *
	 * @return void.
	 */
	protected static function create_pages() {
		$plugin_settings = WishListFFWC()->get_plugin_settings();

		if ( 0 == $plugin_settings['wlffwc_wishlist_page'] ) {
			$page_id = wp_insert_post(
				array(
					'post_title' => 'Wishlist',
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_content' => '[wlffwc_wishlist]',
				)
			);
			$plugin_settings['wlffwc_wishlist_page'] = $page_id;
			update_option( 'wlffwc_global_settings', $plugin_settings );
		}
	} // create_pages()
}
