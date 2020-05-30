<?php

/**
 * @package WoocommerceProductOptions
 */

class Deactivate_Woocommerce_Product_Options
{

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

}
