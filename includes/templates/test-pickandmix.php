<?php
/**
 * Simple custom product
 */
if (!defined('ABSPATH')) {
    exit;
}

global $product;
$selectedTag = get_post_meta($product->get_id(), '_combo_product_tag');

$args = [
    'tag' => 'bag',
];
$pickandMixProduct = wc_get_products($args);
$optionNames = [];
$attributes = $product->get_variation_attributes();
$attribute_keys = array_keys($attributes);
$pricingData = [];

$variations = $product->get_available_variations();

$allowedSweets = [];
foreach ($variations as $variation) {
    $id = $variation['variation_id'];
    array_push($allowedSweets, get_post_meta($id, '_text_field', true));
}

$totalPicks = 10;
foreach ($pickandMixProduct as $option) {
    array_push($optionNames, $option->get_name());
}

do_action('pickandmix_before_add_to_cart_form');?>
<form class="pickandmix_cart" method="post" enctype='multipart/form-data'>
<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
	<table cellspacing="0">
		<tbody>
			<!-- <tr>
				<td >
					<label for="pickandmix_amount"><?php echo __("Amount", 'wcpt'); ?></label>
				</td>
				<td class="price">
					<?php $get_price = get_post_meta($product->get_id(), '_price');
$price = 0;
if (isset($get_price[0])) {
    $price = wc_price($get_price[0]);
}
echo $price;
?>
				</td>

			</tr> -->

        <?php foreach ($attributes as $attribute_name => $options): ?>
					<tr>
						<td class="label"><label for="<?php echo esc_attr(sanitize_title($attribute_name)); ?>"><?php echo wc_attribute_label($attribute_name); // WPCS: XSS ok.                                                 ?></label></td>
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
        <script type="text/javascript">
    $('#color').change(function(){
        var $selectedValue = $(this).children("option:selected").val()
        var $price = '<?php echo 'test'; ?>';
       $('.woocommerce-variation-price').text('hello');
    })
</script>

	<div class="woocommerce-variation-description"></div>
	<div class="woocommerce-variation-price"></div>
	<div class="woocommerce-variation-availability"></div>
<div id="price"></div>
    <ul>
    <div><?php echo ($allowedSweets[0]); ?></div>
    <?php foreach ($optionNames as $index => $option): ?>
    <li><?php echo $option ?>
    <select name="<?php $index?>">
    <?php for ($x = 0; $x <= $totalPicks; $x++): ?>
    <option><?php echo $x ?></option>
    <?php endfor;?>

    </select>
    </li>
<?php endforeach;?>
    </ul>
	<button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
</form>

<?php do_action('pickandmix_after_add_to_cart_form');?>