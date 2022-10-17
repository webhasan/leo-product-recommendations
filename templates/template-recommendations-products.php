<?php 
/**
 * Single Product Template
 *
 * @since      1.0.0
 * @author     LeoCoder
 * @global $theme
 */

 global $product;

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
    <div class="rey-productInner">
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
        
        //fixing not showing product image
        $no_thumbnail_themes = apply_filters('lpr_fix_thumb', ['DavinciWoo','Rey']);
        if(in_array($theme, $no_thumbnail_themes)) {
            echo woocommerce_get_product_thumbnail();
        }

        do_action('woocommerce_before_shop_loop_item_title');
        do_action('woocommerce_shop_loop_item_title');

        //fixing for not showing title 
        $no_product_title = apply_filters('lpr_title_price', ['Rey']);
        if(in_array($theme, $no_product_title)) {
            woocommerce_template_loop_product_title();
        }
        do_action('woocommerce_after_shop_loop_item_title');

        ?>
    </a>

    <?php 
        //fixing for not showing price 
        $no_product_price = apply_filters('lpr_fix_price', ['Sneaker','Rey','Safira','Plantmore']);
        if(in_array($theme, $no_product_price)) {
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
    </div>
<?php echo $ending_tag; ?>