<?php
/**
 * Recommendations Modal Template
 *
 * @since      1.0.0
 * @author     LeoCoder
 */
if (!defined('ABSPATH')) {
    exit;
}
extract($data);
?>

<div class="lpr-modal woocommerce" style="display:none;" id="lpr-modal-<?php echo $product_id; ?>">
    <div class="lpr-modal-dialog lpr-modal-dialog-scrollable">

        <?php do_action('lpr_before_modal_content', $product_id, $recommended_products_id); ?>

        <div class="lpr-modal-content">
            <div class="lpr-modal-head">

                <?php do_action('lpr_start_modal_head', $product_id, $recommended_products_id); ?>

                <div class="lpr-message" role="alert">
                    <div class="message-text">
                        <?php
                            echo apply_filters('lpr_checked_icon', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path d="M256,0C114.84,0,0,114.84,0,256S114.84,512,256,512,512,397.16,512,256,397.16,0,256,0ZM385.75,201.75,247.08,340.41a21.29,21.29,0,0,1-30.16,0l-69.33-69.33a21.33,21.33,0,0,1,30.16-30.16L232,295.17,355.59,171.59a21.33,21.33,0,0,1,30.16,30.16Z"/></g></g></svg>');
                        ?>
                        <span class="lpr-notification-text"><strong class="product-modal"><?php echo get_the_title($product_id); ?></strong> <?php _e('has been added to your cart.', 'leo-product-recommendations'); ?></span>
                        <a href="<?php echo wc_get_cart_url(); ?>" class="lpr-cart-button"><?php _e('View cart', 'leo-product-recommendations'); ?> &rarr; </a>
                    </div>

                    <?php if(apply_filters('lpr_show_buttons', true)): ?>
                    <div class="right-buttons">
                        <?php if(apply_filters('lpr_show_continue_shopping', true)): ?>
                        <a href="#" class="lpr-close-modal lpr-button"><?php _e('Continue Shopping', 'leo-product-recommendations'); ?></a>
                        <?php endif; ?>

                        <?php if(apply_filters('wpr_go_checkout', true)): ?>
                        <a href="<?php echo wc_get_checkout_url(); ?>" class="lpr-button"><?php _e('Checkout', 'leo-product-recommendations'); ?></a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php echo $modal_heading; ?>

                <?php if(apply_filters('lpr_show_close_icon', false)): ?>
                    <span aria-hidden="true" class="lpr-modal-close"></span>
                <?php endif; ?>

                <?php do_action('lpr_end_modal_head', $product_id, $recommended_products_id); ?>
            </div>

            <div class="lpr-modal-body">
                <?php do_action('lpr_before_products_loop', $product_id, $recommended_products_id); ?>

                <ul class="products recommended-products-wrapper recommended-products-list" data-recommendation-ids="<?php echo implode(',', $recommended_products_id); ?>">
                    <!-- prodcut will fill by ajax -->
                </ul>

                <div class="loading-products">
                    <div class="single-loading-product">
                        <div class="loading-thumb"></div>
                        <div class="loading-title"></div><br>
                        <div class="loading-price"></div><br>
                        <div class="loading-button"></div>
                    </div>

                    <div class="single-loading-product">
                        <div class="loading-thumb"></div>
                        <div class="loading-title"></div><br>
                        <div class="loading-price"></div><br>
                        <div class="loading-button"></div>
                    </div>

                    <div class="single-loading-product">
                        <div class="loading-thumb"></div>
                        <div class="loading-title"></div><br>
                        <div class="loading-price"></div><br>
                        <div class="loading-button"></div>
                    </div>

                    <div class="single-loading-product">
                        <div class="loading-thumb"></div>
                        <div class="loading-title"></div><br>
                        <div class="loading-price"></div><br>
                        <div class="loading-button"></div>
                    </div>
                </div>

                <?php do_action('lpr_end_modal_head', $product_id, $recommended_products_id); ?>
            </div>
            <div class="lpr-purchase-notification"></div>
        </div>
        <?php do_action('lpr_after_modal_content', $product_id, $recommended_products_id); ?>
    </div>
</div>