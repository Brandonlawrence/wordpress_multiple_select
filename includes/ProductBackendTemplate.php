<?php

/**
 * @package WoocommerceProductOptions
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
        // add_filter('woocommerce_available_variation', array($this, 'add_custom_field_variation_data'));
    }

    public function wcs_show_attributes_data_panel($tabs)
    {
        $product_type = 'pickandmix';
        // Other default values for 'attribute' are; general, inventory, shipping, linked_product, variations, advanced
        $tabs['variations']['class'][] = 'show_if_variable show_if_' . $product_type;
        $tabs['attribute']['class'][] = 'show_if_variable show_if_' . $product_type;
        // $tabs['shipping']['class'][] = 'hide_if_pickandmix';
        // $tabs['inventory']['class'][] = 'show_if_pickandmix';

        return $tabs;

    }

    /// CUSTOM FEILD FUNCTIONS (FOR PRODUCT VARIATIONS )
    public function add_custom_field_to_variations($loop, $variation_data, $variation)
    {
        global $post;
        $product = wc_get_product($post->ID);
        if ($product->is_type('pickandmix')) {
            // echo '<div class="options_group">';
            woocommerce_wp_text_input(array(
                'id' => '_text_field',
                'class' => 'short',
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

        ?><script type='text/javascript'>
                jQuery( '.options_group.pricing' ).addClass( 'show_if_pickandmix' ).show();
                jQuery( '.enable_variation' ).addClass( 'show_if_pickandmix' ).show();
                jQuery(document).ready(function ($) {
                $('body').on('woocommerce-product-type-change', function () {
                    if ($('select#product-type').val() == 'pickandmix') {
                        $('.show_if_variable').show();
                        $('.hide_if_variable').hide();
                        $('.options_group.pricing ._regular_price_field').show();
                    }
                         });
                          });
                        </script><?php

    }

}
