jQuery(document).ready(function ($) {
  const {product_type} = backend_vars
  // Variable type options are valid for variable workshop.
  $( '.show_if_variable:not(.hide_if_pickandmix)' ).addClass( `show_if_${product_type}` );

  // Trigger change
  $( 'select#product-type' ).change();
 

  $( 'body' ).on( 'woocommerce_added_attribute reload woocommerce-product-type-change', () => {
      if ($('select#product-type').val() == product_type){

  $( `#product_attributes .show_if_variable:not(.hide_if_${product_type})` ).addClass( `show_if_${product_type}` );
  var $attributes     = $( '#product_attributes' ).find( '.woocommerce_attribute' );
  if ( product_type == $( 'select#product-type' ).val() ) {
  $attributes.find( '.enable_variation' ).show();
  }


}})


$('body').on('woocommerce_added_attribute reload woocommerce-product-type-change', (e) => {
  if ($('select#product-type').val() == 'pickandmix'){
    $('.product_attributes').prepend(
      "<div class='product-info-text'><p>You can only have one attribute for this product type</p></|div>"
        )
    
  let $attributeValues  = $('.woocommerce_attribute')
  if($attributeValues.length >= 1){
    $('.add_attribute').attr('disabled',true)

  }else{
    $('.add_attribute').attr('disabled',false)
  }
}else{
  $('.add_attribute').attr('disabled',false)
}
})

$('body').on('woocommerce-product-type-change', (e) => {
  let $attributeValues  = $('.woocommerce_attribute')
  if($attributeValues.length > 1){
    if($($attributeValues).length>1){
      $attributeValues.splice(0, 1)
     $attributeValues.each((index, elm)=> $(elm).remove())
      $('.save_attributes').click();
    }
  }

})






})