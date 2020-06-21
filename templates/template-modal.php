<?php
// global
// $recommendation_products_ids
// $modal_heading;

// modal css class baseed on number of selected prodcuts
$total_rc_products = count($recommendation_products_ids);

// modal heading
$fallback_heading = apply_filters(
    'wpr_common_heading',
    sprintf(
        /* translators: 1. singlular or plural of item, 2. title of product */
        __('<h2 class="modal-heading">You may purchase following %1$s with <strong>%2$s</strong> </h2>', 'woocommerce-product-recommendations'),
        _n('item', 'items', $total_rc_products, 'woocommerce-product-recommendations'),
        get_the_title($product_id)
    )
);

$html_permission = array(
    'span' => array('class'),
    'b'    => array(),
    'strong' => array(),
    'i'    => array(),
    'br'   => array(),
);

$modal_heading  = trim($modal_heading) ? sprintf('<h2 class="modal-heading">%s</h2>', wp_kses($modal_heading, $html_permission)) : $fallback_heading;
?>

<div class="wpr-modal woocommerce" style="display:none;" id="wpr-modal-<?php echo $product_id; ?>">
    <div class="wpr-modal-dialog wpr-modal-dialog-scrollable">

        <?php do_action('wpr_before_modal_content', $product_id, $recommendation_products_ids); ?>

        <div class="wpr-modal-content">
            <div class="wpr-modal-head">

                <?php do_action('wpr_start_modal_head', $product_id, $recommendation_products_ids); ?>

                <div class="wpr-message" role="alert">
                    <?php
                        echo apply_filters('wpr_checked_icon', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path d="M256,0C114.84,0,0,114.84,0,256S114.84,512,256,512,512,397.16,512,256,397.16,0,256,0ZM385.75,201.75,247.08,340.41a21.29,21.29,0,0,1-30.16,0l-69.33-69.33a21.33,21.33,0,0,1,30.16-30.16L232,295.17,355.59,171.59a21.33,21.33,0,0,1,30.16,30.16Z"/></g></g></svg>');
                    ?>
                    <span class="wpr-notification-text"><strong class="product-modal"><?php echo get_the_title($product_id); ?></strong> <?php _e('has been added to your cart.', 'woocommerce-product-recommend'); ?></span>
                    <a href="<?php echo wc_get_cart_url(); ?>" class="wpr-cart-button"><?php _e('View cart', 'woocommerce-product-recommend'); ?> &rarr; </a>
                </div>

                <?php echo $modal_heading; ?>

                <span aria-hidden="true" class="wpr-modal-close"></span>

                <?php do_action('wpr_end_modal_head', $product_id, $recommendation_products_ids); ?>
            </div>

            <div class="wpr-modal-body">
                <?php do_action('wpr_before_products_loop', $product_id, $recommendation_products_ids); ?>

                <ul class="products recommendation-products-wrapper recommendation-product-list" data-recommendation-ids="<?php echo implode(',', $recommendation_products_ids); ?>">
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

                <?php do_action('wpr_after_products_loop', $product_id, $recommendation_products_ids); ?>
            </div>
        </div>

        <?php do_action('wpr_after_modal_content', $product_id, $recommendation_products_ids); ?>

    </div>
</div>