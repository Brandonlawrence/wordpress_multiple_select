<?php

/**
 * @package WoocommerceComboProduct
 */

require_once plugin_dir_path(__FILE__) . 'ComboProductHelperFunctions.php';
class ComboProductBackendTemplate
{
    public function register()
    {
        // Set the visibility of product tabs
        add_filter('woocommerce_product_data_tabs', array($this, 'show_attributes_data_panel'), 10, 1);
        // Loads the script for adding extra options to the product tabs
        add_action('admin_footer', array($this, 'default_tab_options_combo_product'));
        // Add extra field for the Number of Options for a variation
        add_action('woocommerce_variation_options_pricing', array($this, 'add_custom_field_to_variations'), 10, 3);
        // Saves the custom field value onto the variation post Meta
        add_action('woocommerce_save_product_variation', array($this, 'save_custom_field_variations'), 10, 2);
        // Loads script for the Combo Product tab on the product
        add_action('woocommerce_product_data_panels', array($this, 'combo_product_tab_options'));
        // Saves the tag used for the product onto the product meta
        add_action('woocommerce_process_product_meta', array($this, 'save_combo_product_options'));
    }

    public function show_attributes_data_panel($tabs)
    {
        $product_type = PRODUCT_TYPE;
        // Other default values for 'attribute' are; general, inventory, shipping, linked_product, variations, advanced
        $tabs['variations']['class'][] = 'show_if_' . $product_type;
        $tabs['attribute']['class']['attribute_tab'][] = 'show_if_' . $product_type;
        $tabs['combo_product'] = array('label' => __('Combo Product', 'wcpt'), 'target' => 'combo_product_options', 'class' => ('show_if_' . $product_type));
        return $tabs;
    }

    public function default_tab_options_combo_product()
    {
        if ('product' != get_post_type()) {
            return;
        }
        $data = array(
            'product_type' => PRODUCT_TYPE,
        );

        wp_enqueue_script('combo-product-backend-settings', PLUGIN_DIR_URL . ('assets/combo-product-backend-settings.js'), array('jquery'));
        wp_localize_script('combo-product-backend-settings', 'backend_vars', $data);
    }

    public function add_custom_field_to_variations($loop, $variation_data, $variation)
    {
        global $post;
        $product = wc_get_product($post->ID);
        if ($product->is_type(PRODUCT_TYPE)) {
            woocommerce_wp_text_input(array(
                'id' => '_child_product_count',
                'class' => 'short',
                'type' => 'number',
                'custom_attributes' => array('required' => 'required'),
                'wrapper_class' => 'form-row',
                'label' => __('Number Child Product Options for Variation', 'woocommerce'),
                'value' => get_post_meta($variation->ID, '_child_product_count', true),
            ));
        };
    }

    public function save_custom_field_variations($variation_id)
    {
        global $variation;
        $custom_field = $_POST['_child_product_count'];
        if (isset($custom_field)) {
            update_post_meta($variation_id, '_child_product_count', esc_attr($custom_field));
        };

    }

    public function combo_product_tab_options()
    {
        global $product;

        $tagged_products_with_data = ComboProductHelperFunctions::get_data_for_all_products_with_tags();

        $data = array(
            'productsWithTags' => $tagged_products_with_data,
            'product_type' => PRODUCT_TYPE,
        );

        // JQuery script for the combo product tab
        wp_enqueue_script('combo-product-tab-options', PLUGIN_DIR_URL . ('assets/combo-product-tab-options.js'), array('jquery'));
        wp_enqueue_style('combo-product-tab-options-styles', PLUGIN_DIR_URL . ('assets/combo-product-tab-options.css'));
        wp_localize_script('combo-product-tab-options', 'backendVars', $data);

        // Template for the view of the product tab
        $template_path = plugin_dir_path(__FILE__) . 'templates/';
        wc_get_template('combo-product-tab.php', $data, '', trailingslashit($template_path));
    }

    public function save_combo_product_options($post_id)
    {
        $combo_product_unique_tag = $_POST['_combo_product_tag'];

        if (isset($combo_product_unique_tag)) {
            update_post_meta($post_id, '_combo_product_tag', $combo_product_unique_tag);
        }

    }

}
