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

define("PLUGIN_DIR_URL", plugin_dir_url(__FILE__));

require_once plugin_dir_path(__FILE__) . 'includes/Enqueue.php';
require_once plugin_dir_path(__FILE__) . 'includes/InitaliseProductSettings.php';
require_once plugin_dir_path(__FILE__) . 'includes/ProductBackendTemplate.php';
require_once plugin_dir_path(__FILE__) . 'includes/ProductFrontendTemplate.php';

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    class WoocommerceProductOptionsPlugin
    {

        public $classes;

        public function __construct()
        {
            $this->classes = [
                'Enqueue',
                'InitaliseProductSettings',
                'ProductBackendTemplate',
                'ProductFrontendTemplate',
            ];

        }

        public function register()
        {
            add_action('wp_ajax_ob_cart', array($this, 'woocommerce_ajax_add_to_cart'), 1);
            add_action('wp_ajax_nopriv_ob_cart', array($this, 'woocommerce_ajax_add_to_cart'), 1);
            foreach ($this->classes as $class) {
                $instance = new $class();
                $instance->register();
            }

        }

        public function woocommerce_ajax_add_to_cart()
        {
            ob_start();
            $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
            $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
            $variation_id = absint($_POST['variation_id']);
            $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
            $product_status = get_post_status($product_id);
            $user_custom_data_values = $_POST['bundle_data'];
            // if ($_SESSION) {
            session_start();
            session_unset();
            $_SESSION['bundle_user_custom_data'] = $user_custom_data_values;

            if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) {

                do_action('woocommerce_ajax_added_to_cart', $product_id);

                if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
                    wc_add_to_cart_message(array($product_id => $quantity), true);
                }

                WC_AJAX::get_refreshed_fragments();
            } else {

                $data = array(
                    'error' => true,
                    'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));

                echo wp_send_json($data);
            }

            wp_die();

        }

    }

    if (class_exists('WoocommerceProductOptionsPlugin')) {
        $plugin = new WoocommerceProductOptionsPlugin();
        $plugin->register();
    }
}

// add_action('woocommerce_pickandmix_add_to_cart', 'woocommerce_variable_add_to_cart', 30);
// activation
require_once plugin_dir_path(__FILE__) . 'includes/Activate.php';
register_activation_hook(__FILE__, array('Activate_Woocommerce_Product_Options', 'activate'));

// deactivation
require_once plugin_dir_path(__FILE__) . 'includes/Deactivate.php';
register_deactivation_hook(__FILE__, array('Deactivate_Woocommerce_Product_Options', 'deactivate'));
