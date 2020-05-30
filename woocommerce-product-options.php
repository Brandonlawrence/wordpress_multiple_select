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
            foreach ($this->classes as $class) {
                $instance = new $class();
                $instance->register();
            }
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
