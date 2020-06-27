<li class="product single-wpr">
    <?php do_action('wpr_before_recommended_prodcut', get_the_ID()); ?>

    <a href="<?php the_permalink(); ?>">
        <?php
        do_action('woocommerce_before_shop_loop_item_title');
        do_action('woocommerce_shop_loop_item_title');
        do_action('woocommerce_after_shop_loop_item_title');
        ?>
    </a>

    <?php echo do_shortcode('[add_to_cart 
            id="' . get_the_ID() . '" 
            show_price = "FALSE"
            style=""
        ]');
    ?>

    <?php do_action('wpr_after_recommended_prodcut', get_the_ID()); ?>
</li>

