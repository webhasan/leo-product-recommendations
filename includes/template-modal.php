<div class="wpr-modal" style="display:none;" id="wpr-modal-<?php echo $product_id; ?>">
    <div class="wpr-modal-dialog wpr-modal-dialog-scrollable">
        <div class="wpr-modal-content">
            <div class="wpr-modal-head">

                <div class="woocommerce-message" role="alert">
                    <a href="<?php echo wc_get_cart_url(); ?>" class="button wc-forward"><?php _e('View cart', 'woocommerce-product-recommend');?></a> 
                    “<?php echo get_the_title($product_id); ?>” <?php _e('has been added to your cart.','woocommerce-product-recommend'); ?>	
                </div>

                <?php 
                    $modal_heading = apply_filters('pgfy_modal_heading', sprintf(
                        __('<h2>You may purchase following product with "%s"</h2>','woocommerce-product-recommend'), 
                        get_the_title($product_id)
                    ));

                    echo $modal_heading;
                ?>
                
                <span aria-hidden="true" class="wpr-modal-close">×</span>
            </div>
            <div class="wpr-modal-body">
                <div class="recommended-product-list">
                <?php
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'post__in' => $selectedPostsId,
                );
                    $loop = new WP_Query( $args );
                        if ( $loop->have_posts() ): while ( $loop->have_posts() ) : $loop->the_post();
                    ?>

                    <div class="single-wpr">
                        <a href="<?php the_permalink(); ?>" style="display: block;">
                            <?php the_post_thumbnail(); ?>
                            <?php the_title(); ?>
                        </a>

                        <?php 
                            echo apply_filters( 'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
                            sprintf( '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
                                esc_url( $product->add_to_cart_url() ),
                                esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
                                esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
                                isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
                                esc_html( $product->add_to_cart_text() )
                            ),
                            $product, $args );
                        ?>
                    </div>
                    
                    <?php endwhile; endif; wp_reset_postdata();?>
                </div>
            </div>

            <div class="wpr-modal-footer">
                <a href="#" class="wpr-button wpr-button-blue">Shop More</a>
                <a href="<?php echo wc_get_cart_url(); ?>" class="wpr-button wpr-button-green"><?php _e('View cart', 'woocommerce-product-recommend');?></a>
            </div>
        </div>
    </div>
</div>