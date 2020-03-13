<?php
/**
 * Add to wishlist button template
 *
 * @version 1.0.0
 * @package WishListFFWC
 */

/**
 * Template variables:
 *
 * @var $product object Current product.
 * @var $link_label string Add to wishlist text.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="wlffwc-add-to-wishlist-wrap">
	<a href="<?php echo esc_url( add_query_arg( 'wlffwc_add_to_wishlist', $product->get_id() ) ); ?>" rel="nofollow" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-product_type="<?php echo esc_attr( $product->get_type() ); ?>"  class="add-to-wishlist-link">
		<span><?php echo esc_attr( $link_label ); ?></span>
	</a>
</div>
