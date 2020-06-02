<?php

/**
 * @package WoocommerceComboProduct
 */

class ProductBackendTemplate
{
    public function register()
    {
        // Shows product variations and attributes tabs if
        add_filter('woocommerce_product_data_tabs', array($this, 'wcs_show_attributes_data_panel'), 10, 1);
        add_action('admin_footer', array($this, 'show_variable_product_options'));
        add_action('woocommerce_variation_options_pricing', array($this, 'add_custom_field_to_variations'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'save_custom_field_variations'), 10, 2);
        add_action('woocommerce_product_data_panels', array($this, 'combo_product_tab_options'));
        add_action('woocommerce_process_product_meta', array($this, 'save_combo_product_options'));
    }

    public function wcs_show_attributes_data_panel($tabs)
    {
        $product_type = 'pickandmix';
        // Other default values for 'attribute' are; general, inventory, shipping, linked_product, variations, advanced
        $tabs['variations']['class'][] = 'show_if_' . $product_type;
        $tabs['attribute']['class']['attribute_tab'][] = 'show_if_' . $product_type;
        $tabs['combo_product'] = array('label' => __('Combo Product', 'wcpt'), 'target' => 'combo_product_options', 'class' => ('show_if_pickandmix'));
        return $tabs;

    }

    /// CUSTOM FEILD FUNCTIONS (FOR PRODUCT VARIATIONS )
    public function add_custom_field_to_variations($loop, $variation_data, $variation)
    {
        global $post;
        $product = wc_get_product($post->ID);
        if ($product->is_type('pickandmix')) {
            woocommerce_wp_text_input(array(
                'id' => '_text_field',
                'class' => 'short',
                'type' => 'number',
                'custom_attributes' => array('required' => 'required'),
                'wrapper_class' => 'form-row',
                'label' => __('Number Of Sweet Options', 'woocommerce'),
                'value' => get_post_meta($variation->ID, '_text_field', true),
            ));
        };
    }
    public function save_custom_field_variations($variation_id)
    {
        global $variation;

        $custom_field = $_POST['_text_field'];
        if (isset($custom_field)) {
            update_post_meta($variation_id, '_text_field', esc_attr($custom_field));
        };

    }

    public function show_variable_product_options()
    {
        if ('product' != get_post_type()):
            return;
        endif;
        wp_enqueue_script('backend-product-settings', PLUGIN_DIR_URL . ('assets/product-backend-setttings.js'), array('jquery'));

    }

    public function combo_product_tab_options()
    {
        global $product;

        $terms = get_terms('product_tag');
        $term_array = array();
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $term_array[] = $term->name;
            }
        }

        $args = [
            'limit' => 1000,
            'tag' => $term_array,
        ];

        // get name for related Products
        $tagged_products = wc_get_products($args);
        $related_products = [];

        foreach ($tagged_products as $tagged_product) {

            $productTagsRaw = wp_get_post_terms($tagged_product->get_id(), 'product_tag');
            $productTags = [];
            foreach ($productTagsRaw as $productTag) {
                $productTags[] = $productTag->name;
            }

            $related_products[] = ['name' => $tagged_product->get_name(), 'inStock' => $tagged_product->is_in_stock(), 'tags' => $productTags];
        }

        $data = array(
            'productsWithTags' => $related_products,
        );

        wp_enqueue_script('combo_product_tab_options', PLUGIN_DIR_URL . ('assets/combo_product_tab_options.js'), array('jquery'));
        wp_enqueue_style('combo_product_tab_option_styles', PLUGIN_DIR_URL . ('assets/combo_product_tab_options.css'));
        wp_localize_script('combo_product_tab_options', 'backend_vars', $data);

        $template_path = plugin_dir_path(__FILE__) . 'templates/';
        wc_get_template('combo_product_tab.php', $data, '', trailingslashit($template_path));
    }

    public function save_combo_product_options($post_id)
    {
        $combo_product_unique_tag = $_POST['_combo_product_tag'];

        if (isset($combo_product_unique_tag)) {
            update_post_meta($post_id, '_combo_product_tag', $combo_product_unique_tag);
        }

    }

}
