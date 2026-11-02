/**
 * Option menus for variable products in the popup and the cart page list
 * (Popup_Modern::variation_picker). One menu per option (Color, Size):
 *
 * - choices that do not go with what is picked in the other menus are greyed out,
 * - when every menu has a choice, the Add button gets that variation's ID and the
 *   card shows the variation's price,
 * - a click on the Add button before that opens the first empty menu,
 * - a variation with an "Any" value (Size: any) fits every value of that option; its add
 *   sends the picked values too (lpr_attributes, see Popup_Modern::ajax_add_any_variation).
 */
(function ($) {
  "use strict";

  function variationsOf(picker) {
    let list = picker.data("lprVariations");
    if (!list) {
      try {
        list = JSON.parse(picker.attr("data-variations") || "[]");
      } catch (e) {
        list = [];
      }
      picker.data("lprVariations", list);
    }
    return list;
  }

  function chosen(picker) {
    const out = {};
    picker.find(".lpr-vpick__select").each(function () {
      out[$(this).attr("data-attribute")] = $(this).val() || "";
    });
    return out;
  }

  // Picked values sent with an add, for variations with an "Any" value.
  const anyPicks = new WeakMap();

  function fits(variation, picks, skip) {
    return Object.keys(picks).every(function (key) {
      return key === skip || !picks[key] || variation.a[key] === "" || variation.a[key] === picks[key];
    });
  }

  // The most specific variation first, like WooCommerce (Blue + L before Blue + any size).
  function best(list, picks) {
    const exact = (v) => Object.keys(v.a).filter((k) => v.a[k] !== "").length;
    return list.filter((v) => fits(v, picks)).sort((a, b) => exact(b) - exact(a))[0] || null;
  }

  function update(picker) {
    const list = variationsOf(picker);
    const picks = chosen(picker);
    const card = picker.closest("[data-lpr-product]");
    const add = card.find(".add_to_cart_button").first();
    const price = card.find(".price").first();

    // Grey out choices with no variation for the other menus' picks.
    picker.find(".lpr-vpick__select").each(function () {
      const key = $(this).attr("data-attribute");
      $(this)
        .find("option")
        .each(function () {
          if (!this.value) return;
          const value = this.value;
          this.disabled = !list.some((v) => (v.a[key] === value || v.a[key] === "") && fits(v, picks, key));
        });
    });

    const complete = Object.keys(picks).every((k) => picks[k]);
    const match = complete ? best(list, picks) : null;

    if (price.length && !price.data("lprOriginal")) price.data("lprOriginal", price.html());
    if (add[0] && match && Object.keys(match.a).some((k) => match.a[k] === "")) {
      anyPicks.set(add[0], $.extend({}, picks));
    } else if (add[0]) {
      anyPicks.delete(add[0]);
    }

    // For places without an Add button (Pro's checkout offer): the variation picked, or 0.
    card.trigger("lpr_variation_change", [match ? match.id : 0, $.extend({}, picks)]);

    if (match) {
      add.attr("data-product_id", match.id).data("product_id", match.id);
      add.removeClass("is-disabled").attr("aria-disabled", "false");
      if (price.length && match.p) price.html(match.p);
    } else {
      add.attr("data-product_id", "").data("product_id", "");
      add.addClass("is-disabled").attr("aria-disabled", "true");
      if (price.length && price.data("lprOriginal")) price.html(price.data("lprOriginal"));
    }
  }

  // Every AJAX add (WooCommerce's, the popup's own, the cart drawer's) fires this first.
  $(document.body).on("adding_to_cart", function (e, $button, data) {
    const button = $button && ($button.jquery ? $button[0] : $button);
    if (button && data && anyPicks.has(button)) data.lpr_attributes = anyPicks.get(button);
  });

  $(document).on("change", ".lpr-vpick__select", function () {
    update($(this).closest(".lpr-vpick"));
  });

  // Add clicked before every option was picked: open the first empty menu.
  $(document).on("click", "[data-lpr-product] .add_to_cart_button.is-disabled", function (e) {
    const card = $(this).closest("[data-lpr-product]");
    const picker = card.find(".lpr-vpick");
    if (!picker.length) return;
    e.preventDefault();
    const empty = picker.find(".lpr-vpick__select").filter(function () {
      return !$(this).val();
    });
    (empty.length ? empty : picker.find(".lpr-vpick__select")).first().trigger("focus");
    return false;
  });
})(window.jQuery);
