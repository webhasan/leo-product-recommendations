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