<?php 
    // global
    // $selected_products_id
    // $modal_heading;

    // modal css class baseed on number of selected prodcuts
    $total_rc_products = count($selected_products_id);
    $modal_rows = 'wpr-modal-4';
    if($total_rc_products === 3) {
        $modal_rows = 'wpr-modal-3';
    }
    if($total_rc_products <= 2) {
        $modal_rows = 'wpr-modal-2';
    }

    // modal heading
    $common_heading = apply_filters(
        'wpr_common_heading',
        sprintf(
            __('<h2 class="modal-heading">You may purchase following product with "%s"</h2>'), 
            get_the_title($product_id)
        )
    );
    $modal_heading  = trim($modal_heading) ? sprintf('<h2>%s</h2>', $modal_heading) : $common_heading;


    // recommended prodcuts query
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post__in' => $selected_products_id,
    );
    $loop = new WP_Query( $args );
?>

<div class="wpr-modal <?php echo $modal_rows; ?>" style="display:none;" id="wpr-modal-<?php echo $product_id; ?>">
    <div class="wpr-modal-dialog wpr-modal-dialog-scrollable">
        <div class="wpr-modal-content">
            <div class="wpr-modal-head">
                <div class="wpr-message" role="alert">
                “<?php echo get_the_title($product_id); ?>” <?php _e('has been added to your cart.','woocommerce-product-recommend'); ?>
                <a href="<?php echo wc_get_cart_url(); ?>" class="button wc-forward"><?php _e('View cart', 'woocommerce-product-recommend');?></a> 
                </div>
                <?php echo $modal_heading; ?>
                <span aria-hidden="true" class="wpr-modal-close">×</span>
            </div>
            <div class="wpr-modal-body">
                <ul class="products recommended-product-list">
                    <?php
                        if ( $loop->have_posts() ): while ( $loop->have_posts() ) : $loop->the_post();
                            include($this->get_path('templates/template-single-product.php'));
                        endwhile;  wp_reset_postdata(); endif; 
                    ?>
                </ul>
            </div>

            <div class="wpr-modal-footer">
                <?php 
                    echo apply_filters('wpr_modal_footer_buttons', 
                        sprintf(
                            '<a href="#" class="wpr-button wpr-button-blue wpr-close-modal">%1$s</a>
                            <a href="%2$s" class="wpr-button wpr-button-green">%3$s</a>',

                            __('Shop More','woocommerce-product-recommend'),
                            wc_get_cart_url(),
                            __('View cart', 'woocommerce-product-recommend')
                        )
                    )
                ?>
            </div>
        </div>
    </div>
</div>