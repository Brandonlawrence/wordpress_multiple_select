
// DATA PASSED IN 
const {available_variations, attributes, related_products,variation_custom_properties, product_id, ajax_url} = data
//used for image timeout
var timeoutHandle = window.setTimeout(() => {},300)
console.log(related_products);
// GLOBAL VARIABLES 
let totalProductAllowed = 0;
let productsToSelect;
let selected_variation={}
let childProducts = []
let quantity = 1;
let attributeName='';



// GLOBAL DOM ELEMENTS
let quantityWrapper = document.querySelector('.woocommerce-variation-add-to-cart .quantity');
let priceDisplay = document.querySelector('.price')
let variationSelect; 
let resetVariationButton = document.querySelector('.reset_variations')
let childProductsDisplay = document.getElementById('related-products')
let variationInfoAlert = document.getElementById('variation-alert')
let childProductSelectFields =  document.querySelectorAll('.related-product-select')
let childProductNameFields;
let submitFormButton = document.querySelector('.single_add_to_cart_button')
let initialPriceHTML;
let initialImageElement;

//GET VARIATION SELECT ELEMENT
if (Object.keys(attributes).length > 0){
    attributeName = Object.keys(attributes)[0].toLowerCase()
    variationSelect = document.getElementById(attributeName)
}


//////// /// FUNCTIONS RELATED TO UPDATING THE GLOBAL STATE VALUES /// /////////


// CLEARS THE CHILD STATE VALUE
const clearChildProductState = () => {
    childProducts = []
}

// UPDATES THE STATE OF THE CHILD PRODUCTS // 
const updateChildProductState = (data) => {
    if (data.value === 0 ){
        // Update this to use product ID
        childProducts.filter((product) => product.name !== data.name)
    }else{
       const productIndex =  childProducts.findIndex((product) => product.name == data.name)
       
       if(productIndex !== -1){
           childProducts[productIndex].value = data.value
       }else{
           childProducts.push(data)
       }
    }
}

// UPDATES THE STATE OF TOTAL NUMBER OF CHILD PRODUCTS WHICH CAN BE SELECTED
const updateChildProductsToSelectState  = () => {
    productsToSelect = totalProductAllowed
    if(childProductSelectFields.length > 0){
        childProductSelectFields.forEach((dropdown)=>{
            productsToSelect -= dropdown.value
        })
    }
}

// UPDATES THE VARIATION STATE VALUE 
const updateVariationState = (data={},clearState=false) => {
    if (data.variation_id){
        selected_variation = data
    }else{
        selected_variation = {}
    }
}


// //// FUNCTIONS FOR GENERATING/ REMOVING  DOM ELEMENTS //// ////


  // REPLACES THE CURRENT IMAGE OF THE CHILD PRODUCT WITH THE ORIGINAL ONE FOR THE VARIATION OF THE PRODUCT
    const resetImageUrl = () =>{
        currentImageElement = document.querySelector('.woocommerce-product-gallery__wrapper')
    if(initialImageElement){
        currentImageElement.outerHTML = initialImageElement.outerHTML
    }
    }
    
    //REPLACES THE MAIN VARIATION IMAGE WITH A CHILD IMAGE FOR A DURATION OF 3 SECONDS AND THEN SETS IT BACK
    const setVariationImage = (url) => {
    const imageElement = document.querySelector('.woocommerce-product-gallery__wrapper')
    const zoomButton = document.querySelector('.zoom-button')
    
    if(imageElement){
        imageElement.outerHTML = `<img style="width:100%;"src="${url}" class="woocommerce-product-gallery__wrapper"/>`
        window.clearTimeout(timeoutHandle)
        timeoutHandle = window.setTimeout(()=>{
            resetImageUrl()
            if(zoomButton){
                zoomButton.style.display='block'
            }
        },3000)
    }
    if(zoomButton){
        zoomButton.style.display = 'none';
    }
    }
    

//Hides all information related to the child products if they exist on the page
const hideChildProducts = () =>{
    childProductsDisplay.innerHTML = ''
    if(variationInfoAlert){
        variationInfoAlert.innerHTML = ''
    }
    }

// GENERATES THE DOM ELEMENTS FOR THE CHILD PRODUCTS //
const showChildProducts = () => {
    clearFormState()
    if(variationInfoAlert){
    variationInfoAlert.innerHTML += `<div class='woocommerce-message'> You may pick a total of ${totalProductAllowed} items</div>`
    related_products.forEach((item,index)=> {
        childProductsDisplay.innerHTML += `<div class="related-product-data ${!item.inStock && 'related-out-of-stock'}">
        <span class="bundle-product-name">
        ${item.name}${!item.inStock ? " (Out of Stock)": ''}
    </span>
            <select class="related-product-select">
            </select>
            
        </div>`
    }) 

    const outOfStockProducts = document.querySelectorAll('.related-out-of-stock .related-product-select')
    if(outOfStockProducts.length > 0){
        outOfStockProducts.forEach(dropdown => {
            dropdown.disabled = true;
        })
    }


    }

} 

/// VALIDATE WHETHER THE FOR INPUTS CURRENTLY IN THE STATE //
const validateForm = () => {
    const validVariation = Object.keys(selected_variation).length !== 0
    let totalBundleProductsCount =0 
    let validBundleState = false

    if(childProducts.length > 0){
        totalBundleProductsCount = childProducts.reduce((acc,curr) => acc+= parseInt(curr.value),0)
        validBundleState = totalBundleProductsCount == totalProductAllowed;
    }
    
    submitFormButton.disabled = validVariation == true && validBundleState == true ? false : true

}


/// POPULATES THE CHILD PRODUCT SELECT ELEMENTS WITH THE CORRECT NUMBER /// 
const populateChildProductsSelect =  () => {
    // populate all child selects with option values
    getChildProductSelectFields();
    if(childProductSelectFields.length > 0){
        childProductSelectFields.forEach((dropdown,index) =>{
        // if it has a value selected in it repopulate with values below what is selected and above (upto maximum possible to select)
        if (dropdown.value > 0){
            const currentSelectedValue = dropdown.value
            const totalChildProductSelectsRemaining = parseInt(currentSelectedValue) + parseInt(productsToSelect)
            // clear all current dropdown values
            dropdown.innerHTML=''
            for (let i =0; i<=totalChildProductSelectsRemaining; i++){
                let option =  `<option value=${i}>${i}</option>`
                dropdown.innerHTML+=option
                }
            // reselect the value which was orignially selected.
            dropdown.querySelectorAll('select option')[currentSelectedValue].selected = true

            // if there was no number selected for this dropdown just populate with the total amount available to select.
            }else{
                
                dropdown.innerHTML=''
                for (let i =0; i<=productsToSelect; i++){
                let option =  `<option value=${i}>${i}</option>`
                 dropdown.innerHTML+=option
            }
        }
        }) 
    }
}


// UPDATE PRICE DISPLAY // 
const updatePriceHTML = (htmlInput) => {
    if(priceDisplay){
        priceDisplay.innerHTML = htmlInput
     }
    
}

// RESET PRICE HTML TO ORIGINAL VALUE//
const resetPriceHTML =  () =>{
    if(priceDisplay && variationSelect){
        if(!variationSelect.value){
        priceDisplay.innerHTML = initialPriceHTML
        const secondaryPriceDisplay = document.querySelector('.woocommerce-variation.single_variation')
        if(secondaryPriceDisplay){
            secondaryPriceDisplay.style.display = 'none'
        }
        }
     }
}



/// // MISC GETTER FUNCTIONS  // /// 

// GET THE CURRENT IMAGE HTML DISPLAYED 
const getInitialImageElement = () => {
    initialImageElement = document.querySelector('.woocommerce-product-gallery__wrapper')
}
  

// FIND THE CURRENT CHILD PRODUCT NAME FROM AN INDEX 
const getChildProductName = (index=0) => {
    // make sure index is valid and that the  array exists
    if(childProductNameFields.length > 0 && index <= childProductNameFields.length - 1){
        return childProductNameFields[index].textContent.trim()
    }else{
        return ''
    }
}


/// GET INITIAL PRICE HTML 
const getInitialPriceHTML = () => {
    if(priceDisplay){
       initialPriceHTML = priceDisplay.innerHTML
    }else{
        initialPriceHTML = '<div></div>'
    }
}


/// GET CHILD PRODUCT SELECT FEILDS 
const getChildProductSelectFields = () => {
   childProductSelectFields =  document.querySelectorAll('.related-product-select')
   childProductNameFields =  document.querySelectorAll('.bundle-product-name')
}


/// FIND THE CURRENT SELECTED DATA FROM AN ATTRIBUTE NAME 
const findCurrentProductVariation = (attribute, value) => {
    const rawVarationData = available_variations.find((variation) =>  variation.attributes[`attribute_${attribute}`] == value)
    return rawVarationData
 }
 


///// ////  GLOBAL APP STATE FUNCTIONS ///  ///// 

/// CLEAR APP STATE -> DOM ELEMENTS AND STATE ELEMENTS /// 
const clearFormState = () => {
    hideChildProducts()
    updateVariationState(clearState=true)
    clearChildProductState()
    resetPriceHTML()
    validateForm()
}

/// SET APP STATE -> DOM ELEMENTS AND STATE ELEMENTS //// 
const setFormState = () => {
    if(variationSelect){
    const rawVarationData = findCurrentProductVariation( attributeName,variationSelect.value)
    totalProductAllowed = variation_custom_properties[rawVarationData.variation_id]
    updateChildProductsToSelectState()
    updatePriceHTML(rawVarationData.price_html)
    showChildProducts()
    populateChildProductsSelect()
    setChildProductSelectEventListeners()
    updateVariationState({variation_id:rawVarationData.variation_id, rawVarationData:rawVarationData.attributes})
    clearChildProductState()
    getInitialImageElement();
    validateForm()
    }
}


//  ////  EVENT LISTENER FUNCTIONS / ///  ///// 

/// EVENT LISTENER FOR CHILD PRODUCT SELECT /// 
const setChildProductSelectEventListeners = (remove=false) => {
    // add/Remove event listeners from the child select fields

    if(childProductSelectFields.length > 0 && childProductNameFields.length > 0){
        childProductSelectFields.forEach((dropdown,index) =>{
            if (!remove){
       
            dropdown.addEventListener('change', ()=>{
                const childProductName = getChildProductName(index)
                updateChildProductsToSelectState();
                updateChildProductState({name:childProductName, value:dropdown.value})
                populateChildProductsSelect()
                validateForm()
            })

            dropdown.addEventListener('click',()=>{
                if(related_products[index].imageUrl){
                setVariationImage(related_products[index].imageUrl)
                }
            })

        }else{
            clearBundleState()
            validateForm()
            dropdown.removeEventListener()
        }
        
    })
    
    }


}


/// EVENT LISTENER FOR THE VARIATION SELECT ELEMENT //// 
const setVariationSelectEventListener = () => {
    //Set event listener for on change
    variationSelect.addEventListener('change', () => {
        if(variationSelect.value){
            setFormState();
        }else{
            clearFormState()
        }
    
    })
}
 

/// QUANTITY INPUT EVENT LISTENERS  //// 
const setQuantityEventListeners = () => {
    if(quantityWrapper){
       
        const plusButton = quantityWrapper.querySelector('.plus')
        const minusButton = quantityWrapper.querySelector('.minus')
        const quantityField =  quantityWrapper.querySelector('.qty')

        if(quantityField){
            quantityField.value = 1
            quantityField.addEventListener("change", () => {
                quantity = currentQuantity.value + 1
            })        
        }
        if(plusButton){
            plusButton.addEventListener('click',()=>{
                quantity += 1
            })  
        }
        
        if(minusButton){
           minusButton.addEventListener('click',()=>{
                quantity -= 1
            })
    
        }
    
       
    }
    }
    

/// SET EVENT LISTENER FOR RESET VARIATION FIELD
const setResetVarationsEventListener = () => {
    if(resetVariationButton){
        resetVariationButton.addEventListener('click',()=>{
        variationSelect.value = ''
        clearFormState()
    })
    }
}





// RUN PROGRAMME
if (variationSelect){
    getInitialImageElement();
    getInitialPriceHTML()
    setQuantityEventListeners()
    setVariationSelectEventListener()
    setResetVarationsEventListener()
    submitFormButton.disabled = true
    //disabled wc-variation-selection-needed
    if(variationSelect.value){
        setFormState();
       
    }else{
        clearFormState();
    }
}




// SEND INFO TO DATABASE
(function ($) {
    $( document ).on( 'click', '.single_add_to_cart_button', function(e) {
            e.preventDefault();
            
            var $thisbutton = $(this)
            const data = {
                ...selected_variation.attributes,
                action: 'combo_product_add_to_cart',
                product_id:parseInt(product_id),
                // "add-to-cart":parseInt(product_id),
                quantity,
                bundle_data:JSON.stringify(childProducts),
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
    
                        $( document.body ).trigger( 'wc_fragments_loaded' );
                        $(document.body).trigger('added_to_cart', [response.fragments,response.cart_hash, $thisbutton]);
                        $( document.body ).trigger( 'cart_page_refreshed' );
                        variationSelect.value = ''
                        clearFormState()
                    }
                }
            })


    })
return false;
})(jQuery)



