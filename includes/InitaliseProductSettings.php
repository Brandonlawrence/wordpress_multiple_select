<?php

/**
 * @package WoocommerceComboProduct
 */

class InitaliseProductSettings
{
    public function register()
    {
        // Initialises New Product Class
        add_action('init', 'create_pick_and_mix_product_class');
        // Initialises new Data store for Product Class
        add_action('init', 'create_pick_and_mix_product_class_data_store');
        // Initialises Data store
        add_filter('woocommerce_data_stores', array($this, 'setup_data_store'));
        // Loads Product Class so that it appears in the selector and can be loaded
        add_filter('product_type_selector', array($this, 'add_pick_and_mix_product_type'));
        add_filter('woocommerce_product_class', array($this, 'load_pick_and_mix_product_class'), 10, 2);
    }

    public function setup_data_store($stores)
    {
        $stores['product-pickandmix'] = 'WC_Product_Pick_And_Mix_Data_Store_CPT';
        return $stores;
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

function create_pick_and_mix_product_class_data_store()
{

    class WC_Product_Pick_And_Mix_Data_Store_CPT extends WC_Product_Variable_Data_Store_CPT
    {

    };
}
