jQuery(document).ready(function ($) {
const {productsWithTags, product_type} = backendVars

const setUpHtml = (inputValue) => {

    let productsMatchingInput = [];

    productsWithTags.forEach((product) => {
        const {inStock, name,tags} = product
        if(tags.find((tag) => inputValue == tag)){
            productsMatchingInput.push({name, inStock})
        }
    })
    
    if(productsMatchingInput.length > 0 ){
    
        let  html = ""
         html+= "<table class='child-product-table'><tr><th>Product Name</th><th>Is In Stock</th></tr>"
        productsMatchingInput.forEach((product) =>{
            const {inStock, name} = product
            html+=`<tr><td>${name}</td><td>${inStock ? "Yes" : "No"}</td></tr>`
    
        })
        $('.linked-child-products').append(html)
    
    }else{
        $('.linked-child-products').append('<div class="no-products">No Products Matching Current Input</div>')
    
    }
}


//If there is already a value in the input get that value and show the table 
let staticInputValue = $('#_combo_product_tag').val()
setUpHtml(staticInputValue);




//Set up event listener to check if an input has been used
$('#_combo_product_tag').on('input', (e,selector) =>{
$('.linked-child-products').find('table').remove();
$('.linked-child-products').find('.no-products').remove();
let inputValue = $('#_combo_product_tag').val()

setUpHtml(inputValue)
}
)


$( 'body' ).on( 'reload woocommerce-product-type-change', () => {
    if ($('select#product-type').val() == product_type){

        let inputValue = $('#_combo_product_tag').val()

        setUpHtml(inputValue)
    }
})

})