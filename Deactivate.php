<?php

/**
 * @package WoocommerceProductOptions
 */

class Dectivate_Woocommerce_Product_Options
{

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

}
