jQuery(function(a){if("undefined"==typeof wlffwc_wishlist)return!1;function t(){a(document).on("click",".wlffwc-add-to-wishlist-wrap .add-to-wishlist-link",{WLFFWCWishlist:this},this.addToWishlist).on("adding_to_cart","body",{WLFFWCWishlist:this},this.adding_to_cart).on("added_to_cart","body",{WLFFWCWishlist:this},this.added_to_cart).on("click",".remove-from-wishlist",{WLFFWCWishlist:this},this.remove_from_wishlist)}t.prototype.addToWishlist=function(t){var i=a(this),s={product_id:i.attr("data-product_id"),wlffwc_add_nonce:wlffwc_wishlist.wlffwc_add_nonce,action:wlffwc_wishlist.actions.add_to_wishlist_action};return t.preventDefault(),a(document.body).trigger("before_adding_to_wishlist"),a.ajax({type:"POST",url:wlffwc_wishlist.ajax_url,data:s,dataType:"json",beforeSend:function(){c.block(i)},complete:function(){c.unblock(i)},success:function(t){var o=t.success,e=t.message;a(".wlffwc-add-to-wishlist-wrap").html(e),!0===o&&a(document.body).trigger("after_adding_to_wishlist",[i,s])}}),!1},t.prototype.adding_to_cart=function(t,o,e){o.hasClass("wlffwc-wishlist-add-to-cart")&&(e.wlffwc_remove_after_adding_cart=o.attr("data-product_id"))},t.prototype.added_to_cart=function(t,o,e,i){a(i).closest("tr").remove();var s=a("#wlffwc-wishlist-wrapper .wlffwc-wc-alert.woocommerce");0===s.length?s.html(wlffwc_wishlist.labels.added_to_cart_message).fadeIn():s.fadeOut(300,function(){s.html(wlffwc_wishlist.labels.added_to_cart_message).fadeIn()}),c.maybe_show_no_data_message()},t.prototype.remove_from_wishlist=function(t){var i=a(this).closest("tr"),s={product_id:i.attr("data-product_id"),wlffwc_remove_nonce:wlffwc_wishlist.wlffwc_remove_nonce,action:wlffwc_wishlist.actions.remove_from_wishlist_action};return t.preventDefault(),a(document.body).trigger("before_removing_from_wishlist"),a.ajax({type:"POST",url:wlffwc_wishlist.ajax_url,data:s,dataType:"json",beforeSend:function(){c.block(i)},complete:function(){c.unblock(i)},success:function(t){var o=t.success,e=t.message;a(".wlffwc-add-to-wishlist-wrap").html(e),!0===o&&(i.remove(),c.maybe_show_no_data_message(),a(document.body).trigger("after_removing_from_wishlist",[i,s]))}}),!1},t.prototype.maybe_show_no_data_message=function(){0==a("#wlffwc-wishlist-wrapper .wishlist-item").length&&a("#wlffwc-wishlist-wrapper .no-data-found-row").removeClass("hide")},t.prototype.block=function(t){void 0!==a.fn.block&&t.fadeTo("400","0.6").block({message:null,overlayCSS:{background:"transparent url("+wlffwc_wishlist.ajax_loader+") no-repeat center",backgroundSize:"40px 40px",opacity:1}})},t.prototype.unblock=function(t){void 0!==a.fn.unblock&&t.stop(!0).css("opacity","1").unblock()};var c=new t});