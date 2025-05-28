document.addEventListener('DOMContentLoaded', function() {
    const menuIcon = document.querySelector('.menu-icon')
    const sideNav = document.querySelector('.side-nav')
    const closeNav = document.querySelector('.close-nav')
    const overlay = document.querySelector('.overlay')
    const viewComprobanteBtns = document.querySelectorAll('.view-comprobante')
    const receiptModal = document.querySelector('.receipt-modal')
    const closeReceiptBtn = document.querySelector('.close-receipt')
    const searchInput = document.querySelector('.search-input')
    const dateFilter = document.getElementById('date-filter')
    const comprobanteCards = document.querySelectorAll('.comprobante-card')
    const printBtn = document.querySelector('.btn-print')
    const downloadBtn = document.querySelector('.btn-download')

    const receiptsData = {
        1: {
            id: '0001',
            date: '27/05/2025 14:30',
            status: 'completed',
            items: [
                { name: 'Café Americano', quantity: 2, price: 2.50 },
                { name: 'Sandwich de Pollo', quantity: 1, price: 6.75 }
            ],
            subtotal: 11.75,
            tax: 1.18,
            total: 12.93
        },
        2: {
            id: '0002',
            date: '26/05/2025 11:15',
            status: 'completed',
            items: [
                { name: 'Cappuccino', quantity: 1, price: 3.50 },
                { name: 'Ensalada César', quantity: 1, price: 8.25 }
            ],
            subtotal: 11.75,
            tax: 1.18,
            total: 12.93
        },
        3: {
            id: '0003',
            date: '25/05/2025 16:45',
            status: 'completed',
            items: [
                { name: 'Pastel de Chocolate', quantity: 2, price: 5.50 },
                { name: 'Papas Fritas', quantity: 1, price: 3.75 },
                { name: 'Agua Mineral', quantity: 2, price: 2.00 }
            ],
            subtotal: 18.75,
            tax: 1.88,
            total: 20.63
        }
    }

    function toggleSideNav() {
        sideNav.classList.toggle('active')
        overlay.classList.toggle('active')
        document.body.style.overflow = sideNav.classList.contains('active') ? 'hidden' : ''
    }

    function closeSideNav() {
        sideNav.classList.remove('active')
        overlay.classList.remove('active')
        document.body.style.overflow = ''
    }

    function openReceipt(receiptId) {
        const receipt = receiptsData[receiptId]
        if (!receipt) return

        document.getElementById('receipt-id').textContent = receipt.id
        document.getElementById('receipt-date').textContent = receipt.date
        
        const statusElement = document.querySelector('.status')
        statusElement.className = 'status ' + receipt.status
        statusElement.textContent = receipt.status === 'completed' ? 'Completado' : 'Pendiente'
        
        const itemsList = document.querySelector('.items-list')
        itemsList.innerHTML = ''
        
        receipt.items.forEach(item => {
            const itemElement = document.createElement('div')
            itemElement.className = 'item'
            itemElement.innerHTML = `
                <span class="item-name">${item.name}</span>
                <span class="item-quantity">${item.quantity}x</span>
                <span class="item-price">$${(item.price * item.quantity).toFixed(2)}</span>
            `
            itemsList.appendChild(itemElement)
        })
        
        document.getElementById('subtotal').textContent = `$${receipt.subtotal.toFixed(2)}`
        document.getElementById('tax').textContent = `$${receipt.tax.toFixed(2)}`
        document.getElementById('total').textContent = `$${receipt.total.toFixed(2)}`
        
        receiptModal.classList.add('active')
        document.body.style.overflow = 'hidden'
    }

    function closeReceipt() {
        receiptModal.classList.remove('active')
        document.body.style.overflow = ''
    }

    function filterComprobantes(searchTerm) {
        const term = searchTerm.toLowerCase()
        comprobanteCards.forEach(card => {
            const id = card.querySelector('.comprobante-id').textContent.toLowerCase()
            const date = card.querySelector('.comprobante-date').textContent.toLowerCase()
            const total = card.querySelector('.comprobante-total').textContent.toLowerCase()
            
            if (id.includes(term) || date.includes(term) || total.includes(term)) {
                card.style.display = ''
            } else {
                card.style.display = 'none'
            }
        })
    }

    function filterByDate(filterValue) {
        const today = new Date()
        today.setHours(0, 0, 0, 0)
        
        comprobanteCards.forEach(card => {
            const dateStr = card.querySelector('.comprobante-date').textContent
            const [day, month, year] = dateStr.split('/').map(Number)
            const cardDate = new Date(year, month - 1, day)
            
            let show = true
            
            switch (filterValue) {
                case 'today':
                    show = cardDate.getTime() === today.getTime()
                    break
                case 'week':
                    const weekAgo = new Date(today)
                    weekAgo.setDate(today.getDate() - 7)
                    show = cardDate >= weekAgo && cardDate <= today
                    break
                case 'month':
                    const monthAgo = new Date(today)
                    monthAgo.setMonth(today.getMonth() - 1)
                    show = cardDate >= monthAgo && cardDate <= today
                    break
            }
            
            card.style.display = show ? '' : 'none'
        })
    }

    menuIcon.addEventListener('click', toggleSideNav)
    closeNav.addEventListener('click', closeSideNav)
    overlay.addEventListener('click', closeSideNav)
    closeReceiptBtn.addEventListener('click', closeReceipt)
    
    receiptModal.addEventListener('click', function(e) {
        if (e.target === receiptModal) {
            closeReceipt()
        }
    })

    viewComprobanteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const receiptId = this.closest('.comprobante-card').dataset.id
            openReceipt(parseInt(receiptId))
        })
    })

    searchInput.addEventListener('input', function() {
        filterComprobantes(this.value)
    })

    dateFilter.addEventListener('change', function() {
        filterByDate(this.value)
    })

    printBtn.addEventListener('click', function() {
        window.print()
    })

    downloadBtn.addEventListener('click', function() {
        alert('Descargando comprobante...')
    })

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && receiptModal.classList.contains('active')) {
            closeReceipt()
        }
    })
});