<?php

/**
 * @package WoocommerceProductOptions
 */

class Activate_Woocommerce_Product_Options
{

    public static function activate()
    {
        flush_rewrite_rules();
    }

}
