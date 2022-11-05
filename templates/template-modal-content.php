<?php
/**
 * Recommendations Modal Template
 *
 * @since      1.0.0
 * @author     LeoCoder
 * @global     $template_data
 */
if (!defined('ABSPATH')) {
    exit;
}

extract($template_data);
// $product_id
// $recommended_products_id
// $modal_heading
// $show_close_icon
// $show_continue_shopping
// $show_go_check_out
// $layout_type
// $theme
// $query
// $feature_image
// $variable_add_to_cart
?>

<?php do_action('lpr_before_modal_content', $product_id, $recommended_products_id); ?>
<div class="lpr-modal-content">
    <div class="lpr-modal-head">

        <?php do_action('lpr_start_modal_head', $product_id, $recommended_products_id); ?>

        <div class="lpr-message" role="alert">
            <div class="message-text">
                <img class="lpr-added-product" src="<?php echo $feature_image; ?>" alt="">
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

                <?php if(apply_filters('lpr_go_checkout', true)): ?>
                    <a href="<?php echo wc_get_checkout_url(); ?>" class="lpr-button"><?php _e('Checkout', 'leo-product-recommendations'); ?></a>
                <?php endif; ?>

                <?php if(apply_filters('lpr_cart_count', true)): ?>
                    <a href="<?php echo wc_get_cart_url(); ?>" class="lpr-cart-count">
                        <span class="lpr-total-items"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                        <?php 
                            echo apply_filters('lpr_cart_icon','<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path d="M23.76 4.248c-0.096-0.096-0.24-0.24-0.504-0.24h-18.48l-0.48-2.4c-0.024-0.288-0.384-0.528-0.624-0.528h-2.952c-0.384 0-0.624 0.264-0.624 0.624s0.264 0.648 0.624 0.648h2.424l2.328 11.832c0.312 1.608 1.848 2.856 3.48 2.856h11.28c0.384 0 0.624-0.264 0.624-0.624s-0.264-0.624-0.624-0.624h-11.16c-0.696 0-1.344-0.312-1.704-0.816l14.064-1.92c0.264 0 0.528-0.24 0.528-0.528l1.968-7.824v-0.024c-0.024-0.048-0.024-0.288-0.168-0.432zM22.392 5.184l-1.608 6.696-14.064 1.824-1.704-8.52h17.376zM8.568 17.736c-1.464 0-2.592 1.128-2.592 2.592s1.128 2.592 2.592 2.592c1.464 0 2.592-1.128 2.592-2.592s-1.128-2.592-2.592-2.592zM9.888 20.328c0 0.696-0.624 1.32-1.32 1.32s-1.32-0.624-1.32-1.32 0.624-1.32 1.32-1.32 1.32 0.624 1.32 1.32zM18.36 17.736c-1.464 0-2.592 1.128-2.592 2.592s1.128 2.592 2.592 2.592c1.464 0 2.592-1.128 2.592-2.592s-1.128-2.592-2.592-2.592zM19.704 20.328c0 0.696-0.624 1.32-1.32 1.32s-1.344-0.6-1.344-1.32 0.624-1.32 1.32-1.32 1.344 0.624 1.344 1.32z"></path></svg>');
                        ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>


        </div>

        <?php echo $modal_heading; ?>

        <?php if(apply_filters('lpr_show_close_icon', true)): ?>
            <span aria-hidden="true" class="lpr-modal-close"></span>
        <?php endif; ?>

        <?php do_action('lpr_end_modal_head', $product_id, $recommended_products_id); ?>
    </div>

    <div class="lpr-modal-body">
        <?php do_action('lpr_before_products_loop', $product_id, $recommended_products_id); ?>

        <ul class="products recommended-products-wrapper recommended-products-list">
            <?php 
            if ($query->have_posts()): while ($query->have_posts()): $query->the_post();
                include $this->get_templates_path('templates/template-recommendations-products.php');
            endwhile; wp_reset_postdata(); endif;
            ?>
        </ul>

        <?php do_action('lpr_after_products_loop', $product_id, $recommended_products_id); ?>
    </div>
    <div class="lpr-purchase-notification"></div>
</div>
<?php do_action('lpr_after_modal_content', $product_id, $recommended_products_id); ?>