document.addEventListener('DOMContentLoaded', () => {
  const quantityInput = document.getElementById('sylius_add_to_cart_cartItem_quantity');

  quantityInput.addEventListener('change', (event) => {
    const value = event.target.value;

    if (70 === parseInt(value)) {
      console.log(value)
      alert('Great Choice!');
    }

  })

}, false);
