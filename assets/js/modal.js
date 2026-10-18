(function ($, __) {
  //modal plugin
  $.fn.lprModal = function (options) {
    const settings = $.extend({
        action: "open",
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

  //popup count
  function setPopupCount() {
    const prevPopupCountData = localStorage.getItem('lpr-popup-count');
    const hasPrevCount = (prevPopupCountData && JSON.parse(prevPopupCountData).count && (JSON.parse(prevPopupCountData).time > Date.now() - 2 * 3600 * 1000));
    const popupCount = hasPrevCount ? (JSON.parse(prevPopupCountData).count + 1) : 1;
    const popupCountData = {count: popupCount, time: Date.now()} 

    localStorage.setItem('lpr-popup-count', JSON.stringify(popupCountData));
  }

  function getPopupCount() {
    const popupData = localStorage.getItem('lpr-popup-count');
    const hasPopupCount = (popupData && JSON.parse(popupData).count && (JSON.parse(popupData).time > Date.now() - 2 * 3600 * 1000));
    return hasPopupCount ? JSON.parse(popupData).count : 0;
  }


  // Load the recommendations for a product and open the popup.
  async function openRecommendationPopup(addedProductId) {
    //hide existing popup, quick view etc
    $('body, .quickview-wrapper .closeqv, .yith-quick-view-overlay, .mfp-wrap, .owp-qv-overlay, .owp-qv-close').click(); 

    try {
      const modal = await $.get(lc_ajax_modal.url, {
        action: "lpr_get_popup_data",
        nonce: lc_ajax_modal.nonce,
        product_id: addedProductId,
      });

      if(modal) {
        const $modalWrapper = $('#lpr-modal-content');
        $modalWrapper.html(modal);

        if (lc_ajax_modal.layout_type === "slider") {

          const windowWidth = window.innerWidth;
          const $carouselWrapper = $modalWrapper.find(".recommended-products-slider");
          const slideItems = $carouselWrapper.find('.single-lpr').length;
          
          let {
            autoPlay,
            autoPlaySpeed, 
            columnGap, 
            isLoop, 
            slideBy, 
            smartSpeed,
            nextNavIcon, 
            prevNavIcon, 
            smItems, 
            lgItems, 
            mdItems, 
          } = lc_slider_setting;

          let isNav = true;


          if(slideItems <= lgItems) {
            isNav =  false;
            isLoop = false;
          }
          
          if(windowWidth < 992 && slideItems <= mdItems) {
            isNav =  false;
            isLoop = false;
          }

          if(windowWidth < 768 && slideItems <= smItems) {
            isNav =  false;
            isLoop = false;
          }

          const $carousel = $modalWrapper.find(".recommended-products-slider").owlCarousel({
            autoplayHoverPause: true,
            navElement: 'div',
            mouseDrag: false,
            autoplay: autoPlay,
            autoplayTimeout: parseInt(autoPlaySpeed),
            margin: parseInt(columnGap),              
            loop: isLoop,
            items: smItems,
            slideBy: slideBy,
            smartSpeed: parseInt(smartSpeed),
            navText: [prevNavIcon, nextNavIcon],
            nav: true,
            responsive: {
              768: {
                items: mdItems,
              },
              992: {
                items: lgItems
              }
            },
            onInitialize: () => {
              $('#lpr-modal').lprModal();
              setPopupCount();
            }
          });
          
          //$(document.body).on('after_close_lpr_modal', $carousel.trigger('destroy.owl.carousel'));
        }else {
          $('#lpr-modal').lprModal();
          setPopupCount();
        }

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

    }
  }

  // A product added with a normal form post (page reload) has no added_to_cart event.
  // PHP saved its ID in a cookie; open the popup for it once, then delete the cookie.
  function readAddedProductCookie() {
    const cookie = lc_ajax_modal.added_cookie;
    if (!cookie || !cookie.name) return 0;

    const match = document.cookie.match(new RegExp('(?:^|; )' + cookie.name + '=(\\d+)'));
    if (!match) return 0;

    document.cookie = cookie.name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=' + (cookie.path || '/') + (cookie.domain ? '; domain=' + cookie.domain : '');
    return parseInt(match[1], 10) || 0;
  }

  function popupAllowed() {
    if(!lc_ajax_modal.popup_view_times) lc_ajax_modal.popup_view_times = 'always';
    const popupShowTimes = lc_ajax_modal.popup_view_times === 'always' ? Infinity : +lc_ajax_modal.popup_view_times;
    return popupShowTimes > getPopupCount();
  }

  $(function () {
    // call modal
    $(document.body).on("added_to_cart", async function (e, ...data) {
      const popupCount = getPopupCount();

      // fallback for older version of pro plugin
      if(!lc_ajax_modal.popup_view_times) lc_ajax_modal.popup_view_times = 'always';

      const popupShowTimes = lc_ajax_modal.popup_view_times === 'always' ? Infinity : +lc_ajax_modal.popup_view_times;

      if(popupShowTimes > popupCount){
        const [, , buttonInfo] = data;
        const button = buttonInfo[0];

        let addedProductId = $(button).data("product_id");
        //if don't find product id from button data
        if(!addedProductId && $(button).closest('form').length) {
          addedProductId = $(button).closest('form').find('[name="add-to-cart"]').val();
        }

        //don't show modal inside modal
        if (!$(button).closest(".recommended-products-wrapper").length) {
        
          await openRecommendationPopup(addedProductId);

        }else {
          let $productHeading = $(button).closest('.single-lpr').find('.woocommerce-loop-product__title'); //show notification for added product.
          
          //fix for woodmart theme
          if(window.woodmartThemeModule) {
              $productHeading = $(button).closest('.single-lpr').find('.wd-entities-title a:last-child');
          }

          //fix for oceanwp theme
          if(window.oceanwp) {
            $productHeading = $(button).closest('.single-lpr').find('li.title a');
          }

          const  notificationText = ($productHeading.length) ? `${$productHeading.text()} ${__('has been added to cart.','leo-product-recommendations')}` : __('Item has been added to cart.','leo-product-recommendations'); 

          const topPosition = $(button).closest('.lpr-modal').find('.lpr-message').outerHeight();

          const $notification_bar = $(button).closest('.lpr-modal').find('.lpr-purchase-notification'); 
          $notification_bar.css('top', topPosition).fadeIn(300).text(notificationText);
          setTimeout(() => {
            $notification_bar.fadeOut(600);
          }, 1500);
        }
      }
    });

    // popup after a normal (non AJAX) add to cart
    const reloadedProductId = readAddedProductCookie();
    const onCartPage = !!lc_ajax_modal.is_cart_page || $('body').hasClass('woocommerce-cart') || $('body').hasClass('woocommerce-checkout');

    if (reloadedProductId && popupAllowed() && (!onCartPage || lc_ajax_modal.popup_on_cart)) {
      openRecommendationPopup(reloadedProductId);
    }
  });
})(jQuery, wp.i18n.__);