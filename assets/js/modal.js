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


  async function getPopupHtml(productId) {
    return await $.get(lc_ajax_modal.url, {
      action: "lpr_get_popup_data",
      nonce: lc_ajax_modal.nonce,
      product_id: productId,
    });
  }

  // GTM4WP only adds its product data on front-end requests, and the popup HTML comes from
  // admin-ajax. Fetch that data from a front-end request and copy only GTM4WP's own
  // elements/attributes into the popup, so its add_to_cart tracking works there too.
  // Product ID of a popup card (from its add to cart button or form).
  function getCardProductId($card) {
    const $form = $card.find("form.cart").first();
    const $button = $card.find(".add_to_cart_button[data-product_id]").first();

    return parseInt(
      $button.data("product_id") ||
        $form.find('[name="add-to-cart"]').val() ||
        $form.data("product_id"),
      10
    ) || 0;
  }

  function addTrackingData($wrapper) {
    if (!lc_ajax_modal.tracking_url) return;

    const cards = [];

    $wrapper.find(".single-lpr").each(function () {
      const $card = $(this);
      const $form = $card.find("form.cart").first();
      const $button = $card.find(".add_to_cart_button[data-product_id]").first();
      const id = getCardProductId($card);

      if (id) cards.push({ id: id, $form: $form, $button: $button });
    });

    if (!cards.length) return;

    $.post(lc_ajax_modal.tracking_url, {
      nonce: lc_ajax_modal.nonce,
      product_ids: cards.map(function (card) { return card.id; }),
    }).done(function (response) {
      if (!response || !response.success || !response.data) return;

      cards.forEach(function (card) {
        const item = response.data[card.id];
        if (!item) return;

        if (card.$form.length && item.form) {
          $("<div>").html(item.form).find('[class^="gtm4wp"], [class*=" gtm4wp"]').each(function () {
            if (!card.$form.find("." + this.classList[0]).length) {
              card.$form.append(this);
            }
          });
        }

        if (card.$button.length && item.link) {
          const probe = $("<div>").html(item.link).children().get(0);
          if (!probe) return;

          Array.prototype.forEach.call(probe.attributes, function (attribute) {
            if (attribute.name.indexOf("data-gtm4wp") === 0 && !card.$button.attr(attribute.name)) {
              card.$button.attr(attribute.name, attribute.value);
            }
          });
        }
      });
    });
  }

  /*
   * dataLayer events for the popup, in GA4 ecommerce format:
   * lpr_popup_view_item_list (popup opens), lpr_popup_select_item (product link clicked),
   * lpr_popup_add_to_cart (product added from the popup). Only pushed when the site
   * already has window.dataLayer (GTM); the plugin never creates it. The standard
   * add_to_cart of other tracking plugins is left as it is.
   */
  let datalayerData = null;

  function datalayerReady() {
    return !!lc_ajax_modal.datalayer && Array.isArray(window.dataLayer) && !!datalayerData;
  }

  function datalayerPush(event, ecommerce) {
    if (!datalayerReady()) return;

    window.dataLayer.push({ ecommerce: null });
    window.dataLayer.push({
      event: event,
      ecommerce: ecommerce,
      lpr: $.extend({}, datalayerData.source),
    });
  }

  // Popup cards in display order, without the slider's cloned copies.
  function popupCards() {
    const seen = {};

    return $("#lpr-modal .single-lpr")
      .filter(function () {
        if ($(this).closest(".owl-item.cloned").length) return false;
        const id = getCardProductId($(this));
        if (!id || seen[id]) return false;
        seen[id] = true;
        return true;
      })
      .get();
  }

  function cardItem(card, extra) {
    const id = getCardProductId($(card));
    const base = datalayerData && datalayerData.items && datalayerData.items[id];
    if (!base) return null;

    const item = $.extend({}, base, {
      index: popupCards().indexOf($(card).closest(".single-lpr").get(0)),
      item_list_id: datalayerData.item_list_id,
      item_list_name: datalayerData.item_list_name,
    }, extra || {});

    delete item.children;
    if (item.index < 0) delete item.index;

    return item;
  }

  function ecommerceFor(items) {
    const value = items.reduce(function (sum, item) {
      return sum + (parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 1);
    }, 0);

    return {
      currency: datalayerData.currency,
      value: Math.round(value * 100) / 100,
      item_list_id: datalayerData.item_list_id,
      item_list_name: datalayerData.item_list_name,
      items: items,
    };
  }

  function readDatalayerData($wrapper) {
    datalayerData = null;
    const $json = $wrapper.find("script.lpr-datalayer-data").first();
    if (!$json.length) return;

    try {
      datalayerData = JSON.parse($json.text());
    } catch (e) {
      datalayerData = null;
    }
  }

  function pushViewItemList() {
    if (!datalayerReady()) return;

    const items = popupCards().map(function (card) { return cardItem(card); }).filter(Boolean);
    if (!items.length) return;

    const ecommerce = ecommerceFor(items);
    delete ecommerce.value;
    delete ecommerce.currency;
    datalayerPush("lpr_popup_view_item_list", ecommerce);
  }

  // Items added from a popup card (quantity, chosen variation, grouped children).
  function addedItems($card, $button) {
    const $form = $button.closest("form.cart").length ? $button.closest("form.cart") : $card.find("form.cart").first();
    const id = getCardProductId($card);
    const base = datalayerData && datalayerData.items && datalayerData.items[id];
    if (!base) return [];

    // grouped product: one item per child with a quantity
    if ($form.length && base.children) {
      const items = [];
      $form.find('input[name^="quantity["]').each(function () {
        const childId = (this.name.match(/quantity\[(\d+)\]/) || [])[1];
        const qty = parseFloat(this.value) || 0;
        const child = childId && base.children[childId];
        if (child && qty > 0) {
          items.push($.extend({}, child, {
            quantity: qty,
            item_list_id: datalayerData.item_list_id,
            item_list_name: datalayerData.item_list_name,
          }));
        }
      });
      return items;
    }

    let quantity = parseFloat($form.find('[name="quantity"]').val()) || parseFloat($button.data("quantity")) || 1;
    const extra = { quantity: quantity };

    // variable product: the chosen variation
    const variationId = parseInt($form.find('[name="variation_id"]').val(), 10);
    if (variationId) {
      const variations = $form.data("product_variations") || [];
      const variation = Array.isArray(variations)
        ? variations.find(function (v) { return parseInt(v.variation_id, 10) === variationId; })
        : null;

      extra.item_id = datalayerData.id_type === "sku" && variation && variation.sku ? String(variation.sku) : String(variationId);

      if (variation) {
        if (variation.display_price !== undefined) extra.price = parseFloat(variation.display_price);
        const values = Object.keys(variation.attributes || {}).map(function (key) {
          return variation.attributes[key] || $form.find('[name="' + key + '"]').val();
        }).filter(Boolean);
        if (values.length) extra.item_variant = values.join(", ");
      }
    }

    const item = cardItem($card.get(0), extra);
    return item ? [item] : [];
  }

  $(document).on("click", "#lpr-modal .single-lpr a[href]", function () {
    if (!datalayerReady()) return;

    const $link = $(this);
    const href = $link.attr("href") || "";
    if ($link.is(".button, .add_to_cart_button, .added_to_cart") || href.charAt(0) === "#") return;

    const item = cardItem($link.closest(".single-lpr").get(0));
    if (!item) return;

    const ecommerce = ecommerceFor([item]);
    delete ecommerce.value;
    delete ecommerce.currency;
    datalayerPush("lpr_popup_select_item", ecommerce);
  });

  $(document.body).on("added_to_cart", function (e, fragments, cartHash, buttonInfo) {
    if (!datalayerReady() || !buttonInfo) return;

    const button = buttonInfo.jquery ? buttonInfo[0] : buttonInfo.nodeType ? buttonInfo : buttonInfo[0];
    if (!button || !$(button).closest("#lpr-modal").length) return;

    const $card = $(button).closest(".single-lpr");
    if (!$card.length) return;

    const items = addedItems($card, $(button));
    if (items.length) datalayerPush("lpr_popup_add_to_cart", ecommerceFor(items));
  });

  // Load the recommendations for a product and open the popup.
  async function openRecommendationPopup(addedProductId) {
    //hide existing popup, quick view etc
    $('body, .quickview-wrapper .closeqv, .yith-quick-view-overlay, .mfp-wrap, .owp-qv-overlay, .owp-qv-close').click(); 

    try {
      const modal = await getPopupHtml(addedProductId);

      if(modal) {
        const $modalWrapper = $('#lpr-modal-content');
        $modalWrapper.html(modal);
        addTrackingData($modalWrapper);
        readDatalayerData($modalWrapper);

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
              setTimeout(pushViewItemList, 0);
            }
          });
          
          //$(document.body).on('after_close_lpr_modal', $carousel.trigger('destroy.owl.carousel'));
        }else {
          $('#lpr-modal').lprModal();
          setPopupCount();
          pushViewItemList();
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
      // This add opens the popup now; drop any after-reload cookie a background form
      // post (theme AJAX add to cart) may have set, so it doesn't open again later.
      readAddedProductCookie();

      const popupCount = getPopupCount();

      // fallback for older version of pro plugin
      if(!lc_ajax_modal.popup_view_times) lc_ajax_modal.popup_view_times = 'always';

      const popupShowTimes = lc_ajax_modal.popup_view_times === 'always' ? Infinity : +lc_ajax_modal.popup_view_times;

      if(popupShowTimes > popupCount){
        const [, , buttonInfo] = data;
        const button = buttonInfo && (buttonInfo.jquery ? buttonInfo[0] : (buttonInfo.nodeType ? buttonInfo : buttonInfo[0]));

        // Some themes fire added_to_cart without a button (Flatsome's mini cart
        // quantity change); there is no product to show recommendations for.
        if (!button) return;

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