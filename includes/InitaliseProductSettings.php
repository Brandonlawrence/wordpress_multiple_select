<?php

/**
 * @package WoocommerceComboProduct
 */

class InitaliseProductSettings
{
    public function register()
    {
        // Initialises New Product Class
        add_action('init', 'create_combo_product_class');
        // Initialises new Data store for Product Class
        add_action('init', 'create_combo_product_class_data_store');
        // Initialises Data store
        add_filter('woocommerce_data_stores', array($this, 'setup_data_store'));
        // Loads Product Class so that it appears in the product selector on the products page and can be loaded
        add_filter('product_type_selector', array($this, 'combo_product_type'));
        add_filter('woocommerce_product_class', array($this, 'load_combo_product_class'), 10, 2);
    }

    public function setup_data_store($stores)
    {
        $stores['product-' . PRODUCT_TYPE] = 'WC_Product_Combo_Product_Data_Store_CPT';
        return $stores;
    }

    public function combo_product_type($product_types)
    {
        $product_types[PRODUCT_TYPE] = PRODUCT_NAME;
        return $product_types;
    }

    public function load_combo_product_class($php_classname, $product_type)
    {
        if ($product_type == PRODUCT_TYPE) {
            $php_classname = 'WC_Product_Combo_Product';
        }
        return $php_classname;
    }

}

// THESE FUNCTION HAVE TO BE OUTSIDE OF THE CLASS AS YOU CAN INITIALISE A CLASS INSIDE ANOTHER CLASS //

function create_combo_product_class()
{

    // Extends variable product so that we get variation functions
    class WC_Product_Combo_Product extends WC_Product_Variable
    {
        public $product_type;
        public function __construct($product = 0)
        {
            $this->product_type = PRODUCT_TYPE;
            $this->manage_stock = 'yes';
            parent::__construct($product);
        }

        public function get_type()
        {
            return PRODUCT_TYPE; // so you can use $product = wc_get_product(); $product->get_type()
        }
    };
}

function create_combo_product_class_data_store()
{

    class WC_Product_Combo_Product_Data_Store_CPT extends WC_Product_Variable_Data_Store_CPT
    {

    };
}
