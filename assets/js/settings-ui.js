/**
 * Leo Product Recommendations: simple settings and live preview (3.6.0).
 *
 * - Swatches and steppers.
 * - Rows shown only when they apply, "More options" for the rest.
 * - "Unsaved changes" bar.
 * - Live preview of the popup (and the Pro cart drawer) next to the settings.
 *
 * Loaded before the settings script, so the layout is ready before the editors start.
 */
(function ($) {
	"use strict";

	const cfg = window.lprSettingsUI || {};
	const __ = window.wp && wp.i18n ? wp.i18n.__ : (s) => s;
	const $page = $(".lpr-setting-page");
	const $form = $page.find("form").first();
	if (!$form.length) return;

	const PREFIX = (cfg.optionName || "lc_lpr_settings");
	const overrides = {}; // colors being dragged in a color picker, not yet in the input

	/* ---------- form helpers ---------- */

	function $field(name) {
		return $form.find('[name="' + PREFIX + "[" + name + ']"]');
	}

	function has(name) {
		return $field(name).length > 0;
	}

	function val(name, fallback) {
		if (Object.prototype.hasOwnProperty.call(overrides, name)) return overrides[name];
		const $f = $field(name);
		if (!$f.length) return fallback;
		const type = ($f.attr("type") || "").toLowerCase();
		if (type === "radio") {
			const v = $f.filter(":checked").val();
			return v === undefined ? fallback : v;
		}
		if (type === "checkbox") {
			return $f.is(":checked");
		}
		const v = $f.val();
		return v === undefined || v === null || v === "" ? fallback : v;
	}

	function row(id) {
		return $form.find("tr.lpr-row-" + id);
	}

	/* ---------- layout: settings left, preview right ---------- */

	const section = cfg.section || new URLSearchParams(location.search).get("sec") || cfg.firstSection || "lpr-general-settings";
	const previewSections = cfg.previewSections || ["lpr-general-settings", "lpr-style-settings", "lpr-drawer-settings"];
	// Free's More Places tab is a showcase of the Pro places, not a settings page with a preview.
	const placesShowcase = section === "lpr-places-settings" && !["cart_recs", "fbt_recs", "checkout_offer", "thankyou_recs", "freeship_bar"].some(has);
	const showPreview = previewSections.indexOf(section) !== -1 && !placesShowcase;
	// Free's Discounts tab is a showcase too: nothing to save.
	$page.toggleClass("lpr-no-save", section === "lpr-documentation" || (section === "lpr-discounts-settings" && !has("discount_bundle")));
	let $screen = null;
	let $note = null;
	let device = "desktop";
	let mode = section === "lpr-drawer-settings" ? "drawer" : "add";

	// More Places: one preview per place. Pro adds the product page, checkout and thank-you page.
	const isPlaces = section === "lpr-places-settings";
	const PLACES = [
		{ id: "cart", toggle: "cart_recs", label: __("Cart", "leo-product-recommendations") },
		{ id: "shipping", toggle: "freeship_bar", label: __("Free shipping", "leo-product-recommendations") },
		{ id: "fbt", toggle: "fbt_recs", label: __("Product", "leo-product-recommendations") },
		{ id: "checkout", toggle: "checkout_offer", label: __("Checkout", "leo-product-recommendations") },
		{ id: "thankyou", toggle: "thankyou_recs", label: __("Thank-you", "leo-product-recommendations") },
	];
	// Free has no place settings (More Places is Pro): the preview shows every place as an example.
	const placesDemo = placesShowcase;
	if (!placesDemo) {
		for (let i = PLACES.length - 1; i >= 0; i--) if (!has(PLACES[i].toggle)) PLACES.splice(i, 1);
	}
	const placeOn = (name) => placesDemo || val(name, false);
	const SHOWCASE = [
		{ id: "drawer", title: __("Cart drawer", "leo-product-recommendations"), text: __("After add to cart, the cart slides in from the side with recommendations under the items. Shoppers add more without leaving the page.", "leo-product-recommendations") },
		{ id: "cart", title: __("Cart page: Complete your order", "leo-product-recommendations"), text: __("Under the cart, products that go with everything in it. Shown as a grid or a slider.", "leo-product-recommendations") },
		{ id: "shipping", title: __("Free shipping bar", "leo-product-recommendations"), text: __("In the cart and the cart drawer: how much more gets free shipping, with products that cost about that much. One tap and shipping is free.", "leo-product-recommendations") },
		{ id: "fbt", title: __("Product page: Frequently bought together", "leo-product-recommendations"), text: __("The product plus a few that go with it, ticked, with the total price and one button to add them all.", "leo-product-recommendations") },
		{ id: "checkout", title: __("Checkout offer", "leo-product-recommendations"), text: __("One or two small extras above the payment options. One tick adds them to the order.", "leo-product-recommendations") },
		{ id: "thankyou", title: __("Thank-you page", "leo-product-recommendations"), text: __("Right after the order, products that go with what was just bought, for the next order.", "leo-product-recommendations") },
	];
	let $showcase = null;
	if (placesShowcase) {
		$page.addClass("lpr-no-save"); // nothing to save on this tab in Free
		const proLink = $form.find(".lpr-field-pro_note a.button").attr("href") || "";
		let html = '<div class="lpr-showcase">';
		SHOWCASE.forEach((p, i) => {
			html += '<section class="lpr-showcase__item" data-place="' + p.id + '"><div class="lpr-showcase__text"><span class="lpr-showcase__num">' + (i + 1) + "</span><h3>" + esc(p.title) + "</h3><p>" + esc(p.text) + '</p></div><div class="lpr-showcase__frame"><div class="lpr-showcase__screen"></div></div></section>';
		});
		if (proLink) html += '<div class="lpr-showcase__cta"><h3>' + esc(__("Get all six places with Pro", "leo-product-recommendations")) + "</h3><p>" + esc(__("All six places come with Pro, together with the slider, popup colors and recommendation analytics.", "leo-product-recommendations")) + '</p><a class="button button-primary" href="' + esc(proLink) + '" target="_blank" rel="noopener">' + esc(__("Get Pro", "leo-product-recommendations")) + "</a></div>";
		html += "</div>";
		$showcase = $(html);
		const $table = $form.find("table.form-table").filter(function () {
			return !$(this).closest(".hidden").length;
		}).first();
		if ($table.length) $table.after($showcase);
		else $form.append($showcase);
	}
	if (isPlaces && PLACES.length) mode = PLACES[0].id;

	if (showPreview) {
		const $tabs = $form.find(".nav-tab-wrapper").first();
		const $layout = $('<div class="lpr-ui-layout"><div class="lpr-ui-main"></div><aside class="lpr-ui-aside"></aside></div>');
		const $rest = $tabs.length ? $tabs.nextAll() : $form.children();
		if ($tabs.length) $tabs.after($layout);
		else $form.prepend($layout);
		$layout.find(".lpr-ui-main").append($rest);
		$layout.find(".lpr-ui-aside").append(previewMarkup());
		$screen = $layout.find(".lpr-preview__screen");
		$note = $layout.find(".lpr-preview__note");
	}

	function previewMarkup() {
		let modes = section === "lpr-general-settings" && cfg.drawer
			? '<div class="lpr-preview__seg" data-group="mode"><button type="button" data-value="add" aria-pressed="true">' + __("After add to cart", "leo-product-recommendations") + '</button><button type="button" data-value="icon" aria-pressed="false">' + __("Cart icon click", "leo-product-recommendations") + "</button></div>"
			: "";
		if (isPlaces && PLACES.length > 1) {
			modes = '<div class="lpr-preview__seg" data-group="mode">' + PLACES.map((p, i) => '<button type="button" data-value="' + p.id + '" aria-pressed="' + (i === 0 ? "true" : "false") + '">' + esc(p.label) + "</button>").join("") + "</div>";
		}
		return (
			'<div class="lpr-preview" aria-label="' + __("Live preview", "leo-product-recommendations") + '">' +
			'<div class="lpr-preview__bar"><span class="lpr-preview__title">' + __("Live preview", "leo-product-recommendations") + "</span>" + modes +
			'<div class="lpr-preview__seg" data-group="device">' +
			'<button type="button" data-value="desktop" aria-pressed="true">' + __("Desktop", "leo-product-recommendations") + "</button>" +
			'<button type="button" data-value="tablet" aria-pressed="false">' + __("Tablet", "leo-product-recommendations") + "</button>" +
			'<button type="button" data-value="mobile" aria-pressed="false">' + __("Mobile", "leo-product-recommendations") + "</button>" +
			"</div>" +
			'<button type="button" class="lpr-preview__expand" aria-expanded="false">' + __("Enlarge", "leo-product-recommendations") + "</button>" +
			"</div>" +
			'<div class="lpr-preview__stage"><div class="lpr-preview__screen"></div></div>' +
			'<p class="lpr-preview__note"></p>' +
			'<div class="lpr-preview__foot"><span>' + (placesDemo ? __("Your theme can change fonts and buttons a little.", "leo-product-recommendations") : __("Changes show here before you save. Your theme can change fonts and buttons a little.", "leo-product-recommendations")) + "</span>" +
			(cfg.testUrl && !placesDemo ? '<a href="' + esc(cfg.testUrl) + '" target="_blank" rel="noopener">' + __("Test on a product page", "leo-product-recommendations") + "</a>" : "") +
			"</div></div>"
		);
	}

	// Enlarge: show the preview over the page at a bigger size.
	function setExpanded(open) {
		const $p = $page.find(".lpr-preview");
		$p.toggleClass("is-expanded", open);
		$("body").toggleClass("lpr-preview-open", open);
		$p.find(".lpr-preview__expand").attr("aria-expanded", open ? "true" : "false").text(open ? __("Close", "leo-product-recommendations") : __("Enlarge", "leo-product-recommendations"));
		render();
	}

	$page.on("click", ".lpr-preview__expand", function () {
		setExpanded(!$page.find(".lpr-preview").hasClass("is-expanded"));
	});

	$(document).on("keydown", function (e) {
		if (e.key === "Escape" && $page.find(".lpr-preview").hasClass("is-expanded")) setExpanded(false);
	});

	$page.on("click", ".lpr-preview__seg button", function () {
		const $b = $(this);
		const group = $b.parent().data("group");
		$b.parent().find("button").attr("aria-pressed", "false");
		$b.attr("aria-pressed", "true");
		if (group === "device") device = $b.data("value");
		if (group === "mode") mode = $b.data("value");
		render();
	});

	// More Places: the preview follows the card being edited.
	function showPlace(row) {
		const m = /(?:^|\s)lpr-group-(cart|fbt|checkout|thankyou|freeship)(?:\s|$)/.exec(row.className);
		const id = m ? (m[1] === "freeship" ? "shipping" : m[1]) : "";
		if (!m || id === mode || !PLACES.some((p) => p.id === id)) return;
		mode = id;
		$page.find('.lpr-preview__seg[data-group="mode"] button').each(function () {
			$(this).attr("aria-pressed", $(this).data("value") === mode ? "true" : "false");
		});
		render();
	}

	// Hover switches after a short pause, so moving the mouse across other cards on the
	// way to the preview does not make it flicker. Keyboard focus switches at once.
	let hoverTimer = null;
	$form.on("mouseenter", "tr.lpr-group", function () {
		if (!isPlaces) return;
		const row = this;
		clearTimeout(hoverTimer);
		hoverTimer = setTimeout(() => showPlace(row), 150);
	});
	$form.on("mouseleave", "tr.lpr-group", () => clearTimeout(hoverTimer));
	$form.on("focusin click", "tr.lpr-group", function () {
		if (isPlaces) showPlace(this);
	});

	/* ---------- swatches ---------- */

	function setColor($input, color) {
		if (!$input.length) return;
		$input.val(color);
		if ($.fn.spectrum && $input.data("spectrum.id") !== undefined) {
			$input.spectrum("set", color || null);
		}
		$input.trigger("change");
	}

	function darker(hex) {
		const m = /^#?([0-9a-f]{6})$/i.exec(hex || "");
		if (!m) return hex;
		const n = parseInt(m[1], 16);
		const f = (c) => Math.max(0, Math.round(c * 0.85));
		const r = f(n >> 16), g = f((n >> 8) & 255), b = f(n & 255);
		return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
	}

	const probe = document.createElement("span");
	function norm(c) {
		c = String(c || "").trim();
		if (!c) return "";
		probe.style.color = "";
		probe.style.color = c;
		if (!probe.style.color) return c.toLowerCase();
		document.body.appendChild(probe);
		const out = getComputedStyle(probe).color.replace(/\s+/g, "");
		probe.remove();
		return out;
	}

	function syncSwatches($fieldset) {
		const current = norm($fieldset.find(".lpr-swatch-input").val());
		$fieldset.find(".lpr-swatch").each(function () {
			const on = norm($(this).data("color")) === current;
			$(this).toggleClass("is-active", on).attr("aria-pressed", on ? "true" : "false");
		});
	}

	function applyAlso($fieldset, color) {
		const also = String($fieldset.data("also") || "").split(",").filter(Boolean);
		also.forEach((name) => {
			const hover = /hover/.test(name);
			setColor($field(name), hover ? darker(color) : color);
		});
	}

	$form.on("click", ".lpr-swatch", function (e) {
		e.preventDefault();
		const $fieldset = $(this).closest(".lpr-field-swatches");
		const color = String($(this).data("color") || "");
		setColor($fieldset.find(".lpr-swatch-input"), color);
		if (color) applyAlso($fieldset, color);
		syncSwatches($fieldset);
		render();
	});

	$form.on("change", ".lpr-swatch-input", function () {
		syncSwatches($(this).closest(".lpr-field-swatches"));
	});

	// Live color while dragging in any color picker.
	$form.on("move.spectrum", "input.color-picker", function (e, color) {
		const name = /\[([^\]]+)\]$/.exec(this.name || "");
		if (!name) return;
		overrides[name[1]] = color ? color.toRgbString() : "";
		render();
	});

	$form.on("change.spectrum hide.spectrum", "input.color-picker", function () {
		const name = /\[([^\]]+)\]$/.exec(this.name || "");
		if (name) delete overrides[name[1]];
		const $fieldset = $(this).closest(".lpr-field-swatches");
		if ($fieldset.length && this.value && this.dataset.lprLast !== this.value) {
			this.dataset.lprLast = this.value;
			applyAlso($fieldset, this.value);
		}
		render();
	});

	/* ---------- steppers ---------- */

	$form.on("click", ".lpr-step", function (e) {
		e.preventDefault();
		const $input = $(this).siblings('input[type="number"]');
		const min = parseInt($input.attr("min"), 10);
		const max = parseInt($input.attr("max"), 10);
		let v = parseInt($input.val(), 10) || 0;
		v += parseInt($(this).data("step"), 10);
		if (!isNaN(min)) v = Math.max(min, v);
		if (!isNaN(max)) v = Math.min(max, v);
		$input.val(v).trigger("change");
	});

	/* ---------- popup size presets ---------- */

	const SIZES = { small: [760, 720, 600], medium: [1000, 720, 600], large: [1200, 900, 600] };

	function presetFromSizes() {
		const now = [val("popup_lg_size"), val("popup_md_size"), val("popup_sm_size")].map((v) => parseInt(v, 10));
		for (const key in SIZES) {
			if (SIZES[key].every((n, i) => n === now[i])) return key;
		}
		return "custom";
	}

	if (has("popup_size_preset") && !$field("popup_size_preset").filter(":checked").length) {
		$field("popup_size_preset").filter('[value="' + presetFromSizes() + '"]').prop("checked", true);
	}

	$form.on("change", '[name="' + PREFIX + '[popup_size_preset]"]', function () {
		const sizes = SIZES[this.value];
		if (!sizes) return;
		["popup_lg_size", "popup_md_size", "popup_sm_size"].forEach((name, i) => $field(name).val(sizes[i]));
	});

	/* ---------- rows that only apply in some cases ---------- */

	function applyRules() {
		const layout = val("layout_type", "grid");
		const hasLayout = has("layout_type");
		const after = val("after_add_display", "popup");
		const icon = val("drawer_cart_icon", "page");

		const rules = {
			grid_lg_items: !hasLayout || layout === "grid",
			grid_options: !hasLayout || layout === "grid",
			slider_lg_items: layout === "slider",
			slider_options: layout === "slider",
			popup_view_times: !has("after_add_display") || after === "popup",
			popup_sizes: !has("popup_size_preset") || val("popup_size_preset") === "custom",
		};

		// Modern popup: its own layout, size and colors, so the classic ones don't apply.
		if (has("popup_design")) {
			const classic = val("popup_design", "modern") === "classic";
			["layout_type", "grid_lg_items", "slider_lg_items", "grid_options", "slider_options", "popup_size_preset", "popup_sizes", "color_settings", "product_color_setting", "heading_text_color", "notification_bar_bg", "layout_image", "popup_size_image", "popup_color_image"].forEach((id) => {
				if (!classic) rules[id] = false;
				else if (!Object.prototype.hasOwnProperty.call(rules, id)) rules[id] = true;
			});
		}

		// Discounts (Pro): percentage or fixed amount, and the bigger saving for more products.
		["bundle", "addon", "offer"].forEach((kind) => {
			if (!has("discount_" + kind + "_type")) return;
			const fixed = val("discount_" + kind + "_type", "percent") === "fixed";
			rules["discount_" + kind + "_pct"] = !fixed;
			rules["discount_" + kind + "_amount"] = fixed;
			if (has("discount_" + kind + "_tiers")) rules["discount_" + kind + "_tier_values"] = !!val("discount_" + kind + "_tiers", false);
			row("discount_" + kind + "_tier_values").find(".lpr-field-deal_tiers").toggleClass("is-fixed", fixed);
		});

		// Free shipping bar (Pro): own amount only with "An amount I set", the number only with products.
		if (has("freeship_source")) rules.freeship_amount = val("freeship_source", "auto") === "custom";
		if (has("freeship_products")) rules.freeship_number = !!val("freeship_products", false);

		Object.keys(rules).forEach((id) => row(id).toggleClass("lpr-rule-hidden", !rules[id]));

		// Store-wide rule: hand-picked products or categories and tags.
		if (has("global_rule_type")) {
			const ruleType = val("global_rule_type", "rule");
			row("global_products").toggleClass("lpr-rule-hidden", ruleType !== "manual");
			row("selection_options").toggleClass("lpr-rule-hidden", ruleType !== "rule");
			row("smart_status").toggleClass("lpr-rule-hidden", ruleType !== "smart");
			row("smart_months").toggleClass("lpr-rule-hidden", ruleType !== "smart");
			row("smart_options").toggleClass("lpr-rule-hidden", ruleType !== "smart");
		}

		// Store-wide rule: the category picker only matters for "Manual".
		if (has("global_categories")) {
			$form.find(".category-selector").toggleClass("lpr-rule-hidden", val("global_categories", "manual_categories") !== "manual_categories");
		}

		// The Popup tab only matters while the popup is what shows after add to cart.
		const $popupTab = $form.find('.nav-tab[href*="sec=lpr-style-settings"]');
		$popupTab.toggleClass("lpr-rule-hidden", section !== "lpr-style-settings" && has("after_add_display") && after !== "popup");

		const $drawerTab = $form.find('.nav-tab[href*="sec=lpr-drawer-settings"]');
		$drawerTab.toggleClass("lpr-rule-hidden", section !== "lpr-drawer-settings" && has("after_add_display") && !(after === "drawer" || icon === "drawer"));
	}

	/* ---------- "More options" ---------- */

	function setupMoreOptions() {
		$form.find("table.form-table").each(function (index) {
			const $table = $(this);
			const $rows = $table.find("> tbody > tr.lpr-advanced");
			if (!$rows.length) return;

			const key = "lpr-more-" + section + "-" + index;
			let open = false;
			try {
				open = localStorage.getItem(key) === "1";
			} catch (e) {}

			const $btn = $('<button type="button" class="lpr-more-toggle"></button>');
			const label = () => (open ? __("Fewer options", "leo-product-recommendations") : __("More options", "leo-product-recommendations") + " (" + $rows.length + ")");
			const sync = () => {
				$rows.toggleClass("lpr-collapsed", !open);
				$btn.text(label()).attr("aria-expanded", open ? "true" : "false");
			};
			$btn.on("click", () => {
				open = !open;
				try {
					localStorage.setItem(key, open ? "1" : "0");
				} catch (e) {}
				sync();
			});

			// The extra rows go to the end of the list, right under the button,
			// so opening them shows the new options where you clicked.
			const $tbody = $table.children("tbody");
			const $btnRow = $('<tr class="lpr-more-row"><td colspan="2"></td></tr>');
			$btnRow.children("td").append($btn);
			$tbody.append($btnRow);
			$rows.appendTo($tbody);
			sync();
		});
	}

	/* ---------- Smart picks showcase (Free) ---------- */

	// The showcase card stays closed so it does not get in the way of the settings.
	// Clicking the locked Smart picks option opens it right below; again, or x, closes it.
	function toggleTeaser(open) {
		const card = document.getElementById("lpr-smart-teaser");
		if (!card) return;
		const row = card.closest("tr");
		row.classList.toggle("is-open", open);
		$(row).closest("tbody").toggleClass("lpr-teaser-open", open);
		$(".lpr-radio-locked").toggleClass("is-open", open).attr("aria-expanded", open ? "true" : "false")
			.closest("fieldset").toggleClass("lpr-teaser-open", open);
		if (open) {
			card.classList.remove("is-flash");
			void card.offsetWidth;
			card.classList.add("is-flash");
			const r = card.getBoundingClientRect();
			if (r.bottom > window.innerHeight) card.scrollIntoView({ behavior: "smooth", block: "nearest" });
		}
	}
	$(document).on("click", ".lpr-radio-locked", function (e) {
		e.preventDefault();
		const row = $("#lpr-smart-teaser").closest("tr")[0];
		toggleTeaser(!(row && row.classList.contains("is-open")));
	});
	// Choosing another option (even the one already selected) closes the card.
	$(document).on("click", "fieldset.lpr-field-radio > label:not(.lpr-radio-locked)", function () {
		if ($(this).siblings(".lpr-radio-locked").length) toggleTeaser(false);
	});
	$(document).on("click", ".lpr-smart-teaser__close", () => {
		toggleTeaser(false);
		$(".lpr-radio-locked").trigger("focus");
	});

	/* ---------- unsaved changes ---------- */

	let saved = null;
	let dirty = false;
	const $unsaved = $(
		'<div class="lpr-unsaved" role="status"><span class="lpr-unsaved__text">' +
			__("You have unsaved changes.", "leo-product-recommendations") +
			'</span><span><button type="button" class="button lpr-unsaved__discard">' +
			__("Discard", "leo-product-recommendations") +
			'</button> <button type="submit" class="button button-primary">' +
			__("Save Settings", "leo-product-recommendations") +
			"</button></span></div>"
	);

	function snapshot() {
		if (window.tinymce) {
			window.tinymce.triggerSave();
		}
		return $form.serialize();
	}

	function checkDirty() {
		if (saved === null) return;
		dirty = snapshot() !== saved;
		$unsaved.toggleClass("is-visible", dirty);
	}

	$unsaved.on("click", ".lpr-unsaved__discard", () => {
		dirty = false;
		location.reload();
	});

	$form.on("submit", () => {
		dirty = false;
	});

	window.addEventListener("beforeunload", (e) => {
		if (!dirty) return;
		e.preventDefault();
		e.returnValue = "";
	});

	/* ---------- preview ---------- */

	let products = [];

	function esc(s) {
		return String(s === undefined || s === null ? "" : s).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
	}

	function safeColor(c, fallback) {
		c = String(c || "").trim();
		return /^(#[0-9a-f]{3,8}|rgba?\([\d.,%\s]+\)|hsla?\([\d.,%\s]+\)|[a-z]+)$/i.test(c) ? c : fallback;
	}

	function colors() {
		const c = (name, fb) => safeColor(val(name, ""), fb);
		const button = c("checkout_bg", "#4cc491");
		return {
			modalBg: c("modal_bg_color", "#ffffff"),
			bar: c("notification_bar_bg", "#f5f5f5"),
			icon: c("notification_icon_color", "#4cc491"),
			text: c("notification_text_color", "#555555"),
			viewCart: c("view_cart_color", "#4cc491"),
			cont: c("continue_shopping_bg", button),
			checkout: button,
			close: c("modal_close_icon_color", button),
			head: c("heading_text_color", "#555555"),
			headBg: c("heading_background_color", "#ffffff"),
			overlay: c("overlay_color", "rgba(0,0,0,.5)"),
			title: c("product_title_color", "#1d2327"),
			price: c("price_color", "#50575e"),
			btnBg: c("button_background_color", "#1d2327"),
			btnText: c("button_text_color", "#ffffff"),
			productBg: c("product_background_color", "#ffffff"),
		};
	}

	function headingHtml(count) {
		const type = val("heading_type", "default_heading");
		const name = products[0] ? products[0].name : "Black Hoodie";
		const pattern = (s) =>
			String(s || "")
				.replace(/%title%/g, name)
				.replace(/\[([^\[\],]+),([^\[\]]+)\]/g, (m, one, many) => (count === 1 ? one.trim() : many.trim()));
		if (type === "default_heading_description") {
			let html = "";
			const ed = window.tinymce && window.tinymce.get("default-heading-editor");
			html = ed && !ed.isHidden() ? ed.getContent() : String($("#default-heading-editor").val() || "");
			const tmp = document.createElement("div");
			tmp.innerHTML = html; // admin's own content, shown only in their own preview
			tmp.querySelectorAll("script,style,iframe,object,embed").forEach((n) => n.remove());
			const headingEl = tmp.querySelector("h1,h2,h3,h4,h5,h6");
			const heading = headingEl ? headingEl.textContent : "";
			if (headingEl) headingEl.remove();
			return { heading: pattern(heading), desc: pattern(tmp.innerHTML) };
		}
		return { heading: pattern(val("default_heading", "")), desc: "" };
	}

	function sample(i) {
		const p = products.length ? products[i % products.length] : null;
		return p || { name: "Product", price: "", image: "" };
	}

	function img(p) {
		return p.image ? ' style="background-image:url(' + esc(p.image) + ')"' : "";
	}

	// Real screen sizes. The preview draws the page at this size and scales it down,
	// so the popup keeps its true proportions.
	const DEVICES = {
		desktop: { real: 1280, h: 800, px: 500, items: "lg" },
		tablet: { real: 820, h: 1100, px: 360, items: "md" },
		mobile: { real: 390, h: 800, px: 230, items: "sm" },
	};

	// Screen sizes follow the width of the preview card.
	function sizeDevices() {
		if (!$screen) return;
		const stage = Math.max(240, Math.floor($screen.parent().innerWidth() - 24));
		DEVICES.desktop.px = stage;
		DEVICES.mobile.px = Math.min(stage, Math.max(200, Math.min(320, Math.round(stage * 0.45))));
		DEVICES.tablet.px = Math.min(stage, Math.max(DEVICES.mobile.px + 80, Math.round(stage * 0.72)));
	}

	$(window).on("resize", () => {
		sizeDevices();
		render();
	});

	function renderPopup(col) {
		const d = DEVICES[device];
		const sizeName = { lg: "popup_lg_size", md: "popup_md_size", sm: "popup_sm_size" }[d.items];
		const defaultSize = { lg: 1000, md: 720, sm: 600 }[d.items];
		const width = Math.min(parseInt(val(sizeName, defaultSize), 10) || defaultSize, d.real - 30);
		const layout = val("layout_type", "grid");
		const isSlider = layout === "slider";
		const itemsKey = (isSlider ? "slider_" : "grid_") + d.items + "_items";
		const itemsDefault = { lg: 4, md: 3, sm: 2 }[d.items];
		const cols = Math.max(1, Math.min(6, parseInt(val(itemsKey, itemsDefault), 10) || itemsDefault));
		const count = isSlider ? cols : Math.max(cols, Math.min(4, products.length - 1 || 4));
		const recs = [];
		for (let i = 1; i <= count; i++) recs.push(sample(i));
		const h = headingHtml(recs.length);
		const showCont = val("show_continue_shopping", true);
		const showCheckout = val("show_go_check_out", true);
		const showClose = val("show_close_icon", true);
		const added = sample(0);

		let html = '<div class="lpr-preview__overlay" style="background:' + col.overlay + '"></div>';
		html += '<div class="lprp-popup" style="width:' + width.toFixed(0) + "px;background:" + col.modalBg + '">';
		html += '<div class="lprp-msg" style="background:' + col.bar + ";color:" + col.text + '">';
		html += '<span class="lprp-msg__text"><span class="lprp-thumb"' + img(added) + '></span><span style="color:' + col.icon + '">&#10003;</span><span><strong>' + esc(added.name) + "</strong> " + esc(__("has been added to your cart.", "leo-product-recommendations")) + ' <span style="color:' + col.viewCart + '">' + esc(__("View cart", "leo-product-recommendations")) + " &rarr;</span></span></span>";
		html += '<span class="lprp-btns">';
		if (showCont) html += '<span class="lprp-btn" style="background:' + col.cont + '">' + esc(__("Continue Shopping", "leo-product-recommendations")) + "</span>";
		if (showCheckout) html += '<span class="lprp-btn" style="background:' + col.checkout + '">' + esc(__("Checkout", "leo-product-recommendations")) + "</span>";
		html += "</span></div>";
		if (showClose) html += '<span class="lprp-close" style="background:' + col.close + '">&times;</span>';
		html += '<div class="lprp-heading" style="color:' + col.head + ";background:" + col.headBg + '">' + esc(h.heading) + "</div>";
		if (h.desc) html += '<div class="lprp-desc">' + h.desc + "</div>";
		html += '<div class="lprp-grid' + (isSlider ? " is-slider" : "") + '" style="grid-template-columns:repeat(' + cols + ',minmax(0,1fr))">';
		recs.forEach((p) => {
			html += '<div class="lprp-card" style="background:' + col.productBg + '"><div class="lprp-card__img"' + img(p) + '></div>';
			html += '<div class="lprp-card__name" style="color:' + col.title + '">' + esc(p.name) + "</div>";
			html += '<div class="lprp-card__price" style="color:' + col.price + '">' + p.price + "</div>";
			html += '<div class="lprp-card__btn" style="background:' + col.btnBg + ";color:" + col.btnText + '">' + esc(__("Add to cart", "leo-product-recommendations")) + "</div></div>";
		});
		if (isSlider) html += '<span class="lprp-nav lprp-nav--prev">&lsaquo;</span><span class="lprp-nav lprp-nav--next">&rsaquo;</span>';
		html += "</div></div>";
		return html;
	}

	// Modern popup (3.6.0): drawn like the real one. Phones get the sheet from the bottom.
	function renderModernPopup() {
		const d = DEVICES[device];
		const phone = d.items === "sm";
		const accent = safeColor(val("checkout_bg", ""), "#1d2327");
		const count = phone ? 3 : d.items === "md" ? 3 : 4;
		const recs = [];
		for (let i = 1; i <= Math.max(count, 4); i++) recs.push(sample(i));
		const added = sample(0);
		const h = headingHtml(recs.length);
		const own = val("default_heading", "");
		const stock = !own || /You may purchase following/.test(own);
		const article = val("heading_type", "default_heading") === "default_heading_description";
		const heading = (article ? h.heading : !stock && h.heading) || __("Goes well with %s", "leo-product-recommendations").replace("%s", added.name);
		const showCont = val("show_continue_shopping", true);
		const showCheckout = val("show_go_check_out", true);
		const showClose = val("show_close_icon", true);
		const total = cartTotal(2);

		let html = '<div class="lpr-preview__overlay" style="background:rgba(0,0,0,.45)"></div>';
		html += '<div class="lprm' + (phone ? " is-phone" : "") + '" style="--a:' + accent + (phone ? "" : ";width:" + Math.min(880, d.real - 60) + "px") + '">';
		html += '<div class="lprm__head"><span class="lprm__check">&#10003;</span><strong>' + esc(__("Added to your cart", "leo-product-recommendations")) + "</strong>" + (showClose ? '<span class="lprm__close">&times;</span>' : "") + "</div>";
		html += '<div class="lprm__added"><span class="lprm__img"' + img(added) + '></span><span class="lprm__name">' + esc(added.name) + "<small>" + esc(__("Qty 1", "leo-product-recommendations")) + '</small></span><span class="lprm__price">' + added.price + "</span></div>";
		html += '<div class="lprm__sub"><span>' + esc(__("Cart subtotal (2 items)", "leo-product-recommendations")) + "</span><strong>" + money(total) + "</strong></div>";
		html += '<div class="lprm__recs"><div class="lprm__title">' + esc(heading) + "</div>";
		html += '<div class="lprm__row" style="grid-template-columns:repeat(' + recs.length + "," + (phone ? "40%" : "calc((100% - " + (count - 1) * 16 + "px)/" + count + ")") + ')">';
		recs.forEach((p) => {
			html += '<div class="lprm__card"><span class="lprm__cimg"' + img(p) + '></span><span class="lprm__cname">' + esc(p.name) + '</span><span class="lprm__cprice">' + p.price + '</span><span class="lprm__add">' + esc(__("Add", "leo-product-recommendations")) + "</span></div>";
		});
		html += "</div></div>";
		html += '<div class="lprm__foot">';
		if (showCont) html += '<span class="lprm__cont">' + esc(__("Continue shopping", "leo-product-recommendations")) + "</span>";
		html += '<span class="lprm__cart">' + esc(__("View cart", "leo-product-recommendations")) + "</span>";
		if (showCheckout) html += '<span class="lprm__checkout">' + esc(__("Checkout", "leo-product-recommendations")) + "</span>";
		html += "</div></div>";
		return html;
	}

	function renderDrawer(col, afterAdd) {
		const d = DEVICES[device];
		const width = device === "mobile" ? d.real : 420;
		const dc = (name, fb) => safeColor(val(name, ""), fb);
		const accent = dc("drawer_button_color", "#46c28e");
		const heading = dc("drawer_heading_color", "#1d2327");
		const noticeBg = dc("drawer_notice_bg", "#eaf7f1");
		const n = Math.max(1, Math.min(8, parseInt(val("drawer_products_number", 4), 10) || 4));
		const added = sample(0);
		let html = '<div class="lpr-preview__overlay" style="background:rgba(0,0,0,.45)"></div>';
		html += '<div class="lprp-drawer" style="width:' + width.toFixed(0) + 'px">';
		html += '<div class="lprp-drawer__head"><span>' + esc(__("Your cart", "leo-product-recommendations")) + " (1)</span><span>&times;</span></div>";
		if (afterAdd) html += '<div class="lprp-drawer__notice" style="background:' + noticeBg + ';color:#1d2327"><span style="color:' + accent + '">&#10003;</span> ' + esc(added.name) + " " + esc(__("has been added to your cart.", "leo-product-recommendations")) + "</div>";
		html += '<div class="lprp-drawer__item"><span class="lprp-thumb"' + img(added) + "></span><span><strong>" + esc(added.name) + "</strong><br>1 &times; " + added.price + "</span></div>";
		html += '<div class="lprp-drawer__recs"><strong style="color:' + heading + '">' + esc(val("drawer_heading", __("You may also like", "leo-product-recommendations"))) + "</strong>";
		for (let i = 1; i <= n; i++) {
			const p = sample(i);
			html += '<div class="lprp-drawer__rec"><span class="lprp-thumb"' + img(p) + "></span><span>" + esc(p.name) + "<br>" + p.price + '</span><span class="lprp-drawer__add" style="color:' + accent + ";border-color:" + accent + '">' + esc(__("Add", "leo-product-recommendations")) + "</span></div>";
		}
		html += "</div>";
		html += '<div class="lprp-drawer__foot"><span style="border:1px solid #dcdcde">' + esc(__("View cart", "leo-product-recommendations")) + '</span><span style="background:' + accent + ';color:#fff">' + esc(__("Checkout", "leo-product-recommendations")) + "</span></div>";
		html += "</div>";
		return html;
	}

	/* ---------- More Places previews ---------- */

	function money(n) {
		const c = cfg.currency || {};
		const dec = c.decimals === undefined ? 2 : c.decimals;
		let s = Number(n || 0).toFixed(dec);
		let [int, frac] = s.split(".");
		int = int.replace(/\B(?=(\d{3})+(?!\d))/g, c.thousand === undefined ? "," : c.thousand);
		s = frac ? int + (c.decimal || ".") + frac : int;
		return esc((c.format || "%1$s%2$s").replace("%1$s", c.symbol || "$").replace("%2$s", s));
	}

	function amount(p) {
		return typeof p.amount === "number" ? p.amount : parseFloat(p.amount) || 0;
	}

	function placeNumber(name, min, max, def) {
		const n = parseInt(val(name, def), 10);
		return Math.max(min, Math.min(max, isNaN(n) ? def : n));
	}

	function placeText(name, def) {
		const t = String(val(name, "") || "").trim();
		return t || def;
	}

	function recsBlock(heading, count, layout) {
		const slider = layout === "slider";
		const per = slider ? { desktop: 4, tablet: 3, mobile: 1.6 }[device] : { desktop: 4, tablet: 3, mobile: 2 }[device];
		let html = '<div class="lprx-recs' + (slider ? " is-slider" : "") + '"><div class="lprx-recs__head"><h3>' + esc(heading) + "</h3>";
		if (slider && count > Math.floor(per)) html += '<span class="lprx-arrows"><span class="is-off">&lsaquo;</span><span>&rsaquo;</span></span>';
		html += '</div><div class="lprx-recs__list" style="--per:' + per + '">';
		for (let i = 0; i < count; i++) {
			const p = sample(i + 2);
			html += '<div class="lprx-card"><div class="lprx-card__img"' + img(p) + '></div><div class="lprx-card__name">' + esc(p.name) + '</div><div class="lprx-card__price">' + p.price + '</div><div class="lprx-card__btn">' + esc(__("Add to cart", "leo-product-recommendations")) + "</div></div>";
		}
		return html + "</div></div>";
	}

	function cartRows(n) {
		let html = "";
		for (let i = 0; i < n; i++) {
			const p = sample(i);
			html += '<div class="lprx-row"><span class="lprx-thumb"' + img(p) + '></span><span class="lprx-row__name">' + esc(p.name) + '<small>' + p.price + '</small></span><span class="lprx-qty">1</span><span class="lprx-row__sum">' + money(amount(p)) + "</span></div>";
		}
		return html;
	}

	function cartTotal(n) {
		let t = 0;
		for (let i = 0; i < n; i++) t += amount(sample(i));
		return t;
	}

	function renderCartPlace() {
		const on = placeOn("cart_recs");
		let html = '<div class="lprx-page is-cart"><h1>' + esc(__("Cart", "leo-product-recommendations")) + "</h1>";
		html += '<div class="lprx-box">' + cartRows(2) + '<div class="lprx-actions"><span class="lprx-input">' + esc(__("Coupon code", "leo-product-recommendations")) + '</span><span class="lprx-btn is-light">' + esc(__("Update cart", "leo-product-recommendations")) + "</span></div></div>";
		if (on) html += recsBlock(placeText("cart_recs_heading", __("Complete your order", "leo-product-recommendations")), placeNumber("cart_recs_number", 2, 8, 4), val("cart_recs_layout", "grid"));
		html += '<div class="lprx-totals"><h3>' + esc(__("Cart totals", "leo-product-recommendations")) + '</h3><div class="lprx-line"><span>' + esc(__("Total", "leo-product-recommendations")) + "</span><strong>" + money(cartTotal(2)) + '</strong></div><span class="lprx-btn">' + esc(__("Proceed to checkout", "leo-product-recommendations")) + "</span></div>";
		return html + "</div>";
	}

	function renderShippingPlace() {
		const on = placeOn("freeship_bar");
		const total = cartTotal(2);
		const own = val("freeship_source", "auto") === "custom" ? parseFloat(val("freeship_amount", "")) : NaN;
		// An example goal a little above the cart, unless the owner set one.
		const goal = own > total ? own : Math.ceil((total * 1.35) / 10) * 10;
		const left = Math.max(0, goal - total);
		let html = '<div class="lprx-page is-cart"><h1>' + esc(__("Cart", "leo-product-recommendations")) + "</h1>";
		if (on) {
			html += '<div class="lprx-freeship"><p>' + esc(__("Add %s more to get free shipping.", "leo-product-recommendations")).replace("%s", "<strong>" + money(left) + "</strong>") + "</p>";
			html += '<span class="lprx-freeship__track"><span style="width:' + Math.round((total / goal) * 100) + '%"></span></span>';
			if (val("freeship_products", true) !== false) {
				const pool = [];
				for (let i = 2; i < Math.max(products.length, 6); i++) pool.push(sample(i));
				pool.sort((a, b) => Math.abs(amount(a) - left) - Math.abs(amount(b) - left));
				pool.slice(0, placeNumber("freeship_number", 1, 4, 3)).forEach((p) => {
					html += '<div class="lprx-freeship__row"><span class="lprx-thumb"' + img(p) + '></span><span class="lprx-freeship__name">' + esc(p.name) + "<small>" + p.price + '</small></span><span class="lprx-btn is-light">' + esc(__("Add", "leo-product-recommendations")) + "</span></div>";
				});
			}
			html += "</div>";
		}
		html += '<div class="lprx-box">' + cartRows(2) + "</div>";
		html += '<div class="lprx-totals"><h3>' + esc(__("Cart totals", "leo-product-recommendations")) + '</h3><div class="lprx-line"><span>' + esc(__("Total", "leo-product-recommendations")) + "</span><strong>" + money(total) + '</strong></div><span class="lprx-btn">' + esc(__("Proceed to checkout", "leo-product-recommendations")) + "</span></div>";
		return html + "</div>";
	}

	function renderFbtPlace() {
		const on = placeOn("fbt_recs");
		const main = sample(0);
		let html = '<div class="lprx-page lprx-product"><div class="lprx-product__img"' + img(main) + "></div>";
		html += '<div class="lprx-product__info"><h1>' + esc(main.name) + '</h1><div class="lprx-product__price">' + main.price + '</div><p class="lprx-lines"><span></span><span></span><span></span></p>';
		html += '<div class="lprx-buy"><span class="lprx-qty">1</span><span class="lprx-btn">' + esc(__("Add to cart", "leo-product-recommendations")) + "</span></div>";
		if (on) {
			const n = placeNumber("fbt_number", 1, 4, 2);
			const items = [main];
			for (let i = 0; i < n; i++) items.push(sample(i + 2));
			let total = 0;
			items.forEach((p) => (total += amount(p)));
			html += '<div class="lprx-fbt"><h3>' + esc(placeText("fbt_heading", __("Frequently bought together", "leo-product-recommendations"))) + '</h3><div class="lprx-fbt__imgs">';
			html += items.map((p) => '<span class="lprx-thumb is-big"' + img(p) + "></span>").join('<span class="lprx-plus">+</span>');
			html += "</div><ul>";
			items.forEach((p, i) => {
				html += '<li><span class="lprx-check">&#10003;</span><span>' + (i === 0 ? "<strong>" + esc(__("This item:", "leo-product-recommendations")) + "</strong> " : "") + esc(p.name) + "</span><span>" + p.price + "</span></li>";
			});
			html += '</ul><div class="lprx-fbt__total">' + esc(__("Total:", "leo-product-recommendations")) + " <strong>" + money(total) + '</strong></div><span class="lprx-btn">' + esc(/* translators: %d: number of products */ __("Add all %d to cart", "leo-product-recommendations").replace("%d", items.length)) + "</span></div>";
		}
		return html + "</div></div>";
	}

	function renderCheckoutPlace() {
		const on = placeOn("checkout_offer");
		const subtotal = cartTotal(2);
		const share = placeNumber("checkout_offer_share", 5, 100, 30);
		const limit = (subtotal * share) / 100;
		const n = placeNumber("checkout_offer_number", 1, 3, 2);
		// Cheap products first, like the real offer.
		const pool = [];
		for (let i = 2; i < Math.max(products.length, 6); i++) pool.push(sample(i));
		pool.sort((a, b) => amount(a) - amount(b));
		const within = pool.filter((p) => !limit || amount(p) <= limit);
		const offers = (within.length ? within : pool).slice(0, n);

		let html = '<div class="lprx-page"><h1>' + esc(__("Checkout", "leo-product-recommendations")) + '</h1><div class="lprx-co">';
		html += '<div class="lprx-co__form"><h3>' + esc(__("Billing details", "leo-product-recommendations")) + "</h3>";
		const fields = device === "mobile" ? 3 : 6;
		for (let i = 0; i < fields; i++) html += '<span class="lprx-input is-field"></span>';
		html += '</div><div class="lprx-co__side"><h3>' + esc(__("Your order", "leo-product-recommendations")) + "</h3>";
		for (let i = 0; i < 2; i++) {
			const p = sample(i);
			html += '<div class="lprx-line"><span>' + esc(p.name) + " &times; 1</span><span>" + money(amount(p)) + "</span></div>";
		}
		html += '<div class="lprx-line is-total"><span>' + esc(__("Total", "leo-product-recommendations")) + "</span><strong>" + money(subtotal) + "</strong></div>";
		if (on) {
			html += '<div class="lprx-offer"><h4>' + esc(placeText("checkout_offer_heading", __("Last chance to add", "leo-product-recommendations"))) + "</h4>";
			offers.forEach((p) => {
				html += '<div class="lprx-offer__row"><span class="lprx-box-check"></span><span class="lprx-thumb"' + img(p) + '></span><span class="lprx-offer__name">' + esc(p.name) + "<small>" + p.price + "</small></span></div>";
			});
			html += "</div>";
		}
		html += '<div class="lprx-pay"><span class="lprx-radio"></span>' + esc(__("Cash on delivery", "leo-product-recommendations")) + '</div><span class="lprx-btn is-wide">' + esc(__("Place order", "leo-product-recommendations")) + "</span>";
		return html + "</div></div></div>";
	}

	function renderThankyouPlace() {
		const on = placeOn("thankyou_recs");
		let html = '<div class="lprx-page"><h1>' + esc(__("Order received", "leo-product-recommendations")) + '</h1><p class="lprx-thanks">' + esc(__("Thank you. Your order has been received.", "leo-product-recommendations")) + "</p>";
		html += '<div class="lprx-overview">';
		[[__("Order number:", "leo-product-recommendations"), "1024"], [__("Total:", "leo-product-recommendations"), money(cartTotal(2))], [__("Payment method:", "leo-product-recommendations"), esc(__("Cash on delivery", "leo-product-recommendations"))]].forEach((r) => {
			html += "<span>" + esc(r[0]) + "<strong>" + r[1] + "</strong></span>";
		});
		html += "</div>";
		if (on) html += recsBlock(placeText("thankyou_heading", __("You may also like", "leo-product-recommendations")), placeNumber("thankyou_number", 2, 8, 4), val("thankyou_layout", "grid"));
		html += '<div class="lprx-box"><h3>' + esc(__("Order details", "leo-product-recommendations")) + "</h3>" + cartRows(2) + "</div>";
		return html + "</div>";
	}

	function renderPlace() {
		const place = PLACES.find((p) => p.id === mode) || PLACES[0];
		const on = place ? placeOn(place.toggle) : false;
		const notes = {
			cart: __("The cart page with recommendations under the cart table.", "leo-product-recommendations"),
			shipping: __("The cart page with the free shipping bar above the cart. The cart drawer shows it under its header. The amount here is an example.", "leo-product-recommendations"),
			fbt: __("The product page with Frequently bought together under the add to cart button.", "leo-product-recommendations"),
			checkout: __("The checkout with the offer above the payment options. Only products up to the price limit are offered.", "leo-product-recommendations"),
			thankyou: __("The order received page with recommendations for the next order.", "leo-product-recommendations"),
		};
		const html = { cart: renderCartPlace, shipping: renderShippingPlace, fbt: renderFbtPlace, checkout: renderCheckoutPlace, thankyou: renderThankyouPlace }[place ? place.id : "cart"]();
		return {
			html: '<div class="lprx device-' + device + '">' + html + "</div>",
			note: placesDemo
				? __("An example with your products. More Places is part of Pro.", "leo-product-recommendations")
				: on ? notes[place.id] : __("This place is switched off, so shoppers see the normal page.", "leo-product-recommendations"),
		};
	}

	// Free: every place drawn at real desktop size, scaled to the card.
	function drawShowcase() {
		if (!$showcase) return;
		const col = colors();
		const saved = { mode, device };
		device = "desktop";
		const real = DEVICES.desktop.real;
		const h = 720;
		$showcase.find(".lpr-showcase__item").each(function () {
			const id = $(this).data("place");
			const $s = $(this).find(".lpr-showcase__screen");
			const w = Math.max(200, Math.floor($s.parent().width()));
			const scale = w / real;
			$s.css({ width: w + "px", height: Math.round(h * scale) + "px" });
			let body = '<div class="lpr-preview__header"><span>SHOP</span><span>&#128722; 2</span></div>';
			if (id === "drawer") {
				body += '<div class="lpr-preview__shop"><span></span><span></span><span></span><span></span><span></span><span></span></div>' + renderDrawer(col, true);
			} else {
				mode = id;
				body += renderPlace().html;
			}
			$s.html('<div class="lpr-preview__viewport" style="width:' + real + "px;height:" + h + "px;transform:scale(" + scale.toFixed(4) + ')">' + body + "</div>");
		});
		mode = saved.mode;
		device = saved.device;
	}

	let renderTimer = null;
	function render() {
		applyRules();
		if ($showcase) {
			clearTimeout(renderTimer);
			renderTimer = setTimeout(drawShowcase, 30);
			return;
		}
		if (!$screen) return;
		clearTimeout(renderTimer);
		renderTimer = setTimeout(draw, 30);
	}

	function draw() {
		sizeDevices();
		const d = DEVICES[device];
		const col = colors();
		const after = val("after_add_display", "popup");
		const icon = val("drawer_cart_icon", "page");
		const expanded = $page.find(".lpr-preview").hasClass("is-expanded");
		const maxH = expanded ? Math.max(320, window.innerHeight - 190) : 620;
		const scale = Math.min(d.px / d.real, maxH / d.h);
		$screen.css({ width: Math.round(d.real * scale) + "px", height: Math.round(d.h * scale) + "px" });

		let body = '<div class="lpr-preview__header"><span>SHOP</span><span>&#128722; 1</span></div><div class="lpr-preview__shop"><span></span><span></span><span></span><span></span><span></span><span></span></div>';
		let note = "";

		if (isPlaces && mode === "drawer") {
			body += renderDrawer(col, true);
			note = __("An example with your products. More Places is part of Pro.", "leo-product-recommendations");
		} else if (isPlaces) {
			const out = renderPlace();
			body = '<div class="lpr-preview__header"><span>SHOP</span><span>&#128722; 2</span></div>' + out.html;
			note = out.note;
		} else if (section === "lpr-drawer-settings") {
			body += renderDrawer(col, true);
			note = __("The cart drawer with your settings.", "leo-product-recommendations");
		} else if (section === "lpr-general-settings" && mode === "icon") {
			if (icon === "drawer") {
				body += renderDrawer(col, false);
				note = __("Clicking the cart icon opens the drawer.", "leo-product-recommendations");
			} else {
				body += '<div class="lprp-cartpage"><strong>' + esc(__("Cart", "leo-product-recommendations")) + "</strong><p>" + esc(__("The cart icon goes to the normal cart page.", "leo-product-recommendations")) + "</p></div>";
				note = __("The cart icon works as your theme set it up.", "leo-product-recommendations");
			}
		} else if (section === "lpr-general-settings" && after === "drawer") {
			body += renderDrawer(col, true);
			note = __("After add to cart, the drawer slides in.", "leo-product-recommendations");
		} else if (section === "lpr-general-settings" && after === "none") {
			body += '<div class="lprp-toast">' + esc(__("Added to cart. No recommendations shown.", "leo-product-recommendations")) + "</div>";
			note = __("Shoppers only see your theme's normal message.", "leo-product-recommendations");
		} else if (val("popup_design", "modern") !== "classic") {
			body += renderModernPopup();
			note = __("The modern popup after add to cart, with products from your store.", "leo-product-recommendations");
		} else {
			body += renderPopup(col);
			note = __("The popup after add to cart, with products from your store.", "leo-product-recommendations");
		}

		$screen.html('<div class="lpr-preview__viewport" style="width:' + d.real + "px;height:" + d.h + "px;transform:scale(" + scale.toFixed(4) + ')">' + body + "</div>");
		$note.text(note);
	}

	function loadProducts() {
		if ((!showPreview && !placesShowcase) || !cfg.ajaxUrl) return;
		$.get(cfg.ajaxUrl, { action: "lpr_preview_products", nonce: cfg.nonce }).done((res) => {
			if (res && res.success && Array.isArray(res.data)) {
				products = res.data;
				render();
			}
		});
	}

	/* ---------- start ---------- */

	// Store-wide product picker: search as you type, keep the picked order.
	function setupProductPicker() {
		const $sel = $form.find("select.lpr-products-select");
		if (!$sel.length || !$.fn.select2) return;
		// Thumbnail + title, in the list and in the picked chips.
		const withImage = (item) => {
			if (!item.id) return item.text;
			const src = item.image || (item.element && item.element.getAttribute("data-image")) || "";
			const $row = $('<span class="lpr-pick"></span>');
			if (src) $row.append($('<img class="lpr-pick__img" alt="">').attr("src", src));
			$row.append($('<span class="lpr-pick__name"></span>').text(item.text));
			return $row;
		};
		$sel.select2({
			width: "100%",
			placeholder: $sel.data("placeholder"),
			minimumInputLength: 0,
			templateResult: withImage,
			templateSelection: withImage,
			ajax: {
				url: cfg.ajaxUrl,
				dataType: "json",
				delay: 250,
				data: (params) => ({ action: "lpr_search_products", nonce: cfg.nonce, q: params.term || "" }),
				processResults: (data) => data,
			},
		});
		// New picks go to the end of the list, so the saved order is the picking order.
		$sel.on("select2:select", function (e) {
			const $opt = $(this).find('option[value="' + e.params.data.id + '"]');
			if (e.params.data.image) $opt.attr("data-image", e.params.data.image);
			$opt.detach().appendTo(this);
			$(this).trigger("change");
		});
	}

	$(function () {
		setupMoreOptions();
		setupProductPicker();
		$form.find(".lpr-field-swatches").each(function () {
			syncSwatches($(this));
		});
		$form.find(".lpr-unsaved").remove();
		$form.find("p.submit").first().before($unsaved);
		applyRules();
		render();
		loadProducts();

		$form.on("input change", "input, select, textarea", () => {
			render();
			checkDirty();
		});

		// The heading editor shows text in the admin font, not TinyMCE's serif default.
		const EDITOR_CSS = 'body#tinymce,body#tinymce h1,body#tinymce h2,body#tinymce h3,body#tinymce h4,body#tinymce p{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif !important;color:#1d2327}body#tinymce{font-size:15px;line-height:1.5}body#tinymce h1,body#tinymce h2,body#tinymce h3{font-weight:600;line-height:1.3}';
		const editorFont = (ed) => {
			if (!ed || ed.id !== "default-heading-editor") return;
			const apply = () => {
				try {
					ed.dom.addStyle(EDITOR_CSS);
				} catch (e) {}
			};
			if (ed.initialized) apply();
			else ed.on("init", apply);
		};
		if (window.tinymce) {
			window.tinymce.on("AddEditor", (e) => editorFont(e.editor));
			editorFont(window.tinymce.get("default-heading-editor"));
		}

		// The heading editor starts after this script; hook it once it exists.
		let tries = 0;
		const hookEditor = setInterval(() => {
			const ed = window.tinymce && window.tinymce.get("default-heading-editor");
			if (ed || ++tries > 40) {
				clearInterval(hookEditor);
				if (ed) {
					ed.on("keyup change NodeChange", () => {
						render();
						checkDirty();
					});
				}
			}
		}, 250);

		// Color pickers and editors may tidy values on start: take the snapshot after that.
		setTimeout(() => {
			saved = snapshot();
		}, 1200);
	});
})(jQuery);
