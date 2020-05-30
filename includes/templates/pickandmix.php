<?php

/**
 * @package WoocommerceProductOptions
 */

if (!defined('ABSPATH')) {
    exit;
}

global $product;

$attribute_keys = array_keys($attributes);
$variations_json = wp_json_encode($available_variations);
$variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

do_action('pickandmix_before_add_to_cart_form');?>

<form class="pickandmix_cart" method="post" enctype='multipart/form-data'>
<div id='variation-alert'></div>
<?php do_action('pickandmix_before_variations_form');?>
<?php if (empty($available_variations) && false !== $available_variations): ?>
		<p class="stock out-of-stock"><?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?></p>
	<?php else: ?>
		<table class="variations" cellspacing="0">
			<tbody>
				<?php foreach ($attributes as $attribute_name => $options): ?>
					<tr>
						<td class="label"><label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>"><?php echo wc_attribute_label($attribute_name); // WPCS: XSS ok.        ?></label></td>
						<td class="value">
							<?php
wc_dropdown_variation_attribute_options(
    array(
        'options' => $options,
        'attribute' => $attribute_name,
        'product' => $product,
    )
);

echo end($attribute_keys) === $attribute_name ? wp_kses_post(apply_filters('woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__('Clear', 'woocommerce') . '</a>')) : '';
?>
						</td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
        <?php endif;?>
        <div id="related-products"></div>
</form>

<?php do_action('pickandmix_after_add_to_cart_form');?>