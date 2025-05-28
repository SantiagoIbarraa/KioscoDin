document.addEventListener('DOMContentLoaded', function() {
    const menuIcon = document.querySelector('.menu-icon')
    const sideNav = document.querySelector('.side-nav')
    const overlay = document.querySelector('.overlay')
    const closeNav = document.querySelector('.close-nav')
    const cartIcon = document.querySelector('.floating-cart')
    const cartPanel = document.querySelector('.cart-panel')
    const closeCart = document.querySelector('.close-cart')
    const addToCartButtons = document.querySelectorAll('.add-to-cart')
    const cartItemsContainer = document.querySelector('.cart-items')
    const cartCount = document.querySelector('.cart-count')
    const cartTotal = document.querySelector('.total-amount')
    
    let cart = []
    
    menuIcon.addEventListener('click', function() {
        sideNav.classList.add('active')
        overlay.classList.add('active')
        document.body.style.overflow = 'hidden'
    })
    
    closeNav.addEventListener('click', function() {
        sideNav.classList.remove('active')
        overlay.classList.remove('active')
        document.body.style.overflow = ''
    })
    
    cartIcon.addEventListener('click', function(e) {
        e.stopPropagation()
        cartPanel.classList.add('active')
        overlay.classList.add('active')
        document.body.style.overflow = 'hidden'
    })
    
    closeCart.addEventListener('click', function() {
        cartPanel.classList.remove('active')
        overlay.classList.remove('active')
        document.body.style.overflow = ''
    })
    
    overlay.addEventListener('click', function() {
        sideNav.classList.remove('active')
        cartPanel.classList.remove('active')
        overlay.classList.remove('active')
        document.body.style.overflow = ''
    })
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card')
            const productName = productCard.querySelector('h3').textContent
            const productPrice = parseFloat(productCard.querySelector('.price').textContent.replace('$', ''))
            const productQuantity = parseInt(productCard.querySelector('.quantity').textContent)
            const productImage = productCard.querySelector('img').src
            
            const existingItem = cart.find(item => item.name === productName)
            
            if (existingItem) {
                existingItem.quantity += productQuantity
            } else {
                cart.push({
                    name: productName,
                    price: productPrice,
                    quantity: productQuantity,
                    image: productImage
                })
            }
            
            updateCart()
            
            cartPanel.classList.add('active')
            overlay.classList.add('active')
            document.body.style.overflow = 'hidden'
            
            productCard.querySelector('.quantity').textContent = '1'
        })
    })
    
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const quantityElement = this.parentElement.querySelector('.quantity')
            let quantity = parseInt(quantityElement.textContent)
            
            if (this.classList.contains('plus')) {
                quantity++
            } else if (this.classList.contains('minus') && quantity > 1) {
                quantity--
            }
            
            quantityElement.textContent = quantity
        })
    })
    
    function updateCart() {
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0)
        cartCount.textContent = totalItems
        
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Tu carrito está vacío</p>
                </div>`
            document.querySelector('.cart-footer').style.display = 'none'
            cartCount.style.display = 'none'
        } else {
            document.querySelector('.cart-footer').style.display = 'block'
            cartCount.style.display = 'flex'
            cartItemsContainer.innerHTML = ''
            
            cart.forEach((item, index) => {
                const cartItem = document.createElement('div')
                cartItem.className = 'cart-item'
                cartItem.innerHTML = `
                    <div class="cart-item-details">
                        <img src="${item.image}" alt="${item.name}">
                        <div>
                            <h4>${item.name}</h4>
                            <p>$${item.price.toFixed(2)} x ${item.quantity}</p>
                        </div>
                    </div>
                    <div class="cart-item-actions">
                        <button class="remove-item" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`
                cartItemsContainer.appendChild(cartItem)
            })
            
            document.querySelectorAll('.remove-item').forEach((button, index) => {
                button.addEventListener('click', function() {
                    cart.splice(index, 1)
                    updateCart()
                })
            })
        }

        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)
        cartTotal.textContent = `$${total.toFixed(2)}`
    }
    
    updateCart()
    
    cartPanel.addEventListener('click', function(e) {
        e.stopPropagation()
    })
    
    sideNav.addEventListener('click', function(e) {
        e.stopPropagation()
    })
})
