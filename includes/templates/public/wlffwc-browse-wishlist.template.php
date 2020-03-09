<?php
/**
 * Browse wishlist link template
 *
 * @version 1.0.0
 * @package WishListFFWC
 */

/**
 * Template variables:
 *
 * @var $pre_browse_text string A text to display just before browse link.
 * @var $browse_link     string Wishlist page URL.
 * @var $browse_label    string A text for the browse link.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="wlffwc-browse-wishlist"><?php echo esc_html( $pre_browse_text ); ?>
	<a href="
	<?php
	echo esc_url(
		$browse_link
	);
	?>
	" rel="nofollow" title="<?php echo esc_attr( $browse_label ); ?>">
		<span><?php echo esc_attr( $browse_label ); ?></span>
	</a>
</div>
