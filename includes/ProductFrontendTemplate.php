<?php

/**
 * @package WoocommerceProductOptions
 */

class ProductFrontendTemplate
{

    public function register()
    {
        // add_action('woocommerce_pickandmix_add_to_cart', array($this, 'woocommerce_variable_add_to_cart'), 30);
        add_action('woocommerce_single_product_summary', array($this, 'pick_and_mix_template'), 60);
    }

    public function pick_and_mix_template()
    {
        global $product;

        // Args for  Related Products
        $args = [
            'tag' => 'pick&mix',
        ];

        //   Get Available variations?
        $get_variations = count($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 30, $product);
        $available_variations = $get_variations ? $product->get_available_variations() : false;
        $variation_custom_properties = [];

        if ($available_variations) {
            foreach ($available_variations as $variation) {
                $id = $variation['variation_id'];
                $variation_custom_properties[$id] = get_post_meta($id, '_text_field', true);
            }
        }

        // get name for related Products
        $tagged_products = wc_get_products($args);
        $related_products = [];

        foreach ($tagged_products as $tagged_product) {
            $related_products[] = $tagged_product->get_name();
        }

        // Data to pass into the script to deal with the variaton data
        $data = array(
            'available_variations' => $available_variations,
            'attributes' => $product->get_variation_attributes(),
            'selected_attributes' => $product->get_default_attributes(),
            'variation_custom_properties' => $variation_custom_properties,
            'related_products' => $related_products,
            'ajax_url' => admin_url('admin-ajax.php'));

        // Enqueue variation scripts.
        wp_enqueue_script('pick_and_mix_frontend', PLUGIN_DIR_URL . "assets/pick-and-mix-frontend.js");

        // parse data into the javascript  file
        wp_localize_script('pick_and_mix_frontend', 'data', $data);

        if ('pickandmix' === $product->get_type()) {
            $template_path = plugin_dir_path(__FILE__) . 'templates/';
            //Load the template
            wc_get_template('pickandmix.php', $data, '', trailingslashit($template_path),
            );
        }
    }

    /**
     * Output the variable product add to cart area.
     */
    // public function woocommerce_variable_add_to_cart()
    // {
    //     global $product;

    //     // Enqueue variation scripts.
    //     // wp_enqueue_script('wc-add-to-cart-variation', PLUGIN_DIR_URL . "assets/add-to-cart-pickandmix.js");

    //     // Get Available variations?
    //     $get_variations = count($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 30, $product);

    //     // Load the template.
    //     wc_get_template(
    //         'single-product/add-to-cart/variable.php',
    //         array(
    //             'available_variations' => $get_variations ? $product->get_available_variations() : false,
    //             'attributes' => $product->get_variation_attributes(),
    //             'selected_attributes' => $product->get_default_attributes(),
    //         )
    //     );
    // }

}
