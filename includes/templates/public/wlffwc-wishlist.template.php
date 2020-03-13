<?php
/**
 * Wishlist products listing template.
 *
 * @version 1.0.0
 * @package WishListFFWC
 */

/**
 * Template variables:
 *
 * @var $wishlist_title String Wishlist Title. User display name is appended.
 * @var $wishlist_items Array All items in the user's wishlist.
 * @var $wishlist_page_url String Wishlist page URL.
 * @var $no_products_text String A text to display when the wishlist is empty.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div id="wlffwc-wishlist-wrapper">
	<div class="wlffwc-wc-alert woocommerce"></div>
	<h2><?php echo esc_html( stripslashes( $wishlist_title ) ); ?></h2>
	<table class="wlffwc-wishlist-tbl cart">
		<thead>
			<tr>
				<th>&nbsp;</th>
				<th><?php esc_html_e( 'Product', 'wishlist-feature-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Unit Price', 'wishlist-feature-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Stock Status', 'wishlist-feature-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'wishlist-feature-for-woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( ! empty( $wishlist_items ) ) :
				foreach ( $wishlist_items as $w_item ) :
					?>
					<tr class="wishlist-product-<?php echo esc_attr( $w_item['product_object']->get_id() ); ?> wishlist-item"  data-product_id="<?php echo esc_attr( $w_item['product_object']->get_id() ); ?>">
					<?php if ( apply_filters( 'wlffwc_shortcode_display_thumbnail', true, $w_item ) ) : ?>
						<td class="product-thumbnail not-mobile-display">
							<a href="<?php echo esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $w_item['product_object']->get_id() ) ) ); ?>">
								<?php echo $w_item['product_object']->get_image( 'woocommerce_thumbnail' ); ?>
							</a>
						</td>
					<?php endif; ?>
						<td class="product-name">
							<span class="mobile-th"><?php esc_html_e( 'Product', 'wishlist-feature-for-woocommerce' ); ?></span>
							<div class="product-thumbnail mobile-display">
								<a href="<?php echo esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $w_item['product_object']->get_id() ) ) ); ?>">
									<?php echo $w_item['product_object']->get_image( 'woocommerce_thumbnail' ); ?>
								</a>
							</div>
							<a href="<?php echo esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $w_item['product_object']->get_id() ) ) ); ?>">
							<?php echo esc_html( $w_item['product_object']->get_title() ); ?>
							</a>
						</td>
					<?php if ( apply_filters( 'wlffwc_shortcode_display_price', true, $w_item ) ) : ?>
						<td class="product-price">
							<span class="mobile-th"><?php esc_html_e( 'Unit Price', 'wishlist-feature-for-woocommerce' ); ?></span> 
							<?php echo wc_price( wc_get_price_to_display( $w_item['product_object'] ) ); ?>
							<br />
							<?php if ( $w_item['product_object']->get_price() > $w_item['price'] ) : ?>
							<span class="price-increase">
								<?php
								printf(
									/* translators: Price change in % */
									__( 'Price increased by %s%% since added.' ),
									round( ( ( $w_item['product_object']->get_price() - $w_item['price'] ) / $w_item['price'] ) * 100 )
								);
								?>
							</span>
							<?php endif; ?>
							<?php if ( $w_item['product_object']->get_price() < $w_item['price'] ) : ?>
							<span class="price-decrease">
								<?php
								printf(
									/* translators: Price change in % */
									__( 'Price dropped by %s%% since added.' ),
									round( ( ( $w_item['price'] - $w_item['product_object']->get_price() ) / $w_item['price'] ) * 100 )
								);
								?>
							</span>
							<?php endif; ?>
						</td>
					<?php endif; ?>
					<?php if ( apply_filters( 'wlffwc_shortcode_display_stock', true, $w_item ) ) : ?>
						<td class="product-stock-status">
							<span class="mobile-th"><?php esc_html_e( 'Stock Status', 'wishlist-feature-for-woocommerce' ); ?></span> 
							<?php echo wc_get_stock_html( $w_item['product_object'] ); // WPCS: XSS ok. ?>
						</td>
						<?php endif; ?>
						<td class="product-actions">
							<span class="mobile-th"><?php esc_html_e( 'Actions', 'wishlist-feature-for-woocommerce' ); ?></span>
							<?php
								/* translators: Date since added */
								echo '<span class="date-added">' . sprintf( __( 'Added on: %s', 'wishlist-feature-for-woocommerce' ), date_i18n( get_option( 'date_format' ), strtotime( $w_item['added'] ) ) ) . '</span>';
							?>
							<?php if ( $w_item['product_object']->is_in_stock() ) : ?>
								<p class="product woocommerce add_to_cart_inline"><a href="<?php echo add_query_arg( 'wlffwc_remove_after_adding_cart', $w_item['product_object']->get_id(), do_shortcode( '[add_to_cart_url id="' . $w_item['product_object']->get_id() . '"]' ) ); ?>" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart wlffwc-wishlist-add-to-cart" data-product_id="<?php echo $w_item['product_object']->get_id(); ?>" data-product_sku="woo-belt" aria-label="Add Belt to your cart" rel="nofollow">Add to cart</a></p>
							<?php endif; ?>
							<a href="#" class="remove-from-wishlist"><?php esc_html_e( 'Remove', 'wishlist-feature-for-woocommerce' ); ?></a>
						</td>
					</tr>
					<?php
					endforeach;
				endif;
			?>
			<tr class="no-data-found-row <?php echo esc_attr( $hide_no_data_row ); ?>">
				<td colspan="<?php echo esc_attr( apply_filters( 'wlffwc_wishlist_colspan', 5 ) ); ?>"><?php echo esc_html( $no_products_text ); ?></td>
			</tr>
		</tbody>
	</table>
</div>
