<?php
/**
 * Modern popup (3.6.0). Copy to yourtheme/lpr/templates/modern-popup.php to change it.
 *
 * Available: $this (the plugin), $product_id (product just added), $recommended_products_id,
 * $template_data (heading, button settings), $added (its cart line or null).
 *
 * @package LoeCoder\Plugin\ProductRecommendations
 */

namespace LoeCoder\Plugin\ProductRecommendations;

if (!defined('ABSPATH')) {
	exit;
}

$added_product = $added && !empty($added['data']) ? $added['data'] : wc_get_product($product_id);
$added_name    = $added_product ? $added_product->get_name() : get_the_title($product_id);
$added_meta    = array();
if ($added && !empty($added['variation_id']) && $added_product) {
	$added_meta[] = wc_get_formatted_variation($added_product, true, false, false);
}
if ($added) {
	/* translators: %d: quantity */
	$added_meta[] = sprintf(__('Qty %d', 'leo-product-recommendations'), (int) $added['quantity']);
}
$added_price = $added && $added_product ? WC()->cart->get_product_subtotal($added_product, $added['quantity']) : ($added_product ? $added_product->get_price_html() : '');
$heading     = Popup_Modern::heading($this, $product_id, $template_data);
$show_cont   = !empty($template_data['show_continue_shopping']) && apply_filters('lpr_show_continue_shopping', true);
$show_check  = !empty($template_data['show_go_check_out']) && apply_filters('lpr_go_checkout', true);
$show_close  = !empty($template_data['show_close_icon']) && apply_filters('lpr_show_close_icon', true);
?>
<div class="lpr-pm" style="<?php echo esc_attr(Popup_Modern::accent_style($this)); ?>">
	<?php do_action('lpr_before_modal_content', $product_id, $recommended_products_id); ?>

	<div class="lpr-pm__head">
		<span class="lpr-pm__check" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg></span>
		<h2 class="lpr-pm__title" id="lpr-pm-title"><?php esc_html_e('Added to your cart', 'leo-product-recommendations'); ?></h2>
		<?php if ($show_close) : ?>
			<button type="button" class="lpr-pm__close lpr-modal-close" aria-label="<?php esc_attr_e('Close', 'leo-product-recommendations'); ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
		<?php endif; ?>
	</div>

	<div class="lpr-pm__scroll">
		<div class="lpr-pm__added">
			<span class="lpr-pm__added-img"><?php echo $added_product ? $added_product->get_image(array(64, 64)) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce image HTML. ?></span>
			<span class="lpr-pm__added-text">
				<span class="lpr-pm__added-name"><?php echo esc_html($added_name); ?></span>
				<?php if ($added_meta) : ?><span class="lpr-pm__added-meta"><?php echo esc_html(implode(' · ', array_filter($added_meta))); ?></span><?php endif; ?>
			</span>
			<span class="lpr-pm__added-price"><?php echo wp_kses_post($added_price); ?></span>
		</div>

		<div class="lpr-pm__summary" data-lpr-pm-summary><?php echo Popup_Modern::summary_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in summary_html(). ?></div>

		<section class="lpr-pm__recs" aria-labelledby="lpr-pm-recs-title">
			<div class="lpr-pm__recs-head">
				<div class="lpr-pm__recs-text" id="lpr-pm-recs-title">
					<?php echo wp_kses_post($heading); ?>
					<?php do_action('lpr_before_products_loop', $product_id, $recommended_products_id); ?>
				</div>
				<div class="lpr-pm__nav" hidden>
					<button type="button" class="lpr-pm__prev" aria-label="<?php esc_attr_e('Previous products', 'leo-product-recommendations'); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
					<button type="button" class="lpr-pm__next" aria-label="<?php esc_attr_e('Next products', 'leo-product-recommendations'); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
				</div>
			</div>
			<ul class="lpr-pm__list recommended-products-wrapper">
				<?php
				foreach ($recommended_products_id as $id) {
					echo Popup_Modern::card($this, wc_get_product($id), !empty($template_data['variable_add_to_cart'])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in card().
				}
				?>
			</ul>
			<?php do_action('lpr_after_products_loop', $product_id, $recommended_products_id); ?>
		</section>
	</div>

	<div class="lpr-pm__foot">
		<?php if ($show_cont) : ?>
			<button type="button" class="lpr-pm__continue lpr-close-modal"><?php esc_html_e('Continue shopping', 'leo-product-recommendations'); ?></button>
		<?php endif; ?>
		<div class="lpr-pm__actions">
			<a class="lpr-pm__btn lpr-pm__btn--cart" href="<?php echo esc_url(wc_get_cart_url()); ?>"><?php esc_html_e('View cart', 'leo-product-recommendations'); ?></a>
			<?php if ($show_check) : ?>
				<a class="lpr-pm__btn lpr-pm__btn--checkout" href="<?php echo esc_url(wc_get_checkout_url()); ?>"><?php esc_html_e('Checkout', 'leo-product-recommendations'); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="lpr-pm__toast" role="status" aria-live="polite"></div>
	<?php do_action('lpr_after_modal_content', $product_id, $recommended_products_id); ?>
</div>
