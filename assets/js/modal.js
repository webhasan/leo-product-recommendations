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

      // Keep the page from shifting when its scrollbar goes away; restore the old value on close.
      if (!$("body").hasClass("lpr-modal-opened")) {
        savedPadding = document.body.style.paddingRight;
        if (scrollbarWidth > 0) $("body").css("paddingRight", scrollbarWidth);
      }
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
        document.body.style.paddingRight = savedPadding || "";
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

    // Bound once per popup, not once per call.
    that.find(".lpr-modal-close, .lpr-close-modal").off("click.lpr").on("click.lpr", function () {
      closeModal();
      return false;
    });

    that.off("click.lprbg").on("click.lprbg", function (e) {
      if (this === e.target) {
        closeModal();
      }
    });
  };

  let savedPadding = "";
  const reducedMotion = () => !!(window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches);

  //popup count
  // Storage can be blocked (private mode, browser settings) or hold a broken value.
  function readPopupCount() {
    try {
      const data = JSON.parse(localStorage.getItem('lpr-popup-count') || "null");
      return data && data.count && data.time > Date.now() - 2 * 3600 * 1000 ? data.count : 0;
    } catch (e) {
      return 0;
    }
  }

  function setPopupCount() {
    try {
      localStorage.setItem('lpr-popup-count', JSON.stringify({ count: readPopupCount() + 1, time: Date.now() }));
    } catch (e) {}
  }

  function getPopupCount() {
    return readPopupCount();
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
    if ($card.attr("data-lpr-product")) return parseInt($card.attr("data-lpr-product"), 10) || 0;
    const $form = $card.find("form.cart").first();
    const $button = $card.find(".add_to_cart_button[data-product_id]").first();

    return parseInt(
      $button.data("product_id") ||
        $form.find('[name="add-to-cart"]').val() ||
        $form.data("product_id"),
      10
    ) || 0;
  }

  // Parse HTML without running anything in it (no scripts, no onerror handlers).
  function inertFragment(html) {
    return new DOMParser().parseFromString("<body>" + html + "</body>", "text/html").body;
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
          $(inertFragment(item.form)).find('[class^="gtm4wp"], [class*=" gtm4wp"]').each(function () {
            if (!card.$form.find("." + this.classList[0]).length) {
              // Copy only the tag, its classes and data-* attributes (no event handlers).
              const copy = document.createElement(this.tagName === "INPUT" ? "input" : "span");
              Array.prototype.forEach.call(this.attributes, function (attribute) {
                if (/^(class|style|type|name|value|data-[\w-]+)$/.test(attribute.name)) {
                  copy.setAttribute(attribute.name, attribute.value);
                }
              });
              card.$form.append(copy);
            }
          });
        }

        if (card.$button.length && item.link) {
          const probe = inertFragment(item.link).firstElementChild;
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

    // Modern popup: a variation picked from the card's select.
    const buttonId = parseInt($button.data("product_id"), 10);
    if (!variationId && buttonId && buttonId !== id) {
      extra.item_id = String(buttonId);
      const label = $card.find(".lpr-pm-card__select option:selected").text();
      if (label) extra.item_variant = label.replace(/\s*\([^)]*\)\s*$/, "");
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

  /* ---------- modern popup (3.6.0) ---------- */

  let lastFocus = null;

  function setupModern($wrapper) {
    const $list = $wrapper.find(".lpr-pm__list");
    const $nav = $wrapper.find(".lpr-pm__nav");
    const list = $list.get(0);

    function updateNav() {
      if (!list) return;
      const overflow = list.scrollWidth > list.clientWidth + 2;
      $nav.prop("hidden", !overflow);
      // scrollLeft is negative in right-to-left pages; the distance moved is the same.
      const pos = Math.abs(list.scrollLeft);
      const max = list.scrollWidth - list.clientWidth - 2;
      $nav.find(".lpr-pm__prev").prop("disabled", pos <= 1);
      $nav.find(".lpr-pm__next").prop("disabled", pos >= max);
    }

    function move(dir) {
      const card = list && list.querySelector(".lpr-pm-card");
      if (!card) return;
      const gap = parseFloat(getComputedStyle(list).columnGap) || 0;
      const step = card.getBoundingClientRect().width + gap;
      const rtl = getComputedStyle(list).direction === "rtl";
      const by = Math.max(1, Math.floor((list.clientWidth + gap) / step)) * step;
      list.scrollBy({ left: dir * (rtl ? -by : by), behavior: reducedMotion() ? "auto" : "smooth" });
    }

    $wrapper.find(".lpr-pm__prev").on("click", () => move(-1));
    $wrapper.find(".lpr-pm__next").on("click", () => move(1));
    $list.on("scroll", () => window.requestAnimationFrame(updateNav));
    $(window).off("resize.lprpm").on("resize.lprpm", updateNav);
    setTimeout(updateNav, 50);
    $wrapper.find("img").on("load", updateNav);

  }

  // A variation picked in a card: the Add button adds that variation.
  $(document).on("change", "#lpr-modal .lpr-pm-card__select", function () {
    const value = $(this).val();
    const $add = $(this).siblings(".lpr-pm-card__add");
    $add.attr("data-product_id", value || "").data("product_id", value || "");
    $add.toggleClass("is-disabled", !value).attr("aria-disabled", value ? "false" : "true");
  });

  $(document).on("click", "#lpr-modal .lpr-pm-card__add.is-disabled", function (e) {
    e.preventDefault();
    $(this).siblings(".lpr-pm-card__select").trigger("focus");
    return false;
  });

  function modernToast($modal, text) {
    const $toast = $modal.find(".lpr-pm__toast");
    if (!$toast.length) return;
    $toast.text(text).addClass("is-shown");
    clearTimeout($toast.data("timer"));
    $toast.data("timer", setTimeout(() => $toast.removeClass("is-shown"), 1800));
  }

  // After adding from the popup: subtotal and free shipping bar again.
  function refreshModernSummary($modal) {
    const $summary = $modal.find("[data-lpr-pm-summary]");
    if (!$summary.length) return;
    $.get(lc_ajax_modal.url, { action: "lpr_popup_summary", nonce: lc_ajax_modal.nonce, _: Date.now() }).done(function (res) {
      if (res && res.success && res.data) $summary.html(res.data.html || "");
    });
  }

  function popupOpen() {
    return $("#lpr-modal").hasClass("show");
  }

  const FOCUSABLE = 'a[href]:not([tabindex="-1"]), button:not([disabled]):not([tabindex="-1"]), input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

  // Keyboard (both designs): Esc closes, Tab stays inside the popup.
  $(document).on("keydown", function (e) {
    if (!popupOpen()) return;
    if (e.key === "Escape") {
      $("#lpr-modal").lprModal({ action: "close" });
      return;
    }
    if (e.key !== "Tab") return;
    const modal = $("#lpr-modal").get(0);
    const $items = $("#lpr-modal-content").find(FOCUSABLE).filter(":visible");
    if (!$items.length) {
      e.preventDefault();
      return;
    }
    const first = $items.get(0);
    const last = $items.get($items.length - 1);
    const active = document.activeElement;
    const inside = $.contains(modal, active) && active !== $("#lpr-modal-content").get(0);
    if (e.shiftKey && (active === first || !inside)) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && (active === last || !inside)) {
      e.preventDefault();
      first.focus();
    }
  });

  // Classic popup: its close icon works from the keyboard too.
  $(document).on("keydown", "#lpr-modal .lpr-modal-close[role='button']", function (e) {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      $("#lpr-modal").lprModal({ action: "close" });
    }
  });

  // Dialog role and focus for both designs.
  function prepareDialog($wrapper) {
    $("#lpr-modal").attr({ role: "dialog", "aria-modal": "true" });
    const $title = $wrapper.find("#lpr-pm-title, .modal-heading").first();
    if ($title.length) {
      if (!$title.attr("id")) $title.attr("id", "lpr-modal-title");
      $("#lpr-modal").attr("aria-labelledby", $title.attr("id"));
    }
    $wrapper.find(".lpr-modal-close").not("button").attr({ role: "button", tabindex: "0", "aria-label": __("Close", "leo-product-recommendations") }).removeAttr("aria-hidden");
    setTimeout(() => {
      const dialog = $("#lpr-modal-content").attr("tabindex", "-1").get(0);
      if (dialog) dialog.focus({ preventScroll: true });
    }, 60);
  }

  $(document.body).on("after_close_lpr_modal", function () {
    if (lastFocus && document.body.contains(lastFocus)) {
      try { lastFocus.focus({ preventScroll: true }); } catch (err) {}
    }
    lastFocus = null;
  });

  // Load the recommendations for a product and open the popup.
  let openToken = 0;

  async function openRecommendationPopup(addedProductId) {
    const token = ++openToken;
    //hide existing popup, quick view etc
    $('body, .quickview-wrapper .closeqv, .yith-quick-view-overlay, .mfp-wrap, .owp-qv-overlay, .owp-qv-close').click(); 

    try {
      const modal = await getPopupHtml(addedProductId);

      // A newer add (double click, quick second add) opened its own popup: drop this one.
      if (token !== openToken) return;

      if(modal) {
        const $modalWrapper = $('#lpr-modal-content');
        const $oldSlider = $modalWrapper.find(".owl-carousel");
        if ($oldSlider.length) $oldSlider.trigger("destroy.owl.carousel");
        if (!popupOpen()) lastFocus = document.activeElement;
        $modalWrapper.html(modal);
        addTrackingData($modalWrapper);
        readDatalayerData($modalWrapper);

        const isModern = $modalWrapper.find(".lpr-pm").length > 0;
        $("#lpr-modal").toggleClass("lpr-modal--modern", isModern);

        prepareDialog($modalWrapper);

        if (isModern) {
          $('#lpr-modal').lprModal();
          setPopupCount();
          pushViewItemList();
          setupModern($modalWrapper);
          return;
        }

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
            navElement: 'button type="button"',
            mouseDrag: false,
            autoplay: autoPlay && !reducedMotion(),
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

        if (!reducedMotion()) {
          setTimeout(() => {
            //message animation
            $modalWrapper.find('.message-text').addClass('lpr__animated animate__lpr_headShake');
          }, 200);
        }

        //variable product swatch
        setTimeout(() => {
          $( '.lpr-modal .variations_form' ).each( function() {
              $( this ).wc_variation_form();
          }); 

          // woodmart theme variation swatch
          if(window.woodmartThemeModule && woodmartThemeModule.swatchesVariations) {
            woodmartThemeModule.swatchesVariations();
          }
          if (!reducedMotion()) {
            $modalWrapper.find('.modal-heading').addClass('lpr__animated animate__lpr_headShake');
            $modalWrapper.find('.modal-heading-article').addClass('lpr__animated animate__lpr_headShake');
          }
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
    // Lets Pro (or another plugin) show the recommendations somewhere else, for example
    // a cart drawer. Call event.preventDefault() to stop the popup for this add.
    // source: "ajax" (added without reload) or "reload" (normal form post).
    function popupCancelled(productId, source) {
      const event = $.Event("lpr_before_popup");
      $(document.body).trigger(event, [productId, source]);
      return event.isDefaultPrevented();
    }

    // call modal
    $(document.body).on("added_to_cart", async function (e, ...data) {
      // This add opens the popup now; drop any after-reload cookie a background form
      // post (theme AJAX add to cart) may have set, so it doesn't open again later.
      readAddedProductCookie();

      const [, , buttonInfo] = data;
      const button = buttonInfo && (buttonInfo.jquery ? buttonInfo[0] : (buttonInfo.nodeType ? buttonInfo : buttonInfo[0]));

      // Some themes fire added_to_cart without a button (Flatsome's mini cart
      // quantity change); there is no product to show recommendations for.
      if (!button) return;

      // Added from a list of recommendations (the popup or another place): show the
      // short notice in the popup, never a new popup.
      if ($(button).closest(".lpr-pm").length) {
        const $modal = $(button).closest(".lpr-modal");
        const name = $(button).closest(".lpr-pm-card").find(".lpr-pm-card__name").text();
        $(button).addClass("added").text(__("Added", "leo-product-recommendations"));
        /* translators: %s: product name */
        modernToast($modal, name ? __("%s has been added to your cart.", "leo-product-recommendations").replace("%s", name) : __("The product has been added to your cart.", "leo-product-recommendations"));
        refreshModernSummary($modal);
        return;
      }

      if ($(button).closest(".recommended-products-wrapper").length) {
        let $productHeading = $(button).closest('.single-lpr').find('.woocommerce-loop-product__title'); //show notification for added product.

        //fix for woodmart theme
        if(window.woodmartThemeModule) {
            $productHeading = $(button).closest('.single-lpr').find('.wd-entities-title a:last-child');
        }

        //fix for oceanwp theme
        if(window.oceanwp) {
          $productHeading = $(button).closest('.single-lpr').find('li.title a');
        }

        const $modal = $(button).closest('.lpr-modal');
        if (!$modal.length) return;

        /* translators: %s: product name */
        const notificationText = $productHeading.length ? __("%s has been added to your cart.", "leo-product-recommendations").replace("%s", $productHeading.text()) : __("The product has been added to your cart.", "leo-product-recommendations");

        const topPosition = $modal.find('.lpr-message').outerHeight();

        const $notification_bar = $modal.find('.lpr-purchase-notification'); 
        $notification_bar.css('top', topPosition).fadeIn(300).text(notificationText);
        setTimeout(() => {
          $notification_bar.fadeOut(600);
        }, 1500);
        return;
      }

      // Added from a recommendations block on the page (cart page and other places)
      // or on the cart/checkout page: the page itself shows the result, no popup.
      if ($(button).closest(".lpr-place").length || (lc_ajax_modal.is_cart_page && !lc_ajax_modal.popup_on_cart)) return;

      let addedProductId = $(button).data("product_id");
      //if don't find product id from button data
      if(!addedProductId && $(button).closest('form').length) {
        addedProductId = $(button).closest('form').find('[name="add-to-cart"]').val();
      }

      if (popupCancelled(addedProductId, "ajax")) return;

      if (popupAllowed()) {
        await openRecommendationPopup(addedProductId);
      }
    });

    // Block themes: product grids built with WooCommerce blocks (Product Collection,
    // Product Button) add to the cart through the Store API and send only the browser
    // event "wc-blocks_added_to_cart", with no jQuery added_to_cart. Remember which
    // product button was clicked and open the popup (or the drawer) for it.
    let lastJqueryAdd = 0;
    let blockClick = null;
    $(document.body).on("added_to_cart", function () {
      lastJqueryAdd = Date.now();
    });
    document.addEventListener("click", function (e) {
      const el = e.target && e.target.closest ? e.target.closest("[data-product_id]") : null;
      if (!el || el.closest("#lpr-modal, #lpr-drawer, .lpr-place")) return;
      if (!el.closest(".wc-block-components-product-button, .wp-block-woocommerce-product-button, [data-wp-interactive]")) return;
      blockClick = { id: parseInt(el.getAttribute("data-product_id"), 10) || 0, time: Date.now() };
    }, true);
    document.addEventListener("wc-blocks_added_to_cart", function () {
      const click = blockClick;
      // Let a jQuery added_to_cart for the same add (classic buttons) run first.
      setTimeout(async function () {
        if (!click || !click.id || Date.now() - click.time > 15000 || Date.now() - lastJqueryAdd < 2000) return;
        blockClick = null;
        if (lc_ajax_modal.is_cart_page && !lc_ajax_modal.popup_on_cart) return;
        if (popupCancelled(click.id, "ajax")) return;
        if (popupAllowed()) await openRecommendationPopup(click.id);
      }, 80);
    });

    // popup after a normal (non AJAX) add to cart
    const reloadedProductId = readAddedProductCookie();
    const onCartPage = !!lc_ajax_modal.is_cart_page || $('body').hasClass('woocommerce-cart') || $('body').hasClass('woocommerce-checkout');

    if (reloadedProductId && (!onCartPage || lc_ajax_modal.popup_on_cart) && !popupCancelled(reloadedProductId, "reload") && popupAllowed()) {
      openRecommendationPopup(reloadedProductId);
    }
  });
})(jQuery, wp.i18n.__);