<?php

/**
 * @package WoocommerceComboProduct
 */

/*
Plugin Name: Woocommerce Combo Product
Plugin URI: http://google.com
Description: Plugin which allows you to add a combo product which uses other products in your store as options to fill it.
Version: 1.0.0
Author:  Brandon Lawrence & Omar Bello
Liscense: GPLv2 or later
 */

if (!defined('ABSPATH')) {
    die;
}

define("PLUGIN_DIR_URL", plugin_dir_url(__FILE__));
define("PRODUCT_NAME", 'Combo Product');
define("PRODUCT_TYPE", 'combo_product');

require_once plugin_dir_path(__FILE__) . 'includes/Enqueue.php';
require_once plugin_dir_path(__FILE__) . 'includes/InitaliseProductSettings.php';
require_once plugin_dir_path(__FILE__) . 'includes/ProductBackendTemplate.php';
require_once plugin_dir_path(__FILE__) . 'includes/ProductFrontendTemplate.php';

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    class WoocommerceComboProductPlugin
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

    if (class_exists('WoocommerceComboProductPlugin')) {
        $plugin = new WoocommerceComboProductPlugin();
        $plugin->register();
    }
}

// activation
require_once plugin_dir_path(__FILE__) . 'includes/Activate.php';
register_activation_hook(__FILE__, array('Activate_Woocommerce_Combo_Product_Plugin', 'activate'));

// deactivation
require_once plugin_dir_path(__FILE__) . 'includes/Deactivate.php';
register_deactivation_hook(__FILE__, array('Deactivate_Woocommerce_Combo_Product_Plugin', 'deactivate'));
