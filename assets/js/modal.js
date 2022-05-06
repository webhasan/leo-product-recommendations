(function ($, __) {
  //modal plugin
  $.fn.lprModal = function (options) {
    var settings = $.extend(
      {
        action: "open", // option for modal open or close default: open
      },
      options
    );

    var that = this;

    // modal overlay
    var overlay = $('<div class="lpr-modal-overlay show"></div>');

    // open modal
    function openModal() {
      $('body').trigger('before_open_lpr_modal'); // event before modal open

      var scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

      that.addClass("show");

      $("body").css("paddingRight", scrollbarWidth);

      $("body").addClass("lpr-modal-opened").prepend(overlay);

      setTimeout(function () {
        that.addClass("fadeIn");
        overlay.addClass("fadeIn");
      }, 10);

      return false;
    }

    // close modal
    function closeModal() {
      that.removeClass("fadeIn");
      $(".lpr-modal-overlay").addClass("fadeIn");

      setTimeout(function () {
        that.removeClass("show");
        $(".lpr-modal-overlay").remove();
        $("body").css("paddingRight", 0);
        $("body").removeClass("lpr-modal-opened");
      }, 200);

      $('body').trigger('after_close_lpr_modal'); //event after modal close
    }

    // call modal open
    if (settings.action === "open") {
      openModal();
    }

    // call modal close
    if (settings.action === "close") {
      closeModal();
    }

    that.find(".lpr-modal-close, .lpr-close-modal").click(function (e) {
      closeModal();
      return false;
    });

    $(".lpr-modal").click(function (e) {
      if (this === e.target) {
        closeModal();
      }
    });
  };

  $(function () {
    // call modal
    $(document.body).on("added_to_cart", async function (e, ...data) {

      
      const [, , buttonInfo] = data;
      var button = buttonInfo[0];

      var addedProductId = $(button).data("product_id");
      //if don't find product id from button data
      if(!addedProductId && $(button).closest('form').length) {
        addedProductId = $(button).closest('form').find('[name="add-to-cart"]').val();
      }

      var recommendedProducts = [];
      var productDataSpan = $(button).parent().find('.product-recommendations-data');
      if(productDataSpan.length) {
        recommendedProducts = productDataSpan.data('recommended-products').split(',').map(Number);
      }

      let cartItems = await $.get(lc_ajax_modal.url, {
        action: "lc_get_cart_items",
        nonce: lc_ajax_modal.nonce,
      });



      //exclude products which already in cart
      recommendedProducts = recommendedProducts.filter(id => !cartItems.includes(id));

      console.log(recommendedProducts);

      //don't show modal inside modal
      if (!$(button).closest(".recommended-products-wrapper").length) {
        
        //if does not have any product to recommend
        if(!recommendedProducts.length) return false;

        //hide existing popup, quick view etc
        $('body, .quickview-wrapper .closeqv, .yith-quick-view-overlay, .mfp-wrap').click(); 

        //modal 
        const modal = await $.get(lc_ajax_modal.url, {
          action: "fetch_modal_products",
          nonce: lc_ajax_modal.nonce,
          product_id: addedProductId,
          recommendation_items: recommendedProducts,
          layout_type: lc_ajax_modal.layout_type,
          variable_add_to_cart: lc_ajax_modal.variable_add_to_cart
        });

         $('body').append(modal);
         $('.lpr-modal').lprModal();


        // if (lc_ajax_modal.layout_type === "slider") {
        //   var owl = $(modal).find(".recommended-products-slider").trigger("replace.owl.carousel", data);

        //     $total_items = owl.data('owl.carousel')._items.length
        //     $visible_items = owl.data('owl.carousel').options.items;
            
        //     owl.data('owl.carousel').options.loop = owl.data('owl.carousel').options.loop && $total_items > $visible_items
        //     owl.trigger("refresh.owl.carousel");
        // } 

        
        

        setTimeout(() =>{
          //message animation
          $modal.find('.message-text').addClass('lpr__animated animate__lpr_headShake');
        }, 200);

        //variable product swatch
        setTimeout(() => {
          $( '.lpr-modal .variations_form' ).each( function() {
              $( this ).wc_variation_form();
          });   
          // woodmart theme vernation swatch
          if(window.woodmartThemeModule && woodmartThemeModule.swatchesVariations) {
            woodmartThemeModule.swatchesVariations();
          }

          $modal.find('.modal-heading').addClass('lpr__animated animate__lpr_headShake');
          $modal.find('.modal-heading-article').addClass('lpr__animated animate__lpr_headShake');
        }, 700);

      }else {
        var $productHeading = $(button).closest('.single-lpr').find('.woocommerce-loop-product__title'); //show notification for added product.
        var notificationText = ($productHeading.length) ? `${$productHeading.text()} ${__('has been added to cart.','leo-product-recommendations')}` : __('Item has been added to cart.','leo-product-recommendations'); 
        var topPosition = $(button).closest('.lpr-modal').find('.lpr-message').outerHeight();

        $notification_bar = $(button).closest('.lpr-modal').find('.lpr-purchase-notification'); 
        $notification_bar.css('top', topPosition).fadeIn(300).text(notificationText);
        setTimeout(() => {
           $notification_bar.fadeOut(600);
        }, 1500);
      }
    });
  });
})(jQuery, wp.i18n.__);