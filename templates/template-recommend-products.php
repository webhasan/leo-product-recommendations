<?php 
    $nonce = $_GET['nonce'];
    $recommended_items_ids = $_GET['recommended_items'];

    if(!isset($nonce) || !wp_verify_nonce($nonce, 'pgfy-ajax-modal') || !isset($recommended_items_ids)) {
        wp_send_json_error(array('message' => 'Bad request'), 400 );
    }

   

    $recommenede_products = explode(',', $recommended_items_ids);

    // foreach( WC()->cart->get_cart() as $cart_item ){
    //     $products_ids_array[] = $cart_item['product_id'];
    // }
    
    // recommended prodcuts query
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        // 'post__not_in' => 
        'post__in' =>  $recommenede_products,
    );
    $loop = new WP_Query( $args );

    if ( $loop->have_posts() ): while ( $loop->have_posts() ) : $loop->the_post();
?>

<li class="product single-wpr">
    <?php do_action('wpr_before_recommended_prodcut', get_the_ID()); ?>

    <a href="<?php the_permalink(); ?>"> 
    <?php 
        do_action( 'woocommerce_before_shop_loop_item_title' );
        do_action( 'woocommerce_shop_loop_item_title' );     
        do_action( 'woocommerce_after_shop_loop_item_title' );               
    ?>
    </a>
    <?php  echo do_shortcode('[add_to_cart 
        id="'.get_the_ID().'" 
        show_price = "FALSE"
        style=""
    ]'); 
    ?>
    
    <?php do_action('wpr_after_recommended_prodcut', get_the_ID()); ?>
</li>
<?php  endwhile;  wp_reset_postdata(); endif;  
wp_die();
?>