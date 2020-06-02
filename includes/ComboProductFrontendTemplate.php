<?php

/**
 * @package WoocommerceComboProduct
 */

class ComboProductFrontendTemplate
{

    public function register()
    {
        // add_action('woocommerce_pickandmix_add_to_cart', array($this, 'woocommerce_variable_add_to_cart'), 30);
        //Get the custom Combo Product data from the created session and puts it in to the cart data (Backend data)
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'get_custom_cart_items_from_session'), 1, 3);
        // Adds the custom Combo Product data to the cart page when product is added (Backend Data)
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_custom_item_data'), 1, 2);
        // Adds data custom data from session into the cart line item (Frontend View)
        add_filter('woocommerce_get_item_data', array($this, 'add_custom_options_from_session_into_cart'), 1, 3);
        // Adds custom data into the order line item (both in the user and backend order)
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'add_custom_values_to_order_item_meta'), 10, 4);
        // Removes the custom data from the cart data when the item is removed from the cart
        add_action('woocommerce_before_cart_item_quantity_zero', array($this, 'remove_custom_data_options_from_cart'), 1, 1);
        // Creates the combo product page (loads the JS for the view and adds the template)
        add_action('woocommerce_' . PRODUCT_TYPE . '_add_to_cart', array($this, 'combo_product_template'), 60);
        // add_filter('woocommerce_cart_item_price', array($this, 'add_custom_options_from_session_into_cart'), 1, 3);
        // add_action('wp_enqueue_scripts', array($this, 'combo_product_ajax_add_to_cart_js'), 99);
        add_action('wp_ajax_ob_cart', array($this, 'combo_product_ajax_add_to_cart'), 1);
        add_action('wp_ajax_nopriv_ob_cart', array($this, 'combo_product_ajax_add_to_cart'), 1);

    }

    public function get_custom_cart_items_from_session($item, $values, $key)
    {
        if (array_key_exists('combo_product_custom_data', $values)) {
            $item['combo_product_custom_data'] = $values['combo_product_custom_data'];
        }
        return $item;

    }
    public function add_custom_item_data($cart_item_data, $product_id)
    {
        global $product;
        global $woocommerce;

        session_start();
        if (isset($_SESSION['combo_product_custom_data'])) {
            $option = $_SESSION['combo_product_custom_data'];
            session_unset();
            $new_value = array('combo_product_custom_data' => $option);
        }

        if (empty($option)) {
            return $cart_item_data;
        } else {
            if (empty($cart_item_data)) {
                return $new_value;
            } else {
                return $cart_item_data['combo_product_custom_data'] = $option;
            }

        }

    }

    public function add_custom_options_from_session_into_cart($item_data, $cart_item)
    {
        if (array_key_exists('combo_product_custom_data', $cart_item)) {
            $comboProductData = json_decode(stripslashes($cart_item['combo_product_custom_data']), true);
            foreach ($comboProductData as $childProduct) {
                $item_data[] = array(
                    'key' => __($childProduct['name'], 'iconic'),
                    'value' => $childProduct['value'],
                    'display' => '',
                );
            }
            return $item_data;
        } else {
            return $item_data;
        }
    }

    public function add_custom_values_to_order_item_meta($item, $cart_item_key, $values, $order)
    {
        global $woocommerce, $wpdb;
        if (empty($values['combo_product_custom_data'])) {
            return;
        }

        $comboProductData = json_decode(stripslashes($values['combo_product_custom_data']), true);

        foreach ($comboProductData as $childProduct) {
            $item->add_meta_data($childProduct['name'], $childProduct['value']);
        }

    }

    public function remove_custom_data_options_from_cart($cart_item_key)
    {
        global $woocommerce;
        // Get cart
        $cart = $woocommerce->cart->get_cart();
        // For each item in cart, if item is upsell of deleted product, delete it
        foreach ($cart as $key => $values) {
            if ($values['combo_product_custom_data'] == $cart_item_key) {
                unset($woocommerce->cart->cart_contents[$key]);
            }

        }
    }

    // TO FIX
    public function combo_product_template()
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
                $variation_custom_properties[$id] = get_post_meta($id, '_child_product_count', true);
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

        wp_enqueue_script('combo-product-frontend', PLUGIN_DIR_URL . "assets/combo-product-frontend.js");
        // parse data into the javascript  file
        wp_localize_script('combo-product-frontend', 'data', $data);

        // if ('pickandmix' === $product->get_type()) {
        $template_path = plugin_dir_path(__FILE__) . 'templates/';
        //Load the template
        wc_get_template('combo-product-frontend.php', $data, '', trailingslashit($template_path));
        // }
        // }
    }

    public function combo_product_ajax_add_to_cart()
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
        $_SESSION['combo_product_custom_data'] = $user_custom_data_values;

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
