/**
 * Recommendations in more places (cart page, and Pro places).
 *
 * - GA4 dataLayer: the lpr_popup_* events with the place's item_list_id
 *   (lpr_cart, lpr_fbt, lpr_checkout, lpr_thankyou).
 * - jQuery events on document.body for the Pro analytics:
 *   lpr_place_viewed [section, data], lpr_place_clicked [section, productId, sourceId].
 * - Cart block: refreshes the block after an add and hides the added product.
 */
(function ($) {
  "use strict";

  const cfg = window.lprPlaces || {};
  const viewed = {};

  function readData(section) {
    const node = section.querySelector("script.lpr-datalayer-data");
    if (!node) return null;
    try {
      return JSON.parse(node.textContent);
    } catch (e) {
      return null;
    }
  }

  function cards(section) {
    return Array.prototype.slice.call(section.querySelectorAll("[data-lpr-product]"));
  }

  function canPush(data) {
    return cfg.datalayer && data && Array.isArray(window.dataLayer);
  }

  function item(data, id, index, extra) {
    const base = data.items && data.items[id];
    if (!base) return null;
    const out = $.extend({}, base, extra || {}, { item_list_id: data.item_list_id, item_list_name: data.item_list_name });
    delete out.children;
    if (index >= 0) out.index = index;
    return out;
  }

  function push(data, event, items, sourceId) {
    if (!items.length) return;
    let value = 0;
    items.forEach((i) => (value += (parseFloat(i.price) || 0) * (i.quantity || 1)));
    const source = sourceId || (data.source && data.source.source_product_id);
    window.dataLayer.push({ ecommerce: null });
    window.dataLayer.push({
      event: event,
      lpr_place: data.item_list_id,
      lpr_source_product_id: source ? String(source) : undefined,
      ecommerce: {
        currency: data.currency,
        value: Math.round(value * 100) / 100,
        item_list_id: data.item_list_id,
        item_list_name: data.item_list_name,
        items: items,
      },
    });
  }

  function onView(section) {
    const data = readData(section);
    const key = section.getAttribute("data-lpr-place");
    if (!data || viewed[key]) return;
    viewed[key] = true;

    if (canPush(data)) {
      const list = cards(section)
        .map((c, i) => item(data, c.getAttribute("data-lpr-product"), i))
        .filter(Boolean);
      push(data, "lpr_popup_view_item_list", list);
    }

    $(document.body).trigger("lpr_place_viewed", [section, data]);
  }

  let io = null;

  function watch() {
    const sections = document.querySelectorAll(".lpr-place[data-lpr-place]");
    // One observer at a time: the classic cart replaces its HTML after every change.
    if (io) {
      io.disconnect();
      io = null;
    }
    if (!sections.length) return;

    if (!("IntersectionObserver" in window)) {
      sections.forEach(onView);
      return;
    }

    io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            onView(entry.target);
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.25 }
    );

    sections.forEach((s) => {
      if (!viewed[s.getAttribute("data-lpr-place")]) io.observe(s);
    });
  }

  // Product link clicked.
  $(document).on("click", ".lpr-place [data-lpr-product] a[href]", function () {
    const $link = $(this);
    const href = $link.attr("href") || "";
    if ($link.is(".button, .add_to_cart_button, .added_to_cart") || href.charAt(0) === "#") return;

    const section = $link.closest(".lpr-place")[0];
    const $card = $link.closest("[data-lpr-product]");
    const id = $card.attr("data-lpr-product");
    const source = $card.attr("data-lpr-source");
    const data = readData(section);

    if (canPush(data)) {
      const one = item(data, id, cards(section).indexOf($card[0]));
      if (one) push(data, "lpr_popup_select_item", [one], source);
    }

    $(document.body).trigger("lpr_place_clicked", [section, parseInt(id, 10) || 0, parseInt(source, 10) || 0]);
  });

  // Added from a place.
  $(document.body).on("added_to_cart", function (e, fragments, hash, $button) {
    const button = $button && ($button.jquery ? $button[0] : $button);
    if (!button || !button.closest) return;

    const section = button.closest(".lpr-place");
    if (!section) return;

    const card = button.closest("[data-lpr-product]");
    const data = readData(section);

    if (card && canPush(data)) {
      const one = item(data, card.getAttribute("data-lpr-product"), cards(section).indexOf(card));
      if (one) push(data, "lpr_popup_add_to_cart", [one], card.getAttribute("data-lpr-source"));
    }

    // Cart block: tell the block the cart changed and take the product off the list.
    if (section.classList.contains("lpr-place--block")) {
      document.body.dispatchEvent(new CustomEvent("wc-blocks_added_to_cart", { bubbles: true, cancelable: true }));
      try {
        if (window.wp && wp.data && wp.data.dispatch("wc/store/cart")) {
          wp.data.dispatch("wc/store/cart").invalidateResolutionForStore();
        }
      } catch (err) {}
      if (card) {
        $(card).fadeOut(300, function () {
          const left = cards(section).filter((c) => c.offsetParent !== null);
          if (!left.length) $(section).slideUp(200);
          else $(section).trigger("lpr_slider_update");
        });
      }
    }
  });

  /* ---------- Slider layout ---------- */

  function sliderUpdate(section) {
    const list = section.querySelector("ul.products");
    const nav = section.querySelector(".lpr-place__nav");
    if (!list || !nav) return;

    const max = list.scrollWidth - list.clientWidth;
    const pos = Math.abs(list.scrollLeft); // negative in RTL
    nav.hidden = max <= 2;
    nav.querySelector(".lpr-place__prev").disabled = pos <= 2;
    nav.querySelector(".lpr-place__next").disabled = pos >= max - 2;
  }

  function sliderInit(section) {
    if (section.getAttribute("data-lpr-slider") === "1") {
      sliderUpdate(section);
      return;
    }
    const list = section.querySelector("ul.products");
    const nav = section.querySelector(".lpr-place__nav");
    if (!list || !nav) return;
    section.setAttribute("data-lpr-slider", "1");

    const rtl = getComputedStyle(list).direction === "rtl";
    const move = (dir) => {
      const card = list.querySelector("li.product");
      const gap = parseFloat(getComputedStyle(list).columnGap) || 0;
      const step = card ? card.getBoundingClientRect().width + gap : list.clientWidth;
      // Move by the cards that are fully in view, at least one.
      const by = Math.max(1, Math.floor((list.clientWidth + gap) / step)) * step;
      const reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      list.scrollBy({ left: dir * (rtl ? -by : by), behavior: reduce ? "auto" : "smooth" });
    };

    nav.querySelector(".lpr-place__prev").addEventListener("click", () => move(-1));
    nav.querySelector(".lpr-place__next").addEventListener("click", () => move(1));

    let frame = 0;
    const update = () => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => sliderUpdate(section));
    };
    list.addEventListener("scroll", update, { passive: true });
    // Removed with the section when the classic cart replaces its HTML.
    const onResize = () => {
      if (!document.body.contains(section)) {
        window.removeEventListener("resize", onResize);
        return;
      }
      update();
    };
    window.addEventListener("resize", onResize);
    $(section).on("lpr_slider_update", update);
    sliderUpdate(section);
  }

  function sliders() {
    document.querySelectorAll(".lpr-place--slider").forEach(sliderInit);
  }

  $(watch);
  $(sliders);
  $(window).on("load", sliders); // image sizes known

  // The classic cart replaces its HTML after every change.
  $(document.body).on("updated_wc_div updated_cart_totals lpr_places_refresh", function () {
    watch();
    sliders();
  });
})(window.jQuery);
