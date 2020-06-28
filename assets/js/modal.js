(function ($) {
  //setup cart items in localstorage to exclude from recommendation
  function wpr_cart_items() {
    $.ajax({
      method: "GET",
      url: lc_ajax_modal.url,
      data: {
        action: "lc_get_cart_items",
        nonce: lc_ajax_modal.nonce,
      },
    }).done(function (data) {
      localStorage.setItem("wpr_cart_items", data);
    });
  }

  wpr_cart_items();

  $(document.body).on(
    "added_to_cart removed_from_cart wc_fragments_refreshed",
    function () {
      wpr_cart_items();
    }
  );

  //modal plugin
  $.fn.wprModal = function (options) {
    var settings = $.extend(
      {
        action: "open", // opton for modal open or close default: open
      },
      options
    );

    var that = this;

    // modal overlay
    var overlay = $('<div class="wpr-modal-overlay show"></div>');

    // opne modal
    function opneModal() {
      var scrollbarWidth =
        window.innerWidth - document.documentElement.clientWidth;

      that.addClass("show");

      $("body").css("paddingRight", scrollbarWidth);

      $("body").addClass("wpr-modal-opened").prepend(overlay);

      setTimeout(function () {
        that.addClass("fadeIn");
        overlay.addClass("fadeIn");
      }, 10);

      return false;
    }

    // close modal
    function closeModal() {
      that.removeClass("fadeIn");
      $(".wpr-modal-overlay").addClass("fadeIn");

      setTimeout(function () {
        that.removeClass("show");
        $(".wpr-modal-overlay").remove();
        $("body").css("paddingRight", 0);
        $("body").removeClass("wpr-modal-opened");
      }, 200);
    }

    // call modal open
    if (settings.action === "open") {
      opneModal();
    }

    // call modal close
    if (settings.action === "close") {
      closeModal();
    }

    that.find(".wpr-modal-close, .wpr-close-modal").click(function (e) {
      closeModal();
      return false;
    });

    $(".wpr-modal").click(function (e) {
      if (this === e.target) {
        closeModal();
      }
    });
  };

  $(function () {
    // call modal
    $(document.body).on("added_to_cart", function (e, ...data) {
      const [, , buttonInfo] = data;
      var button = buttonInfo[0];

      //don't show modal inside modal
      if (!$(button).closest(".recommended-products-list").length) {
        var buttonId = $(button).data("product_id");
        var modalId = "#wpr-modal-" + buttonId;
        var $modal = $(modalId);

        if ($modal.length) {
          var $preloader = $modal.find(".loading-products");
          var $recommendationProductsWrapper = $modal.find(".recommended-products-wrapper");

          var recommendationProducts = $recommendationProductsWrapper.data("recommendation-ids");

          if (recommendationProducts) {
            recommendationProducts = recommendationProducts.toString();
            recommendationProducts = recommendationProducts.split(",").map(Number);
          }

          var addedProducts = localStorage.getItem("wpr_cart_items");

          if (addedProducts) {
            addedProducts = addedProducts.split(",").map(Number);
            recommendationProducts = recommendationProducts.filter(function (id) {
              return addedProducts.indexOf(id) < 0;
            });
          }

          // return if all recommendation product are already in cart
          if (!recommendationProducts.length) return;

          $('body, .yith-quick-view-overlay').click(); // to hide existing popup, quick view, etc
          $modal.wprModal();
          $preloader.show();

          $.ajax({
            method: "GET",
            url: lc_ajax_modal.url,
            data: {
              action: "fetch_modal_products",
              nonce: lc_ajax_modal.nonce,
              recommendation_items: recommendationProducts,
              layout_type: lc_ajax_modal.layout_type,
            },
          }).done(function (data) {
            $preloader.hide();

            if (lc_ajax_modal.layout_type === "slider") {
              var owl = $modal.find(".recommended-products-slider").trigger("replace.owl.carousel", data);

                $total_items = owl.data('owl.carousel')._items.length
                $visible_items = owl.data('owl.carousel').options.items;
                owl.data('owl.carousel').options.loop = owl.data('owl.carousel').options.loop && $total_items > $visible_items

                owl.trigger("refresh.owl.carousel");
            } else {
              $recommendationProductsWrapper.html(data);
            }
          });
        }
      }
    });
  });
})(jQuery);
