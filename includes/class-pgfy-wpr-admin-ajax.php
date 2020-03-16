<?php 
/**
 * Class for handeling all admin ajax related task
 */

class Pgfy_Wpr_Admin_Ajax {

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_wpr_initial_data', array($this, 'initial_data'));
        add_action( 'wp_ajax_nopriv_wpr_initial_data', array($this, 'initial_data'));

        add_action( 'wp_ajax_wpr_fetch_categores', array($this, 'fetch_categoreis'));
        add_action( 'wp_ajax_nopriv_wpr_fetch_categores', array($this, 'fetch_categoreis'));
        
        add_action( 'wp_ajax_wpr_fetch_products', array($this, 'fetch_prodcuts'));
        add_action( 'wp_ajax_nopriv_wpr_fetch_products', array($this, 'fetch_prodcuts'));
        
    }

    /**
	 * Fetch Product Meta and Recommend Products, etc 
	 * 
	 * @since      1.0.0
	 */
	public function initial_data() {

        // post ID 
        $post_id = $_GET['post_id'];

        if(!metadata_exists( 'post', $post_id, '_pgfy_pr_data' )) {
            wp_send_json(null);
        }

        $data = get_post_meta($post_id, '_pgfy_pr_data', true);
        
        if(!empty($data['products'])) {
            $products = $data['products'];
            $products = array_map(function($prodcut) {
                $id = (int) $prodcut;
                $title = get_the_title($id);
                $feature_image = get_the_post_thumbnail_url($id, array('100','100'));

                return compact('id', 'title', 'feature_image');
            }, $products);
        }

        $data['products'] = $products;

        wp_send_json($data);
    }


    /**
     * Fetch posts
     *
     * @return void
     */
    public function fetch_categoreis() {

        $product_categoreis = get_terms(array(
            'taxonomy'   => "product_cat",
            'orderby'    => 'name',
            'hide_empty' => false
        ));

        $product_categoreis = array_map(function($category){
            $id = $category->term_id;
            $name = $category->name;
            $parent = $category->parent;

            return compact('id','name','parent');
        }, $product_categoreis);

        wp_send_json($product_categoreis);
    }
    
    /**
     * Fetch posts
     *
     * @return void
     */
    public function fetch_prodcuts() {
        // post ID 
        $post_id = (int) $_GET['post_id'];

        // page
        $paged = !empty($_GET['page']) ? (int) $_GET['page'] : 1;

        $args = array(
            'post_type' 	=> 'product',
            'numberposts' 	=> 10,
            'exclude'     	=> array($post_id),
            'paged'         => $paged        
        );

        if(!empty($_GET['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => (int) $_GET['category']
                )
            );
        }

        if(!empty($_GET['query'])) {
            $args['s'] = $_GET['query'];
        }

        $products = get_posts( $args );
        
        // map prodcut id , title, thumbnails and categoris
		$products = array_map(function($item) {
			$id = $item->ID;
			$title = $item->post_title;
            $feature_image = get_the_post_thumbnail_url($id, array('100','100'));
            
			return compact('id','title','feature_image');
        }, $products);
        
        wp_send_json($products);
    }
}