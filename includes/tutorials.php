<?php
/**
 * Tutorials tab.
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

if (!defined('ABSPATH')) {
    exit;
}

$lpr_docs_url    = 'https://leocoder.com/docs/woocommerce-product-recommendations/add-recommended-products/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=docs&utm_content=tutorials';
$lpr_support_url = 'https://wordpress.org/support/plugin/leo-product-recommendations/';
?>
<div class="tutorials lpr-help">
    <h2><?php esc_html_e('Get Started', 'leo-product-recommendations'); ?></h2>
    <p class="lpr-section-intro"><?php esc_html_e('Three steps to your first recommendations.', 'leo-product-recommendations'); ?></p>

    <div class="lpr-help__grid">
        <div class="lpr-help__card">
            <ol class="lpr-help__steps">
                <li>
                    <strong><?php esc_html_e('Choose what to recommend', 'leo-product-recommendations'); ?></strong>
                    <span><?php esc_html_e('Turn on Upsells and Cross-sells or set a store-wide rule in the What to Recommend tab.', 'leo-product-recommendations'); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e('Pick products for key items', 'leo-product-recommendations'); ?></strong>
                    <span><?php esc_html_e('Edit a product, open its Recommendations tab and pick what goes with it.', 'leo-product-recommendations'); ?></span>
                </li>
                <li>
                    <strong><?php esc_html_e('Make it look right', 'leo-product-recommendations'); ?></strong>
                    <span><?php esc_html_e('Choose the popup design and heading in the Popup tab. The preview shows every change.', 'leo-product-recommendations'); ?></span>
                </li>
            </ol>
            <p class="lpr-help__links">
                <a class="button button-primary" href="<?php echo esc_url($lpr_docs_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Read the documentation', 'leo-product-recommendations'); ?></a>
                <a class="button" href="<?php echo esc_url($lpr_support_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Ask for help', 'leo-product-recommendations'); ?></a>
            </p>
        </div>

        <div class="lpr-help__card lpr-help__video">
            <iframe src="https://www.youtube-nocookie.com/embed/M5i8FDHge2s" title="<?php esc_attr_e('Setup video', 'leo-product-recommendations'); ?>" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</div>
