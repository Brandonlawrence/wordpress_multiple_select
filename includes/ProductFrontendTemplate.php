<?php

/**
 * @package WoocommerceComboProduct
 */

class ProductFrontendTemplate
{

    public function register()
    {
        // add_action('woocommerce_pickandmix_add_to_cart', array($this, 'woocommerce_variable_add_to_cart'), 30);
        add_action('woocommerce_pickandmix_add_to_cart', array($this, 'pick_and_mix_template'), 60);
        add_filter('woocommerce_add_cart_item_data', array($this, 'wdm_add_item_data'), 1, 2);
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'wdm_get_cart_items_from_session'), 1, 3);
        add_filter('woocommerce_get_item_data', array($this, 'wdm_add_user_custom_option_from_session_into_cart'), 1, 3);
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'wdm_add_values_to_order_item_meta'), 10, 4);
        add_action('woocommerce_before_cart_item_quantity_zero', array($this, 'wdm_remove_user_custom_data_options_from_cart'), 1, 1);
        // add_filter('woocommerce_cart_item_price', array($this, 'wdm_add_user_custom_option_from_session_into_cart'), 1, 3);
        // add_action('wp_enqueue_scripts', array($this, 'woocommerce_ajax_add_to_cart_js'), 99);
        add_action('wp_ajax_ob_cart', array($this, 'woocommerce_ajax_add_to_cart'), 1);
        add_action('wp_ajax_nopriv_ob_cart', array($this, 'woocommerce_ajax_add_to_cart'), 1);

    }

    public function wdm_remove_user_custom_data_options_from_cart($cart_item_key)
    {
        global $woocommerce;
        // Get cart
        $cart = $woocommerce->cart->get_cart();
        // For each item in cart, if item is upsell of deleted product, delete it
        foreach ($cart as $key => $values) {
            if ($values['bundle_user_custom_data'] == $cart_item_key) {
                unset($woocommerce->cart->cart_contents[$key]);
            }

        }
    }

    public function wdm_add_item_data($cart_item_data, $product_id)
    {
        global $product;
        global $woocommerce;

        session_start();
        if (isset($_SESSION['bundle_user_custom_data'])) {
            $option = $_SESSION['bundle_user_custom_data'];
            session_unset();
            $new_value = array('bundle_user_custom_data' => $option);
        }

        if (empty($option)) {
            return $cart_item_data;
        } else {
            if (empty($cart_item_data)) {
                return $new_value;
            } else {
                return $cart_item_data['bundle_user_custom_data'] = $option;
            }

        }

    }

    public function wdm_add_values_to_order_item_meta($item, $cart_item_key, $values, $order)
    {
        global $woocommerce, $wpdb;
        if (empty($values['bundle_user_custom_data'])) {
            return;
        }

        $bundleData = json_decode(stripslashes($values['bundle_user_custom_data']), true);

        foreach ($bundleData as $bundleProduct) {
            $item->add_meta_data($bundleProduct['name'], $bundleProduct['value']);
        }

    }

    public function wdm_get_cart_items_from_session($item, $values, $key)
    {
        if (array_key_exists('bundle_user_custom_data', $values)) {
            $item['bundle_user_custom_data'] = $values['bundle_user_custom_data'];
        }
        return $item;

    }

    public function wdm_add_user_custom_option_from_session_into_cart($item_data, $cart_item)
    {
        /*code to add custom data on Cart & checkout Page*/
        if (array_key_exists('bundle_user_custom_data', $cart_item)) {
            $bundleData = json_decode(stripslashes($cart_item['bundle_user_custom_data']), true);
            // print_r($bundleData);
            // $return_string = $product_name . "</a><dl class='variation'>";
            // $return_string .= "<table class='wdm_options_table' id='" . $values['product_id'] . "'>";
            foreach ($bundleData as $bundleProduct) {
                // $return_string .= "<tr><td>" . $bundleProduct['name'] . "</td>";
                // $return_string .= "<td>" . $bundleProduct['value'] . "</td> </tr>";
                $item_data[] = array(
                    'key' => __($bundleProduct['name'], 'iconic'),
                    'value' => $bundleProduct['value'],
                    'display' => '',
                );
            }
            return $item_data;
        } else {
            return $item_data;
        }
    }

    public function pick_and_mix_template()
    {
        global $product;
        // if ($product->get_type() === 'pickandmix') {
        $selectedTag = get_post_meta($product->get_id(), '_combo_product_tag');
        $selectedTagSlug = '';
        $allTags = get_terms('product_tag');

        if (!empty($allTags) && !is_wp_error($allTags)) {
            foreach ($allTags as $tag) {
                if ($tag->name == $selectedTag[0]) {
                    $selectedTagSlug = $tag->slug;
                }
            }
        }

        // Args for  Related Products
        $args = [
            'tag' => $selectedTagSlug,
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
            'ajax_url' => admin_url('admin-ajax.php'),
            'product_id' => $product->get_id());

        wp_enqueue_script('pick_and_mix_frontend', PLUGIN_DIR_URL . "assets/pick-and-mix-frontend.js");
        // parse data into the javascript  file
        wp_localize_script('pick_and_mix_frontend', 'data', $data);

        // if ('pickandmix' === $product->get_type()) {
        $template_path = plugin_dir_path(__FILE__) . 'templates/';
        //Load the template
        wc_get_template('pickandmix.php', $data, '', trailingslashit($template_path));
        // }
        // }
    }

    public function woocommerce_ajax_add_to_cart()
    {
        ob_start();
        $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
        $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
        $variation_id = absint($_POST['variation_id']);
        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
        $product_status = get_post_status($product_id);
        $user_custom_data_values = $_POST['bundle_data'];
        // if ($_SESSION) {
        session_start();
        session_unset();
        $_SESSION['bundle_user_custom_data'] = $user_custom_data_values;

        if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) {

            do_action('woocommerce_ajax_added_to_cart', $product_id);

            if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
                wc_add_to_cart_message(array($product_id => $quantity), true);
            }

            WC_AJAX::get_refreshed_fragments();
        } else {

            $data = array(
                'error' => true,
                'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));

            echo wp_send_json($data);
        }

        wp_die();

    }

}
