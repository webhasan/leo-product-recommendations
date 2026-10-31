<?php
/**
 * Feature Request tab: the request form, then links to the docs and support.
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included from a class method, so these variables are local to that method.

$lpr_docs_url    = 'https://leocoder.com/docs/woocommerce-product-recommendations/add-recommended-products/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=docs&utm_content=tutorials';
$lpr_support_url = 'https://wordpress.org/support/plugin/leo-product-recommendations/';
?>

<div class="tutorials lpr-help lpr-fr-page">
    <?php
    // Feature request form (from the free plugin, 3.7.0+).
    if (class_exists('\\LoeCoder\\Plugin\\ProductRecommendations\\Feature_Request')) {
        include \LoeCoder\Plugin\ProductRecommendations\Feature_Request::template();
    }
    ?>

    <p class="lpr-fr-help">
        <span><?php esc_html_e('Need help with the plugin?', 'leo-product-recommendations'); ?></span>
        <a href="<?php echo esc_url($lpr_docs_url); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-book" aria-hidden="true"></span><?php esc_html_e('Read the documentation', 'leo-product-recommendations'); ?></a>
        <a href="<?php echo esc_url($lpr_support_url); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-sos" aria-hidden="true"></span><?php esc_html_e('Ask for help', 'leo-product-recommendations'); ?></a>
    </p>
</div>
