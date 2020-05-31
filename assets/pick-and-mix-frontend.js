

let price = document.querySelector('.price')
let totalProductAllowed;
let productsToSelect;
let selected_variation={}
let bundle_products=[] 


// Get data passed in
const {available_variations, attributes, related_products,variation_custom_properties, product_id, ajax_url} = data

// save price HTML
let priceHTML = price.innerHTML
// ON Change

const findCurrentProductVariation = (attribute, value) => {
    const selectedVariation = available_variations.find((variation) =>  variation.attributes[`attribute_${attribute}`] == value)

    return selectedVariation
}






//Updates the products bundled for the form.
const updateBundleState = (data) => {
    if (data.value === 0 ){
        // Update this to use product ID
        bundle_products.filter((product) => product.name !== data.name)
    }else{
       const productIndex =  bundle_products.findIndex((product) => product.name == data.name)
       
       if(productIndex !== -1){
           bundle_products[productIndex].value = data.value
       }else{
           bundle_products.push(data)
       }
    }
}

const clearBundleState = () => {
    bundle_products = []
}

const updateVariationState = (data) => {
    if (data.variation_id){
        selected_variation = data
    }else{
        selected_variation = {}
    }
}




const populateRelatedProductsSelect =  (numberToPopulate) => {
    // populate all dropdowns with option values
    let dropdowns =  document.querySelectorAll('.related-product-select')
    
    if(dropdowns.length > 0){
        dropdowns.forEach((dropdown,index) =>{
        // if it has a value selected in it repopulate with values below what is selected
        
        if (dropdown.value > 0){
            const currentValue = dropdown.value
            const totalLeft = parseInt(currentValue) + parseInt(numberToPopulate)
            dropdown.innerHTML=''
            for (let i =0; i<=totalLeft; i++){
                let option =  `<option value=${i}>${i}</option>`
                dropdown.innerHTML+=option
                }
            dropdown.querySelectorAll(`select option`)[currentValue].selected = true
          
            }else{
                dropdown.innerHTML=''
      
        for (let i =0; i<=numberToPopulate; i++){
           
            let option =  `<option value=${i}>${i}</option>`
            dropdown.innerHTML+=option
            }
        }
        }) 
    }
}

const updateProductsToSelect  = () => {
    let dropdowns =  document.querySelectorAll('.related-product-select')
    if(dropdowns.length > 0){
        productsToSelect = totalProductAllowed
        dropdowns.forEach((dropdown)=>{
            productsToSelect -= dropdown.value
        })
    }
}

const relatedProductSelectEventListeners = (shouldRemove) => {
    // add/Remove event listners from this component
    let dropdowns =  document.querySelectorAll('.related-product-select')
    let bundleProductNames =  document.querySelectorAll('.bundle-product-name')
    
    if(dropdowns.length > 0){
        dropdowns.forEach((dropdown,index) =>{
            if (!shouldRemove){
                dropdown.value
            dropdown.addEventListener('change', ()=>{
                updateProductsToSelect();
                updateBundleState({name:bundleProductNames[index].textContent.trim(), value:dropdown.value})
                populateRelatedProductsSelect(productsToSelect);
                validateForm()
            })
        }else{
            clearBundleState()
            validateForm()
            dropdown.removeEventListener()
        }
    })
    
    }


}

const showRelatedProducts = (isHidden, totalProductAllowed=0 ) => {
    let relatedProductsBox = document.getElementById('related-products')
    let variationAlert = document.getElementById('variation-alert')
    relatedProductsBox.innerHTML = ''
    variationAlert.innerHTML = ''

    if(!isHidden){
        variationAlert.innerHTML += `<div class='woocommerce-message'> You may pick a total of ${totalProductAllowed} items</div>`
        related_products.forEach((item,index)=> {
            relatedProductsBox.innerHTML += `<div class="related-product-data">
            <span class="bundle-product-name">
            ${item}
        </span>
                <select class="related-product-select"> 
                </select>
            </div>`
        }) 

    }
} 



// For Each variation (should not have more than one but this allows dynamic name setting)
Object.keys(attributes).forEach((attribute) => {

// Get select element
let variationSelect = document.getElementById(attribute)

//Set event listener for on change
variationSelect.addEventListener('change', () => {

    // get the value in the select 

    if(variationSelect.value){

   const variation = findCurrentProductVariation(attribute,variationSelect.value)
   
   totalProductAllowed = variation_custom_properties[variation.variation_id]
    productsToSelect = totalProductAllowed;
    console.log('total Product',totalProductAllowed)

    price.innerHTML = variation.price_html
    showRelatedProducts(false,totalProductAllowed)
    populateRelatedProductsSelect(totalProductAllowed)
    relatedProductSelectEventListeners(false)
    updateVariationState({variation_id:variation.variation_id, attributes:variation.attributes})
 
    }else{
    //remove price
    // hide notice box 
    // hide options
    price.innerHTML = priceHTML
    showRelatedProducts(true)
    updateVariationState({})
    }
    clearBundleState()
    validateForm()
    }
)
})

 console.log(data)


console.log('attached')



let submitFormButton = document.querySelector('.single_add_to_cart_button')
submitFormButton.disabled = true
//disabled wc-variation-selection-needed


const validateForm = () => {
    const validVariation = Object.keys(selected_variation).length !== 0
    let totalBundleProductsCount =0 
    let validBundleState = false

    console.log('bundleState',validBundleState, 'variationState', validVariation)


    if(bundle_products.length > 0){
        totalBundleProductsCount = bundle_products.reduce((acc,curr) => acc+= parseInt(curr.value),0)
        validBundleState = totalBundleProductsCount == totalProductAllowed;
    }
    
    submitFormButton.disabled = validVariation == true && validBundleState == true ? false : true
  
}

// const  SubmitFormData = (data) =>{
//     let xhttp = new XMLHttpRequest()
//     console.log(ajax_url)
//     xhttp.open("POST",'/wp-admin/admin-ajax.php',true)
//     xhttp.onreadystatechange = () => {
//         if(xhttp.readyState == 4 && xhttp.status == 200){
//             console.log('success',xhttp.status, xhttp.readyState)
//         }else{
//             console.log('failed',xhttp.status, xhttp.readyState)
//         }
//     }
//     xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//     xhttp.send("action=custom_ajax_add_to_cart")
// }


(function ($) {
    $( document ).on( 'click', '.single_add_to_cart_button', function(e) {
            e.preventDefault();

            var $thisbutton = $(this)
            const data = {
                ...selected_variation.attributes,
                action: 'ob_cart',
                product_id:parseInt(product_id),
                // "add-to-cart":parseInt(product_id),
                quantity:1,
                bundle_data:JSON.stringify(bundle_products),
                product_sku:'',
                variation_id: selected_variation.variation_id
            }
            $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

            $.ajax({
                type:'POST',
                url:ajax_url,
                data,
                beforeSend:(response) => {
                    $thisbutton.removeClass('added').addClass('loading')
                },
                complete:(response) => {
                    $thisbutton.addClass('added').removeClass('loading')
                },
                success:(response) =>{
                    if(response.error & response.product_url){
                        window.location = response.product_url
                        
                    }else{
                        console.log(response)
                        $( document.body ).trigger( 'wc_fragments_loaded' );
                        $(document.body).trigger('added_to_cart', [response.fragments,response.cart_hash, $thisbutton]);
                        $( document.body ).trigger( 'cart_page_refreshed' );
                        // location.reload();
                    }
                }
            })


    })
return false;
})(jQuery)


// submitFormButton.addEventListener('click', (e) => { 
//     e.preventDefault()
//     console.log("clicked")
 
//     }

//     SubmitFormData(data)
   

// })



