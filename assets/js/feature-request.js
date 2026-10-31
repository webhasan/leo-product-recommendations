/* LPR Settings > Feature Request: send the form with AJAX. */
(function ($) {
	"use strict";

	const cfg = window.lpr_feature_request || {};
	const { __ } = (window.wp && window.wp.i18n) || { __: (s) => s };

	$(function () {
		const $wrap = $(".lpr-fr");
		if (!$wrap.length) {
			return;
		}

		const $idea = $("#lpr-fr-idea");
		const $why = $("#lpr-fr-why");
		const $email = $("#lpr-fr-email");
		const $send = $("#lpr-fr-send");
		const $status = $("#lpr-fr-status");

		const show = (msg, type) => {
			$status.text(msg).removeClass("is-error is-success").addClass(type ? "is-" + type : "");
		};

		// These fields sit inside the settings form: Enter must not save the settings.
		$email.on("keydown", (e) => {
			if (e.key === "Enter") {
				e.preventDefault();
				$send.trigger("click");
			}
		});

		$send.on("click", () => {
			const idea = $.trim($idea.val());
			if (!idea) {
				show(__("Please describe the feature you would like.", "leo-product-recommendations"), "error");
				$idea.trigger("focus");
				return;
			}

			$send.prop("disabled", true);
			show(__("Sending...", "leo-product-recommendations"));

			$.post(cfg.ajax_url, {
				action: cfg.action,
				security: cfg.nonce,
				idea: idea,
				why: $.trim($why.val()),
				email: $.trim($email.val()),
			})
				.done((res) => {
					const msg = res && res.data && res.data.message;
					if (res && res.success) {
						show(msg || __("Thanks! Your request was sent.", "leo-product-recommendations"), "success");
						$idea.val("");
						$why.val("");
					} else {
						show(msg || __("Sorry, it could not be sent right now.", "leo-product-recommendations"), "error");
					}
				})
				.fail(() => {
					show(__("Sorry, it could not be sent right now. Please try again in a few minutes.", "leo-product-recommendations"), "error");
				})
				.always(() => {
					$send.prop("disabled", false);
				});
		});
	});
})(jQuery);
