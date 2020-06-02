<?php

/**
 * @package WoocommerceComboProduct
 */

class Deactivate_Woocommerce_Combo_Product_Plugin
{

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

}
