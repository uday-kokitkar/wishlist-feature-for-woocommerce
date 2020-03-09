<?php
/**
 * Global plugin settings template.
 *
 * @package WishListFFWC
 * @version 1.0.0
 */

/**
 * Template variables:
 *
 * @var $default_settings array Default global settings of the plugin.
 * @var $global_settings array Global settings of the plugin with user defined values.
 * @var $wp_pages array WordPress pages.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<form method="post" id="wlffwc_global_settings" action="" enctype="multipart/form-data" class="wlffwc-form">
	<div class="wrap">
		<h2 class = 'header'><?php esc_html_e( 'Wishlist Feature for WooCommerce', 'wishlist-feature-for-woocommerce' ); ?></h2>
		<div class="hr"></div>
		<table class = 'form-table' id = "settings-table">
			<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="wlffwc_wishlist_text"><?php esc_html_e( 'Wishlist text', 'wishlist-feature-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<input 
						   name = "wlffwc_wishlist_text"
						   id = "wlffwc_wishlist_text"
						   type = "text"
						   value = "<?php echo esc_attr( wp_unslash( $global_settings['wlffwc_wishlist_text'] ) ); ?>"
						   class = ""
						   placeholder = "<?php echo esc_attr( wp_unslash( $default_settings['wlffwc_wishlist_text'] ) ); ?>"
						  />
						<p class="description"><?php esc_html_e( 'A "wishlist" text. Changing this text will take effect on frontend only.', 'wishlist-feature-for-woocommerce' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="wlffwc_wishlist_page"><?php esc_html_e( 'Wishlist page', 'wishlist-feature-for-woocommerce' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<select name="wlffwc_wishlist_page" id="wlffwc_wishlist_page">
							<?php
							foreach ( $wp_pages as $w_page ) {
								$sel = '';
								if ( $w_page->ID == $global_settings['wlffwc_wishlist_page'] ) {
									$sel = ' selected="selected"';
								}
								echo '<option value="' . esc_attr( $w_page->ID ) . '"' . $sel . '>' . esc_html( $w_page->post_title ) . '</option>';
							}
							?>
						</select>
						<p class="description"><?php esc_html_e( 'A page with [wlffwc_wishlist] shortcode inserted.', 'wishlist-feature-for-woocommerce' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php do_action( 'wlffwc_after_global_settings' ); ?>
		<p class="submit">
			<button name="save" class="button-primary wlffwc-save-button" type="submit"><?php esc_html_e( 'Save changes', 'wishlist-feature-for-woocommerce' ); ?></button>
			<?php
				wp_nonce_field( 'wlffwc-global-settings-nonce', 'wlffwc_global_settings_nonce' );
			?>
		</p>
	</div>
</form>
