<?php
/**
 * Feature request card, shown inside the Get Started tab.
 *
 * This prints inside the settings form, so it uses no <form> of its own and
 * its fields have no name attribute (they are never saved as settings).
 * The Send button posts them with AJAX (assets/js/feature-request.js).
 *
 * @since 3.7.0
 */

if (!defined('ABSPATH')) {
	exit;
}

?>
<div class="lpr-help__card lpr-fr" id="feature-request">
	<h3 class="lpr-fr__title"><?php esc_html_e('Request a feature', 'leo-product-recommendations'); ?></h3>
	<p class="lpr-fr__intro"><?php esc_html_e('Missing something? Tell us what would make the plugin more useful for your store.', 'leo-product-recommendations'); ?></p>

	<div class="lpr-fr__field">
		<label for="lpr-fr-idea"><?php esc_html_e('What feature would you like?', 'leo-product-recommendations'); ?> <span class="lpr-fr__req">*</span></label>
		<textarea id="lpr-fr-idea" rows="4" maxlength="2000" placeholder="<?php esc_attr_e('For example: show recommendations on the shop page under each product.', 'leo-product-recommendations'); ?>"></textarea>
	</div>

	<div class="lpr-fr__field">
		<label for="lpr-fr-why"><?php esc_html_e('What would it help you do?', 'leo-product-recommendations'); ?> <span class="lpr-fr__opt"><?php esc_html_e('Optional', 'leo-product-recommendations'); ?></span></label>
		<textarea id="lpr-fr-why" rows="2" maxlength="1000" placeholder="<?php esc_attr_e('For example: sell more accessories to people who browse but do not add to cart.', 'leo-product-recommendations'); ?>"></textarea>
	</div>

	<div class="lpr-fr__field">
		<label for="lpr-fr-email"><?php esc_html_e('Your email', 'leo-product-recommendations'); ?> <span class="lpr-fr__opt"><?php esc_html_e('Optional, only if you want a reply', 'leo-product-recommendations'); ?></span></label>
		<input type="email" id="lpr-fr-email" maxlength="100" autocomplete="email">
	</div>

	<p class="lpr-fr__actions">
		<button type="button" class="button button-primary" id="lpr-fr-send"><?php esc_html_e('Send request', 'leo-product-recommendations'); ?></button>
		<span class="lpr-fr__status" id="lpr-fr-status" role="status" aria-live="polite"></span>
	</p>
</div>
