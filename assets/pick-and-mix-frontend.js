
//TODO:
let price = document.querySelector('.price')
let totalProductAllowed;
let productsToSelect; 


// Get data passed in
const {available_variations, attributes, related_products,variation_custom_properties } = data

// save price HTML
let priceHTML = price.innerHTML
// ON Change

const findCurrentProduct = (attribute, value) => {
    const selectedVariation = available_variations.find((variation) =>  variation.attributes[`attribute_${attribute}`] == value)

    return selectedVariation
}

const populateRelatedProductsSelect =  (numberToPopulate) => {
    // populate all dropdowns with option values
    let dropdowns =  document.querySelectorAll('.related-product-select')
    
    if(dropdowns.length > 0){
        dropdowns.forEach((dropdown) =>{
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
        console.log('number to populate', productsToSelect)
    }
}

const relatedProductSelectEventListeners = (shouldRemove) => {
    // add/Remove event listners from this component
    let dropdowns =  document.querySelectorAll('.related-product-select')
   
    if(dropdowns.length > 0){
        dropdowns.forEach((dropdown) =>{
            if (!shouldRemove){
                dropdown.value
            dropdown.addEventListener('change', ()=>{
                updateProductsToSelect();
                populateRelatedProductsSelect(productsToSelect);
            })
        }else{
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
                ${item}
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

   const variation = findCurrentProduct(attribute,variationSelect.value)
        totalProductAllowed = variation_custom_properties[variation.variation_id]
        productsToSelect = totalProductAllowed;

    price.innerHTML = variation.price_html
    showRelatedProducts(false,totalProductAllowed)
    populateRelatedProductsSelect(totalProductAllowed)
    relatedProductSelectEventListeners(false)
    }else{
    //remove price
    // hide notice box 
    // hide options
    price.innerHTML = priceHTML
    showRelatedProducts(true)
    }
    }
)
})

 console.log(data)


console.log('attached')