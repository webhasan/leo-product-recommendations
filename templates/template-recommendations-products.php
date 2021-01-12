<?php 
/**
 * Single Product Template
 *
 * @since      1.0.0
 * @author     LeoCoder
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<li class="product single-lpr">
    <?php do_action('lpr_before_recommended_prodcut', get_the_ID()); ?>
    
    <a href="<?php the_permalink(); ?>">
        <?php
        if($theme === 'Flatsome') { // Flatsome product thumbnail
            do_action( 'flatsome_woocommerce_shop_loop_images' );
        }
        do_action('woocommerce_before_shop_loop_item_title');
        do_action('woocommerce_shop_loop_item_title');
        do_action('woocommerce_after_shop_loop_item_title');
        ?>
    </a>

    <?php 
    if($variable_add_to_cart) {
        woocommerce_template_single_add_to_cart();
    }else {
        woocommerce_template_loop_add_to_cart();
    }
    do_action('lpr_after_recommended_prodcut', get_the_ID()); ?>
</li>

