document.addEventListener('DOMContentLoaded', function() {
    const menuIcon = document.querySelector('.menu-icon')
    const sideNav = document.querySelector('.side-nav')
    const overlay = document.querySelector('.overlay')
    const closeNav = document.querySelector('.close-nav')
    const cartIcon = document.querySelector('.floating-cart')
    const cartPanel = document.querySelector('.cart-panel')
    const closeCart = document.querySelector('.close-cart')
    const cartItemsContainer = document.querySelector('.cart-items')
    const cartCount = document.querySelector('.cart-count')
    const cartTotal = document.querySelector('.total-amount')
    const categoryButtons = document.querySelectorAll('.category-btn')
    const searchInput = document.querySelector('.search-input')
    const searchButton = document.querySelector('.search-button')
    
    let cart = JSON.parse(localStorage.getItem('cart')) || []
    
    menuIcon.addEventListener('click', toggleSideNav)
    closeNav.addEventListener('click', closeSideNav)
    cartIcon.addEventListener('click', openCart)
    closeCart.addEventListener('click', closeCartPanel)
    overlay.addEventListener('click', closeAllPanels)
    searchButton.addEventListener('click', handleSearch)
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') handleSearch()
    })
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.textContent.toLowerCase()
            filterProducts(category)
            
            categoryButtons.forEach(btn => btn.classList.remove('active'))
            this.classList.add('active')
        })
    })
    
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart')) {
            const button = e.target.closest('.add-to-cart')
            const productCard = button.closest('.product-card')
            const productId = button.getAttribute('data-id')
            const productName = button.getAttribute('data-name')
            const productPrice = parseFloat(button.getAttribute('data-price'))
            const productQuantity = parseInt(productCard.querySelector('.quantity').textContent)
            const productImage = productCard.querySelector('img').src
            
            addToCart({
                id: productId,
                name: productName,
                price: productPrice,
                quantity: productQuantity,
                image: productImage
            })
            
            productCard.querySelector('.quantity').textContent = '1'
        }
        
        if (e.target.closest('.quantity-btn')) {
            const button = e.target.closest('.quantity-btn')
            const quantityElement = button.parentElement.querySelector('.quantity')
            let quantity = parseInt(quantityElement.textContent)
            
            if (button.classList.contains('plus')) {
                quantity++
            } else if (button.classList.contains('minus') && quantity > 1) {
                quantity--
            }
            
            quantityElement.textContent = quantity
        }
        
        if (e.target.closest('.remove-item')) {
            const button = e.target.closest('.remove-item')
            const index = parseInt(button.getAttribute('data-index'))
            cart.splice(index, 1)
            updateCart()
        }
    })
    
    function toggleSideNav() {
        sideNav.classList.add('active')
        overlay.classList.add('active')
        document.body.style.overflow = 'hidden'
    }
    
    function closeSideNav() {
        sideNav.classList.remove('active')
        overlay.classList.remove('active')
        document.body.style.overflow = ''
    }
    
    function openCart(e) {
        e.stopPropagation()
        cartPanel.classList.add('active')
        overlay.classList.add('active')
        document.body.style.overflow = 'hidden'
    }
    
    function closeCartPanel() {
        cartPanel.classList.remove('active')
        overlay.classList.remove('active')
        document.body.style.overflow = ''
    }
    
    function closeAllPanels() {
        sideNav.classList.remove('active')
        cartPanel.classList.remove('active')
        overlay.classList.remove('active')
        document.body.style.overflow = ''
    }
    
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id)
        
        if (existingItem) {
            existingItem.quantity += product.quantity
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                quantity: product.quantity,
                image: product.image
            })
        }
        
        updateCart()
        openCart({ stopPropagation: () => {} })
    }
    
    function updateCart() {
        localStorage.setItem('cart', JSON.stringify(cart))
        
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
        }

        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)
        cartTotal.textContent = `$${total.toFixed(2)}`
    }
    
    function filterProducts(category) {
        const products = document.querySelectorAll('.product-card')
        
        products.forEach(product => {
            if (category === 'all' || category === 'todos') {
                product.style.display = 'block'
            } else {
                const productElement = product.querySelector('.add-to-cart')
                const productCategory = productElement ? productElement.getAttribute('data-category') : ''
                
                if (productCategory === category) {
                    product.style.display = 'block'
                } else {
                    product.style.display = 'none'
                }
            }
        })
    }
    
    function handleSearch() {
        const searchTerm = searchInput.value.toLowerCase()
        const products = document.querySelectorAll('.product-card')
        
        products.forEach(product => {
            const productName = product.querySelector('h3').textContent.toLowerCase()
            const productDesc = product.querySelector('.description').textContent.toLowerCase()
            
            if (productName.includes(searchTerm) || productDesc.includes(searchTerm)) {
                product.style.display = 'block'
            } else {
                product.style.display = 'none'
            }
        })
    }
    
    const paymentModal = document.getElementById('paymentModal')
    const closePaymentModal = document.querySelector('.close-payment-modal')
    const cancelPaymentBtn = document.getElementById('cancelPayment')
    const confirmPaymentBtn = document.getElementById('confirmPayment')
    const orderItemsContainer = document.getElementById('orderItems')
    const orderTotalElement = document.getElementById('orderTotal')
    const paymentMethods = document.querySelectorAll('.payment-method')
    const checkoutBtn = document.querySelector('.checkout-btn')
    let selectedPaymentMethod = null

    paymentMethods.forEach(method => {
        method.addEventListener('click', () => {
            paymentMethods.forEach(m => m.classList.remove('active'))
            method.classList.add('active')
            selectedPaymentMethod = method.getAttribute('data-method')
        })
    })

    checkoutBtn.addEventListener('click', openPaymentModal)

    closePaymentModal.addEventListener('click', closePaymentModalFunc)
    cancelPaymentBtn.addEventListener('click', closePaymentModalFunc)

    const paymentForm = document.getElementById('paymentForm')
    paymentForm.addEventListener('submit', async function(e) {
        e.preventDefault()
        
        if (this.dataset.submitting) return
        this.dataset.submitting = 'true'
        
        try {
            await processPayment()
        } finally {
            delete this.dataset.submitting
        }
    })


    function openPaymentModal() {
        if (cart.length === 0) {
            alert('El carrito está vacío')
            return
        }
        
        closeCartPanel()

        orderItemsContainer.innerHTML = ''
        let total = 0

        cart.forEach(item => {
            const itemElement = document.createElement('div')
            itemElement.className = 'order-item'
            itemElement.innerHTML = `
                <span>${item.quantity}x ${item.name}</span>
                <span>${(item.price * item.quantity).toFixed(2)}$</span>
            `
            orderItemsContainer.appendChild(itemElement)
            total += item.price * item.quantity
        })

        orderTotalElement.textContent = `${total.toFixed(2)}$`
        paymentModal.style.display = 'flex'
        document.body.style.overflow = 'hidden'
    }


    function closePaymentModalFunc() {
        paymentModal.style.display = 'none'
        document.body.style.overflow = ''
    }


    async function processPayment() {
        if (!selectedPaymentMethod) {
            alert('Por favor seleccione un método de pago')
            return
        }

        const deliveryTime = document.getElementById('deliveryTime').value
        if (!deliveryTime) {
            alert('Por favor seleccione una fecha y hora de entrega')
            return
        }

        try {
            const userId = document.body.getAttribute('data-user-id') || '0'
            const response = await fetch('process_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    userId: parseInt(userId, 10),
                    deliveryTime: deliveryTime,
                    paymentMethod: selectedPaymentMethod,
                    items: cart
                })
            })


            const result = await response.json()

            if (response.ok) {
                alert(`¡Pedido realizado con éxito! Número de pedido: ${result.orderId}`)
                cart = []
                updateCart()
                closePaymentModalFunc()
                closeCartPanel()
                closeSideNav()
            } else {
                throw new Error(result.message || 'Error al procesar el pedido')
            }
        } catch (error) {
            console.error('Error:', error)
            alert('Error al procesar el pedido: ' + error.message)
        }
    }

    updateCart()
    
    window.addEventListener('click', function(e) {
        if (cartPanel.classList.contains('active') && !e.target.closest('.cart-panel') && !e.target.closest('.floating-cart')) {
            closeCartPanel()
        }
        
        if (sideNav.classList.contains('active') && !e.target.closest('.side-nav') && !e.target.closest('.menu-icon')) {
            closeSideNav()
        }

        if (e.target === paymentModal) {
            closePaymentModalFunc()
        }
    })
})
