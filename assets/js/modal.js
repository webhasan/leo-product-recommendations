(function ($, __) {
  //modal plugin
  $.fn.lprModal = function (options) {
    const settings = $.extend(
      {
        action: "open", // option for modal open or close default: open
      },
      options
    );

    const that = this;

    // modal overlay
    const overlay = $('<div class="lpr-modal-overlay show"></div>');

    // open modal
    function openModal() {
      $('body').trigger('before_open_lpr_modal'); // event before modal open

      const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

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
      const button = buttonInfo[0];

      let addedProductId = $(button).data("product_id");
      //if don't find product id from button data
      if(!addedProductId && $(button).closest('form').length) {
        addedProductId = $(button).closest('form').find('[name="add-to-cart"]').val();
      }

      //don't show modal inside modal
      if (!$(button).closest(".recommended-products-wrapper").length) {
      
        //hide existing popup, quick view etc
        $('body, .quickview-wrapper .closeqv, .yith-quick-view-overlay, .mfp-wrap').click(); 

        try {
          const modal = await $.get(lc_ajax_modal.url, {
            action: "fetch_modal_products",
            nonce: lc_ajax_modal.nonce,
            product_id: addedProductId,
          });

          if(modal) {
            const $modalWrapper = $('#lpr-modal-content');
            $modalWrapper.html(modal);

            $('#lpr-modal').lprModal();
            setTimeout(() =>{
              //message animation
              $modalWrapper.find('.message-text').addClass('lpr__animated animate__lpr_headShake');
            }, 200);

            setTimeout(() =>{
              //message animation
              $modalWrapper.find('.message-text').addClass('lpr__animated animate__lpr_headShake');
            }, 200);

            //variable product swatch
            setTimeout(() => {
              $( '.lpr-modal .variations_form' ).each( function() {
                  $( this ).wc_variation_form();
              }); 

              // woodmart theme variation swatch
              if(window.woodmartThemeModule && woodmartThemeModule.swatchesVariations) {
                woodmartThemeModule.swatchesVariations();
              }
              $modalWrapper.find('.modal-heading').addClass('lpr__animated animate__lpr_headShake');
              $modalWrapper.find('.modal-heading-article').addClass('lpr__animated animate__lpr_headShake');
            }, 700);
          }
        }catch(e) {
          //error occurred to fetch template
        }

      }else {
        const $productHeading = $(button).closest('.single-lpr').find('.woocommerce-loop-product__title'); //show notification for added product.

        const  notificationText = ($productHeading.length) ? `${$productHeading.text()} ${__('has been added to cart.','leo-product-recommendations')}` : __('Item has been added to cart.','leo-product-recommendations'); 
        const topPosition = $(button).closest('.lpr-modal').find('.lpr-message').outerHeight();

        const $notification_bar = $(button).closest('.lpr-modal').find('.lpr-purchase-notification'); 
        $notification_bar.css('top', topPosition).fadeIn(300).text(notificationText);
        setTimeout(() => {
           $notification_bar.fadeOut(600);
        }, 1500);
      }
    });
  });
})(jQuery, wp.i18n.__);