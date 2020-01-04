<?php 
    $total_rc_products = count($selected_products_id);

    $modal_rows = 'wpr-modal-4';

    if($total_rc_products === 3) {
        $modal_rows = 'wpr-modal-3';
    }

    if($total_rc_products <= 2) {
        $modal_rows = 'wpr-modal-2';
    }

?>

<div class="wpr-modal <?php echo $modal_rows; ?>" style="display:none;" id="wpr-modal-<?php echo $product_id; ?>">
    <div class="wpr-modal-dialog wpr-modal-dialog-scrollable">
        <div class="wpr-modal-content">
            <div class="wpr-modal-head">

                <div class="wpr-message" role="alert">
                “<?php echo get_the_title($product_id); ?>” <?php _e('has been added to your cart.','woocommerce-product-recommend'); ?>
                <a href="<?php echo wc_get_cart_url(); ?>" class="button wc-forward"><?php _e('View cart', 'woocommerce-product-recommend');?></a> 
                </div>

                <?php 
                    $modal_heading = apply_filters('pgfy_modal_heading', sprintf(
                        __('<h2 class="modal-heading">You may purchase following product with "%s"</h2>','woocommerce-product-recommend'), 
                        get_the_title($product_id)
                    ));

                    echo $modal_heading;
                ?>
                
                <span aria-hidden="true" class="wpr-modal-close">×</span>
            </div>
            <div class="wpr-modal-body">
                <ul class="products recommended-product-list">

                <?php
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                        'post__in' => $selected_products_id,
                    );

                    $loop = new WP_Query( $args );
                        if ( $loop->have_posts() ): while ( $loop->have_posts() ) : $loop->the_post();
                    ?>

                    <li class="product single-wpr">
                        <a href="<?php the_permalink(); ?>"> 
                        <?php 
                            do_action( 'woocommerce_before_shop_loop_item_title' );
                            do_action( 'woocommerce_shop_loop_item_title' );     
                            do_action( 'woocommerce_after_shop_loop_item_title' );               
                        ?>
                        </a>
                        <?php echo do_shortcode('[add_to_cart 
                            id="'.get_the_ID().'" 
                            show_price = "FALSE"
                            style=""
                        ]'); ?>
                    </li>
                    
                    <?php endwhile;  wp_reset_postdata(); endif; ?>
                </ul>
            </div>

            <div class="wpr-modal-footer">
                <a href="#" class="wpr-button wpr-button-blue">Shop More</a>
                <a href="<?php echo wc_get_cart_url(); ?>" class="wpr-button wpr-button-green"><?php _e('View cart', 'woocommerce-product-recommend');?></a>
            </div>
        </div>
    </div>
</div>