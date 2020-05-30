<?php

/**
 * @package WoocommerceProductOptions
 */

class ProductFrontendTemplate
{

    public function register()
    {
        // add_action('woocommerce_before_add_to_cart_form', 'pick_and_mix_template', 60);
        add_action('woocommerce_pickandmix_add_to_cart', array($this, 'woocommerce_variable_add_to_cart'), 30);
    }

    // public function pick_and_mix_template()
    // {
    //     global $product;
    //     if ('pickandmix' === $product->get_type()) {
    //         $template_path = plugin_dir_path(__FILE__) . 'templates/';
    //         //Load the template
    //         wc_get_template('pickandmix.php', '', '', trailingslashit($template_path));
    //     }
    // }

    /**
     * Output the variable product add to cart area.
     */
    public function woocommerce_variable_add_to_cart()
    {
        global $product;

        // Enqueue variation scripts.
        wp_enqueue_script('wc-add-to-cart-variation', PLUGIN_DIR_URL . "assets/add-to-cart-pickandmix.js");

        // Get Available variations?
        $get_variations = count($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 30, $product);

        // Load the template.
        wc_get_template(
            'single-product/add-to-cart/variable.php',
            array(
                'available_variations' => $get_variations ? $product->get_available_variations() : false,
                'attributes' => $product->get_variation_attributes(),
                'selected_attributes' => $product->get_default_attributes(),
            )
        );
    }

}
