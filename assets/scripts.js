jQuery(function ($) {
  // Variable type options are valid for variable workshop.
  $(".show_if_variable:not(.hide_if_gift-card)").addClass("show_if_gift-card");

  // Trigger change
  $("select#product-type").change();

  // Show variable type options when new attribute is added.
  $(document.body).on("woocommerce_added_attribute", function (e) {
    $("#product_attributes .show_if_variable:not(.hide_if_gift-card)").addClass(
      "show_if_gift-card"
    );

    var $attributes = $("#product_attributes").find(".woocommerce_attribute");

    if ("gift-card" == $("select#product-type").val()) {
      $attributes.find(".enable_variation").show();
    }
  });
});
