<?php 
/**
 * Single Product Template
 *
 * @since      1.0.0
 * @author     LeoCoder
 * @global $theme
 */

if (!defined('ABSPATH')) {
    exit;
}

$tag_classes = 'product single-lpr ';

if($theme === 'Electro') {
    $tag_classes .= 'single-product';
}

if($theme === 'Electro') {
    $tag_classes .= 'single-product';
}

if($theme === 'OceanWP') {
    $tag_classes .= 'product-inner';
}

$beginning_tag = sprintf('<li class="%s">', $tag_classes);
$ending_tag   = '</li>';
?>

<?php echo $beginning_tag; ?>
    <?php do_action('lpr_before_recommended_product', get_the_ID()); ?>

    <?php if($theme === 'OceanWP') { // ocean wp theme archive product
         wc_get_template( 'owp-archive-product.php' );
    } ?>

    <a href="<?php the_permalink(); ?>">
        <?php
        if($theme === 'Flatsome') { //Flatsome product thumbnail
            do_action( 'flatsome_woocommerce_shop_loop_images' );
        }

        if($theme === 'Medibazar') { //Medibazar product thumbnail
            medibazar_shop_thumbnail();
        }
        
        //fixed not showing image issue
        $no_thumbnail_themes = apply_filters('lpr_show_image', ['DavinciWoo']);
        if(in_array($theme, $no_thumbnail_themes)) {
            echo woocommerce_get_product_thumbnail();
        }

        do_action('woocommerce_before_shop_loop_item_title');
        do_action('woocommerce_shop_loop_item_title');
        do_action('woocommerce_after_shop_loop_item_title');
        ?>
    </a>

    <?php 
    // Fixing for Sneaker & Safira theme
        if($theme === 'Sneaker' || $theme === 'Safira' || $theme === 'Plantmore') {
            woocommerce_template_single_price();
        }
    ?>

    <?php 
    if($variable_add_to_cart) {
        woocommerce_template_single_add_to_cart();
    }else {
        woocommerce_template_loop_add_to_cart();
    }
    do_action('lpr_after_recommended_product', get_the_ID()); ?>
<?php echo $ending_tag; ?>

