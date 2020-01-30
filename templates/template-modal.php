<?php 
    // global
    // $recommended_products_ids
    // $modal_heading;

    // modal css class baseed on number of selected prodcuts
    $total_rc_products = count($recommended_products_ids);
    $modal_rows = 'wpr-modal-4';

    if($total_rc_products <= 3) {
        $modal_rows = 'wpr-modal-3';
    }

    // modal heading
    $common_heading = apply_filters(
        'wpr_common_heading',
        sprintf(
            __('<h2 class="modal-heading">You may purchase following %1$s with <strong>%2$s</strong> </h2>','woocommerce-product-recommend'), 
            _n( 'item', 'items', $total_rc_products, 'woocommerce-product-recommend' ),
            get_the_title($product_id)
        )
    );
    $modal_heading  = trim($modal_heading) ? sprintf('<h2 class="modal-heading">%s</h2>', $modal_heading) : $common_heading;


    // recommended prodcuts query
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post__in' => $recommended_products_ids,
    );
    $loop = new WP_Query( $args );
?>

<div class="wpr-modal <?php echo $modal_rows; ?>" style="display:none;" id="wpr-modal-<?php echo $product_id; ?>">
    <div class="wpr-modal-dialog wpr-modal-dialog-scrollable">

        <?php do_action('wpr_before_modal_content', $product_id, $recommended_products_ids); ?>

        <div class="wpr-modal-content">
            <div class="wpr-modal-head">

                <?php do_action('wpr_start_modal_head', $product_id, $recommended_products_ids); ?>

                <div class="wpr-message" role="alert">
                    <?php 
                    echo apply_filters('wpr_checked_icon', '<svg xmlns="http://www.w3.org/2000/svg" height="512px" viewBox="0 0 512 512" width="512px" class=""><g><path d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0" fill="#2196f3" data-original="#2196F3" class="" style="fill:#46C28E" data-old_color="#2196f3"/><path d="m385.75 201.75-138.667969 138.664062c-4.160156 4.160157-9.621093 6.253907-15.082031 6.253907s-10.921875-2.09375-15.082031-6.253907l-69.332031-69.332031c-8.34375-8.339843-8.34375-21.824219 0-30.164062 8.339843-8.34375 21.820312-8.34375 30.164062 0l54.25 54.25 123.585938-123.582031c8.339843-8.34375 21.820312-8.34375 30.164062 0 8.339844 8.339843 8.339844 21.820312 0 30.164062zm0 0" fill="#fafafa" data-original="#FAFAFA" class="active-path" style="fill:#FFFFFF" data-old_color="#fafafa"/></g> </svg><strong class="product-modal">');
                    ?>
                    <?php echo get_the_title($product_id); ?><?php _e(' </strong> has been added to your cart.','woocommerce-product-recommend'); ?>
                    <a href="<?php echo wc_get_cart_url(); ?>" class="wpr-cart-button"><?php _e('View cart', 'woocommerce-product-recommend');?> &rarr; </a> 
                </div>

                <?php echo $modal_heading; ?>

                <span aria-hidden="true" class="wpr-modal-close"></span>

                <?php do_action('wpr_end_modal_head', $product_id, $recommended_products_ids); ?>
            </div>

            <div class="wpr-modal-body">
                <?php do_action('wpr_before_products_loop', $product_id, $recommended_products_ids); ?>

                <ul class="products recommended-product-list">
                    <?php
                        if ( $loop->have_posts() ): while ( $loop->have_posts() ) : $loop->the_post();
                            include($this->get_path('templates/template-single-product.php'));
                        endwhile;  wp_reset_postdata(); endif; 
                    ?>
                </ul>

                <?php do_action('wpr_after_products_loop', $product_id, $recommended_products_ids); ?>
            </div>
        </div>

        <?php do_action('wpr_after_modal_content', $product_id, $recommended_products_ids); ?>

    </div>
</div>