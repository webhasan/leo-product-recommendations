<?php
/**
 * Class to handle ajax request 
 * send from admin side
 * 
 * @since      1.0.0
 * @author     LeoCoder
 */

 if (!defined('ABSPATH')) {
    exit;
}

class LC_Wpr_Admin_Ajax {
    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct() {
        add_action('wp_ajax_wpr_initial_data', array($this, 'initial_data'));
        add_action('wp_ajax_nopriv_wpr_initial_data', array($this, 'initial_data'));

        add_action('wp_ajax_wpr_fetch_categories', array($this, 'fetch_categories'));
        add_action('wp_ajax_nopriv_wpr_fetch_categories', array($this, 'fetch_categories'));

        add_action('wp_ajax_wpr_fetch_tags', array($this, 'fetch_tags'));
        add_action('wp_ajax_nopriv_wpr_fetch_tags', array($this, 'fetch_tags'));

        add_action('wp_ajax_wpr_fetch_products', array($this, 'fetch_prodcuts'));
        add_action('wp_ajax_nopriv_wpr_fetch_products', array($this, 'fetch_prodcuts'));
    }

    /**
     * Fetch Product meta, recommendation products, etc
     *
     * @since      1.0.0
     */
    public function initial_data() {

        // post ID
        $post_id = $_GET['post_id'];

        if (!metadata_exists('post', $post_id, '_lc_wpr_data')) {
            wp_send_json(null);
        }

        $data = get_post_meta($post_id, '_lc_wpr_data', true);

        if (!empty($data['heading'])) {

            $html_permission = array(
                'span'   => array('class'),
                'b'      => array(),
                'strong' => array(),
                'i'      => array(),
                'br'     => array(),
            );

            $data['heading'] = wp_kses($data['heading'], $html_permission);
        }

        if (!empty($data['products'])) {
            $products = $data['products'];
            $products = array_map(function ($prodcut) {
                $id    = (int) $prodcut;
                $title = get_the_title($id);

                $feature_image = get_the_post_thumbnail_url($id, array('100', '100'));

                return compact('id', 'title', 'feature_image');
            }, $products);

            $data['products'] = $products;
        }

        if (!empty($data['categories'])) {
            $categories = $data['categories'];
            $categories = array_map(function ($category) {
                return (int) $category;
            }, $categories);

            $data['categories'] = $categories;
        }

        if (!empty($data['tags'])) {
            $tags = $data['tags'];
            $tags = array_map(function ($tag) {
                return (int) $tag;
            }, $tags);

            $data['tags'] = $tags;
        }

        if (!empty($data['number'])) {
            $data['number'] = (int) $data['number'];
        }

        if (!empty($data['sale'])) {
            $data['sale'] = (int) $data['sale'];
        }

        wp_send_json($data);
    }

    /**
     * Fetch categories
     *
     * @return void
     */
    public function fetch_categories() {

        $product_categoreis = get_terms(array(
            'taxonomy'   => "product_cat",
            'orderby'    => 'name',
            'hide_empty' => false,
        ));

        $product_categoreis = array_map(function ($category) {
            $id     = $category->term_id;
            $name   = $category->name;
            $parent = $category->parent;

            return compact('id', 'name', 'parent');
        }, $product_categoreis);

        wp_send_json($product_categoreis);
    }

    /**
     * Fetch tags
     *
     * @return void
     */
    public function fetch_tags() {

        $product_tags = get_terms(array(
            'taxonomy'   => "product_tag",
            'orderby'    => 'name',
            'hide_empty' => false,
        ));

        $product_tags = array_map(function ($category) {
            $value = (int) $category->term_id;
            $label = $category->name;

            return compact('value', 'label');
        }, $product_tags);

        wp_send_json($product_tags);
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
            'post_type'   => 'product',
            'numberposts' => 20,
            'exclude'     => array($post_id),
            'paged'       => $paged,
        );

        if (!empty($_GET['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => (int) $_GET['category'],
                ),
            );
        }

        if (!empty($_GET['query'])) {
            $args['s'] = $_GET['query'];
        }

        $products      = get_posts($args);
        $prodcut_query = new WP_Query($args);

        // map prodcut id , title, thumbnails and categoris
        $products = array_map(function ($item) {
            $id            = $item->ID;
            $title         = $item->post_title;
            $feature_image = get_the_post_thumbnail_url($id, array('100', '100'));

            return compact('id', 'title', 'feature_image');
        }, $products);

        $max_page = $prodcut_query->max_num_pages;

        wp_send_json(compact('products', 'max_page'));
    }
}
