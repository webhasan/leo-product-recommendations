<?php
// global
// $recommendation_products_ids
// $modal_heading;

// modal css class baseed on number of selected prodcuts
$total_rc_products = count($recommendation_products_ids);
$modal_rows = 'wpr-modal-4';

if ($total_rc_products <= 3) {
    $modal_rows = 'wpr-modal-3';
}

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

<div class="wpr-modal woocommerce <?php echo $modal_rows; ?>" style="display:none;" id="wpr-modal-<?php echo $product_id; ?>">
    <div class="wpr-modal-dialog wpr-modal-dialog-scrollable">

        <?php do_action('wpr_before_modal_content', $product_id, $recommendation_products_ids); ?>

        <div class="wpr-modal-content">
            <div class="wpr-modal-head">

                <?php do_action('wpr_start_modal_head', $product_id, $recommendation_products_ids); ?>

                <div class="wpr-message" role="alert">
                    <?php
                        echo apply_filters('wpr_checked_icon', '<svg height="512pt" viewBox="0 0 512 512" width="512pt" xmlns="http://www.w3.org/2000/svg"><path d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm129.75 201.75-138.667969 138.664062c-4.160156 4.160157-9.621093 6.253907-15.082031 6.253907s-10.921875-2.09375-15.082031-6.253907l-69.332031-69.332031c-8.34375-8.339843-8.34375-21.824219 0-30.164062 8.339843-8.34375 21.820312-8.34375 30.164062 0l54.25 54.25 123.585938-123.582031c8.339843-8.34375 21.820312-8.34375 30.164062 0 8.339844 8.339843 8.339844 21.820312 0 30.164062zm0 0"/></svg>');
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