<?php

/**
 * @package WoocommerceComboProduct
 */

class Activate_Woocommerce_Combo_Product_Plugin
{

    public static function activate()
    {
        flush_rewrite_rules();
    }

}
