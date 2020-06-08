<?php

/**
 * @package WoocommerceProductOptions
 */

if (!defined('ABSPATH')) {
    exit;
}
global $product;

?><div id='combo_product_options' class='panel woocommerce_options_panel'><?php
?><div class='options_group'><?php

woocommerce_wp_text_input(array(
    'id' => '_combo_product_tag',
    'label' => __('Unique Tag', 'wcpt'),
    'placeholder' => '',
    'required' => true,
    'desc_tip' => true,
    'description' => __("Please enter the tag name, this will be the tag value that you will use to tag children products so they appear in the combo product, make sure it's a unqiue tag and avoid using spaces", 'wcpt'),
));

?></div>
<div class="options_group linked-child-products" style="padding:1rem;">
<h3> Linked Child Products</h3>
<div style="padding:1rem 0;">These are the Child products that will appear as dropdowns for the Combo select product using the tag you have in the input
    <strong>for these products to appear you must save these changes.</strong>
</div>
</div>
</div>