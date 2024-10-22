const handleProductOptionChange = function handleProductOptionChange() {
  // Listening to Product's quantity change only, as (i.e. within cart) it's the only input field that was required to change
  $('[name*="sylius_add_to_cart[cartItem][quantity]"]').on('change', ({target}) => {
    // Could be as simple as string comparing, but for the sake of demonstration, I'm using parseInt
    //
    // Also, it's javascript
    if (parseInt($(target).val(), 10) === 70) {
      alert('Great Choice!')
    }
  });
};


$.fn.extend({
  variantPrices() {
    handleProductOptionChange();
  }
})
