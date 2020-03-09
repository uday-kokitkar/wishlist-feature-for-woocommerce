jQuery( function( $ ) {

	// console.log( wlffwc_wishlist );

	if ( 'undefined' === typeof wlffwc_wishlist ) {
		return false;
	}

	/**
	 * WishList.
	 */
	var WLFFWCWishlist = function() {

		$( document )
			.on( 'click', '.wlffwc-add-to-wishlist-wrap .add-to-wishlist-link', { WLFFWCWishlist: this }, this.addToWishlist )
			.on( 'adding_to_cart', 'body', { WLFFWCWishlist: this }, this.adding_to_cart )
			.on( 'added_to_cart', 'body', { WLFFWCWishlist: this }, this.added_to_cart )
			.on( 'click', '.remove-from-wishlist', { WLFFWCWishlist: this }, this.remove_from_wishlist )
	};

	/**
	 * Sends request to add product to wishlist.
	 *
	 * @since 1.0.0
	 *
	 * @param jQuery object el Add to wishlist link.
	 */
	WLFFWCWishlist.prototype.addToWishlist = function( el ) {

		var that = $( this),
		    product_id = that.attr( 'data-product_id' ),
		    data = {
		        product_id: product_id,
		        wlffwc_add_nonce: wlffwc_wishlist.wlffwc_add_nonce,
		        action: wlffwc_wishlist.actions.add_to_wishlist_action,
		    };

		el.preventDefault();

		// console.log( data );

		$( document.body ).trigger( 'before_adding_to_wishlist' );

		$.ajax({
		    type: 'POST',
		    url: wlffwc_wishlist.ajax_url,
		    data: data,
		    dataType: 'json',
		    beforeSend: function(){
		        WLFFWCWishlistObj.block( that );
		    },
		    complete: function(){
		        WLFFWCWishlistObj.unblock( that );
		    },
		    success: function( response ) {
		        var response_result = response.success,
		            response_message = response.message;

		        // if( true === response_result ) {
		        	$( '.wlffwc-add-to-wishlist-wrap' ).html( response_message );
		            // console.log( 'yeh, true!' );
		        // }
		        if( true === response_result ) {
		        	$( document.body ).trigger('after_adding_to_wishlist', [ that, data ] );
		        }
		    }
		});

		return false;
	}; // ! addToWishlist
	
	/**
	 * Appends data to send to add to cart ajax request along with WC data.
	 *
	 * @since 1.0.0
	 *
	 * @param  object ev        Event object.
	 * @param  object button    jQuery add to cart button object.
	 * @param  object data      WC Ajax add to cart data.
	 *
	 * @return void.
	 */
	WLFFWCWishlist.prototype.adding_to_cart = function( ev, button, data ) {
		if( button.hasClass( 'wlffwc-wishlist-add-to-cart' ) ){
		    data.wlffwc_remove_after_adding_cart = button.attr( 'data-product_id' );
		}
	}; // ! adding_to_cart

	/**
	 * Remove item from the list after adding to the cart.
	 *
	 * @since 1.0.0
	 *
	 * @param  object ev        Event object.
	 * @param  object fragments WC Cart fragements.
	 * @param  string carthash  Cart hash value.
	 * @param  object button    jQuery add to cart button object.
	 *
	 * @return void.
	 */
	WLFFWCWishlist.prototype.added_to_cart = function( ev, fragments, carthash, button ) {
		$( button ).closest('tr').remove();
		var messages = $( '#wlffwc-wishlist-wrapper .wlffwc-wc-alert.woocommerce');

		if( messages.length === 0 ){
		    messages.html( wlffwc_wishlist.labels.added_to_cart_message ).fadeIn();
		} else {
		    messages.fadeOut( 300, function(){
		        messages.html( wlffwc_wishlist.labels.added_to_cart_message ).fadeIn();
		    } );
		}
		WLFFWCWishlistObj.maybe_show_no_data_message();
	}; // ! added_to_cart

	/**
	 * Removes item from the wishlist.
	 *
	 * @since 1.0.0
	 *
	 * @param  jQuery object el Remove element to remove the product from the list.
	 *
	 * @return void.
	 */
	WLFFWCWishlist.prototype.remove_from_wishlist = function( el ) {
		var that       = $( this ).closest( 'tr' ),
		    product_id = that.attr( 'data-product_id' ),
		    data       = {
					    	product_id          : product_id,
					        wlffwc_remove_nonce : wlffwc_wishlist.wlffwc_remove_nonce,
					        action              : wlffwc_wishlist.actions.remove_from_wishlist_action,
		    			 };

		el.preventDefault();

		$( document.body ).trigger( 'before_removing_from_wishlist' );

		$.ajax({
		    type: 'POST',
		    url: wlffwc_wishlist.ajax_url,
		    data: data,
		    dataType: 'json',
		    beforeSend: function(){
		        WLFFWCWishlistObj.block( that );
		    },
		    complete: function(){
		        WLFFWCWishlistObj.unblock( that );
		    },
		    success: function( response ) {
		        var response_result = response.success,
		            response_message = response.message;

		        // if( true === response_result ) {
		        	$( '.wlffwc-add-to-wishlist-wrap' ).html( response_message );
		        // }
		        if( true === response_result ) {
		        	that.remove();
		        	WLFFWCWishlistObj.maybe_show_no_data_message();
		        	$( document.body ).trigger('after_removing_from_wishlist', [ that, data ] );
		        }
		    }
		});

		return false;
	}; // ! remove_from_wishlist


	/************************
	|                       |
	|   UTILITY FUNCTIONS   |
	|                       |
	************************/

	/**
	 * Checks how many items in the list. If found zero, shows "no data" row.
	 *
	 * @since 1.0.0
	 *
	 * @return void.
	 */
	WLFFWCWishlist.prototype.maybe_show_no_data_message = function() {
		if ( $( '#wlffwc-wishlist-wrapper .wishlist-item' ).length == 0 ) {
			$( '#wlffwc-wishlist-wrapper .no-data-found-row' ).removeClass('hide');
		}
	}

	/**
	 * Block item if possible.
	 *
	 * @since 1.0.0
	 *
	 * @param item jQuery object
	 *
	 * @return void
	 */
	WLFFWCWishlist.prototype.block = function( item ) {
	    if( typeof $.fn.block != 'undefined' ) {
	        item.fadeTo( '400', '0.6' ).block( {
	            message: null,
	            overlayCSS : {
	                background    : 'transparent url(' + wlffwc_wishlist.ajax_loader + ') no-repeat center',
	                backgroundSize: '40px 40px',
	                opacity       : 1
	            }
	        } );
	    }
	} // ! block

	/**
	 * Unblock item if possible
	 *
	 * @since 1.0.0
	 *
	 * @param item jQuery object
	 *
	 * @return void
	 */
	WLFFWCWishlist.prototype.unblock = function( item ) {
	    if( typeof $.fn.unblock != 'undefined' ) {
	        item.stop( true ).css( 'opacity', '1' ).unblock();
	    }
	} // ! unblock

	var WLFFWCWishlistObj = new WLFFWCWishlist();
});
