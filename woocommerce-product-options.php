<?php

/**
 * @package WoocommerceProductOptions
 */

/*
Plugin Name: Woocommerce Product Options
Plugin URI: http://google.com
Description: Plugin to add extra product options
Version: 1.0.0
Author: Omar Bello
Author: http://google.com
Liscense: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    die;
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    class WoocommerceProductOptionsPlugin
    {
        public $currentProductId;
        public function __construct()
        {
            add_action('admin_head', array($this, 'init'));
            add_action('woocommerce_before_single_product_summary', array($this, 'show_custom_text'), 5);
            add_filter('product_type_selector', array($this, 'add_pick_and_mix_product_type'));
            add_action('init', 'create_pick_and_mix_product_class');
            add_action('init', 'create_pick_and_mix_product_class_data_store');
            add_filter('woocommerce_product_class', array($this, 'load_pick_and_mix_product_class'), 10, 2);
            add_action('woocommerce_product_options_general_product_data', array($this, 'misha_option_group'));
            add_action('admin_footer', array($this, 'simple_rental_custom_js'));
            add_filter('woocommerce_product_data_tabs', array($this, 'wcs_show_attributes_data_panel'), 10, 1);
            add_action('wp_enqueue_scripts', array($this, 'add_my_script'));
            add_action('woocommerce_variation_options_pricing', array($this, 'add_custom_field_to_variations'), 10, 3);
            add_action('woocommerce_save_product_variation', array($this, 'save_custom_field_variations'), 10, 2);
            add_filter('woocommerce_available_variation', array($this, 'add_custom_field_variation_data'));

            // add_action('template_redirect', array($this, 'wpse69369_special_thingy'));
        }

        public function init()
        {

        }

        public function show_custom_text()
        {
            global $product;
            // $args = array(
            //     'tag' => "pick&mix",
            // );
            // $products = wc_get_products($args);

            // $filterTag = "pick&mix";

            // foreach ($products as $product) {
            //     $tags = wc_get_product_tag_list($product->get_id());
            //     print_r($product->get_tag_ids());
            if (has_term(28, 'product_tag')) {
                echo '<div class="woocommerce-product-gallery" style="background: #fdfd5a; padding: 1em 2em">' . $product->get_name() . '</div>';
                return;
            }
            // return;
            // }

        }
        public function misha_option_group()
        {
            echo '<div class="option_group">';

            woocommerce_wp_checkbox(array(
                'id' => 'super_product',
                'value' => get_post_meta(get_the_ID(), 'super_product', true),
                'label' => 'This is a super product',
                'desc_tip' => true,
                'description' => 'If it is not a regular WooCommerce product',
            ));

            echo '</div>';
        }

        public function wcs_show_attributes_data_panel($tabs)
        {
            $product_type = 'pickandmix';
            // Other default values for 'attribute' are; general, inventory, shipping, linked_product, variations, advanced
            $tabs['variations']['class'][] = 'show_if_variable show_if_' . $product_type;
            $tabs['attribute']['class'][] = 'show_if_variable show_if_' . $product_type;
            // $tabs['shipping']['class'][] = 'hide_if_pickandmix';
            // $tabs['inventory']['class'][] = 'show_if_pickandmix';

            return $tabs;

        }

        public function simple_rental_custom_js()
        {

            if ('product' != get_post_type()):
                return;
            endif;

            ?><script type='text/javascript'>
                    jQuery( '.options_group.pricing' ).addClass( 'show_if_pickandmix' ).show();
                    jQuery( '.enable_variation' ).addClass( 'show_if_pickandmix' ).show();
                    jQuery(document).ready(function ($) {
                    $('body').on('woocommerce-product-type-change', function () {
                        if ($('select#product-type').val() == 'pickandmix') {
                            $('.show_if_variable').show();
                            $('.hide_if_variable').hide();
                            $('.options_group.pricing ._regular_price_field').show();
                        }
                             });
                              });
                            </script><?php

        }

        public function add_pick_and_mix_product_type($product_types)
        {
            $product_types['pickandmix'] = 'Pick and Mix';
            return $product_types;
        }

        public function load_pick_and_mix_product_class($php_classname, $product_type)
        {
            if ($product_type == 'pickandmix') {
                $php_classname = 'WC_Product_Pick_And_Mix';
            }
            return $php_classname;
        }

        /// CUSTOM FEILD FUNCTIONS (FOR PRODUCT VARIATIONS )
        public function add_custom_field_to_variations($loop, $variation_data, $variation)
        {
            global $post;
            $product = wc_get_product($post->ID);
            if ($product->is_type('pickandmix')) {
                // echo '<div class="options_group">';
                woocommerce_wp_text_input(array(
                    'id' => '_text_field',
                    'class' => 'short',
                    'wrapper_class' => 'form-row',
                    'label' => __('Number Of Sweet Options', 'woocommerce'),
                    'value' => get_post_meta($variation->ID, '_text_field', true),
                ));
            };
            echo '</div>';
        }

        public function save_custom_field_variations($variation_id)
        {
            global $variation;

            $custom_field = $_POST['_text_field'];
            if (isset($custom_field)) {
                update_post_meta($variation_id, '_text_field', esc_attr($custom_field));
            };

        }

        public function add_custom_field_variation_data($variations)
        {
            // $variations['variation_id'] = '<div class="woocommerce_custom_field">Custom Field: <span>' . get_post_meta($variations['variation_id'], 'number_of_sweet_options', true) . '</span></div>';
            return $variations;
        }

        //testing
        // public function wpse69369_special_thingy($content)
        // {
        //     $post_id = get_queried_object_id();

        //     echo $post_id;

        //     return $content;
        // }
        public static function activate()
        {
            flush_rewrite_rules();
        }

        public static function deactivate()
        {
            flush_rewrite_rules();
        }

        public function add_my_script()
        {
            echo plugin_dir_url(__FILE__ . "assets/scripts.js");
            wp_enqueue_script('pluginScript101', plugin_dir_url(__FILE__ . "assets/scripts.js"), array('jquery'));
        }
    }

    add_filter('woocommerce_data_stores', 'setup_data_store');

    function setup_data_store($stores)
    {
        $stores['product-pickandmix'] = 'WC_Product_Pick_And_Mix_Data_Store_CPT';
        return $stores;
    }

    add_filter('woocommerce_product_get_prices', 'reigel_woocommerce_get_price', 20, 2);
    function reigel_woocommerce_get_price($price, $post)
    {
        if ($post->post->post_type === 'pickandmix') {
            $price = get_post_meta($post->id, "price", true);
        }

        return $price;
    }
    function create_pick_and_mix_product_class()
    {

        class WC_Product_Pick_And_Mix extends WC_Product_Variable
        {
            public $product_type;
            public function __construct($product = 0)
            {
                $this->product_type = 'pickandmix';
                $this->manage_stock = 'yes';
                parent::__construct($product);
            }

            public function get_type()
            {
                return 'pickandmix'; // so you can use $product = wc_get_product(); $product->get_type()
            }
        };
    }

    add_action('woocommerce_single_product_summary', 'pick_and_mix_template', 60);

    function pick_and_mix_template()
    {
        global $product;
        if ('pickandmix' === $product->get_type()) {
            $template_path = plugin_dir_path(__FILE__) . 'templates/';
            //Load the template
            wc_get_template('pickandmix.php', '', '', trailingslashit($template_path));
        }
    }

    function create_pick_and_mix_product_class_data_store()
    {

        class WC_Product_Pick_And_Mix_Data_Store_CPT extends WC_Product_Variable_Data_Store_CPT
        {
            // public $product_type;
            // public function __construct($product = 0)
            // {
            //     $this->product_type = 'pickandmix';
            //     $this->manage_stock = 'yes';
            //     parent::__construct($product);
            // }

            // public function get_type()
            // {
            //     return 'pickandmix'; // so you can use $product = wc_get_product(); $product->get_type()
            // }
        };
    }

    if (class_exists('WoocommerceProductOptionsPlugin')) {
        $plugin = new WoocommerceProductOptionsPlugin();
    }
}
// activation
register_activation_hook(__FILE__, array($plugin, 'activate'));

// deactivation
register_deactivation_hook(__FILE__, array($plugin, 'deactivate'));
