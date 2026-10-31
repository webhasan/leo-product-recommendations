/**
 * Popup Add buttons on block themes.
 *
 * WooCommerce removes its classic add to cart script on block themes when its own product
 * buttons are on the page. Without it, the popup's Add buttons would follow their link and
 * reload the page. This adds the product with WooCommerce's add_to_cart endpoint instead, and
 * fires the same added_to_cart event, so the popup works the same on every theme.
 * It does nothing when WooCommerce's script is on the page.
 *
 * @since 3.7.0
 */
(function ($) {
	"use strict";

	if (!$ || typeof lpr_popup_add === "undefined") {
		return;
	}

	$(document.body).on("click", "#lpr-modal .ajax_add_to_cart[data-product_id]", function (e) {
		// WooCommerce's own script is loaded: it handles the click.
		if (typeof window.wc_add_to_cart_params !== "undefined") {
			return;
		}

		const $button = $(this);
		const productId = $button.attr("data-product_id");
		if (!productId || $button.hasClass("is-disabled")) {
			return;
		}

		e.preventDefault();
		if ($button.hasClass("loading")) {
			return;
		}

		$button.removeClass("added").addClass("loading");

		const data = {
			product_id: productId,
			quantity: $button.attr("data-quantity") || 1,
			lpr_source: "popup",
		};
		// Same event WooCommerce fires before its AJAX add, so other scripts can add to the
		// request (Pro adds where the product was recommended, for its savings and analytics).
		$(document.body).trigger("adding_to_cart", [$button, data]);

		$.post(lpr_popup_add.wc_ajax_url.replace("%%endpoint%%", "add_to_cart"), data)
			.done(function (res) {
				if (!res) {
					return;
				}
				// Needs options or out of stock: WooCommerce sends the product page.
				if (res.error && res.product_url) {
					window.location = res.product_url;
					return;
				}
				$(document.body).trigger("added_to_cart", [res.fragments, res.cart_hash, $button]);
			})
			.always(function () {
				$button.removeClass("loading");
			});
	});
})(window.jQuery);
