document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const menuIcon = document.querySelector('.menu-icon');
    const sideNav = document.querySelector('.side-nav');
    const closeNav = document.querySelector('.close-nav');
    const overlay = document.querySelector('.overlay');
    const viewComprobanteBtns = document.querySelectorAll('.view-comprobante');
    const receiptModal = document.querySelector('.receipt-modal');
    const closeReceiptBtn = document.querySelector('.close-receipt');
    const searchInput = document.querySelector('.search-input');
    const dateFilter = document.getElementById('date-filter');
    const comprobanteCards = document.querySelectorAll('.comprobante-card');
    const printBtn = document.querySelector('.btn-print');
    const downloadBtn = document.querySelector('.btn-download');

    // Sample receipt data (in a real app, this would come from an API)
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
    };

    // Toggle side navigation
    function toggleSideNav() {
        sideNav.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.style.overflow = sideNav.classList.contains('active') ? 'hidden' : '';
    }

    // Close side navigation
    function closeSideNav() {
        sideNav.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Open receipt modal
    function openReceipt(receiptId) {
        const receipt = receiptsData[receiptId];
        if (!receipt) return;

        // Update modal with receipt data
        document.getElementById('receipt-id').textContent = receipt.id;
        document.getElementById('receipt-date').textContent = receipt.date;
        
        // Update status
        const statusElement = document.querySelector('.status');
        statusElement.className = 'status ' + receipt.status;
        statusElement.textContent = receipt.status === 'completed' ? 'Completado' : 'Pendiente';
        
        // Update items list
        const itemsList = document.querySelector('.items-list');
        itemsList.innerHTML = '';
        
        receipt.items.forEach(item => {
            const itemElement = document.createElement('div');
            itemElement.className = 'item';
            itemElement.innerHTML = `
                <span class="item-name">${item.name}</span>
                <span class="item-quantity">${item.quantity}x</span>
                <span class="item-price">$${(item.price * item.quantity).toFixed(2)}</span>
            `;
            itemsList.appendChild(itemElement);
        });
        
        // Update totals
        document.getElementById('subtotal').textContent = `$${receipt.subtotal.toFixed(2)}`;
        document.getElementById('tax').textContent = `$${receipt.tax.toFixed(2)}`;
        document.getElementById('total').textContent = `$${receipt.total.toFixed(2)}`;
        
        // Show modal
        receiptModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Close receipt modal
    function closeReceipt() {
        receiptModal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Filter comprobantes by search term
    function filterComprobantes(searchTerm) {
        const term = searchTerm.toLowerCase();
        comprobanteCards.forEach(card => {
            const id = card.querySelector('.comprobante-id').textContent.toLowerCase();
            const date = card.querySelector('.comprobante-date').textContent.toLowerCase();
            const total = card.querySelector('.comprobante-total').textContent.toLowerCase();
            
            if (id.includes(term) || date.includes(term) || total.includes(term)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Filter comprobantes by date
    function filterByDate(filterValue) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        comprobanteCards.forEach(card => {
            const dateStr = card.querySelector('.comprobante-date').textContent;
            const [day, month, year] = dateStr.split('/').map(Number);
            const cardDate = new Date(year, month - 1, day);
            
            let show = true;
            
            switch (filterValue) {
                case 'today':
                    show = cardDate.getTime() === today.getTime();
                    break;
                case 'week':
                    const weekAgo = new Date(today);
                    weekAgo.setDate(today.getDate() - 7);
                    show = cardDate >= weekAgo && cardDate <= today;
                    break;
                case 'month':
                    const monthAgo = new Date(today);
                    monthAgo.setMonth(today.getMonth() - 1);
                    show = cardDate >= monthAgo && cardDate <= today;
                    break;
                // 'all' or any other value shows all
            }
            
            card.style.display = show ? '' : 'none';
        });
    }

    // Event Listeners
    menuIcon.addEventListener('click', toggleSideNav);
    closeNav.addEventListener('click', closeSideNav);
    overlay.addEventListener('click', closeSideNav);
    closeReceiptBtn.addEventListener('click', closeReceipt);
    
    // Close modal when clicking outside content
    receiptModal.addEventListener('click', function(e) {
        if (e.target === receiptModal) {
            closeReceipt();
        }
    });

    // Add click event to all view receipt buttons
    viewComprobanteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const receiptId = this.closest('.comprobante-card').dataset.id;
            openReceipt(parseInt(receiptId));
        });
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        filterComprobantes(this.value);
    });

    // Date filter functionality
    dateFilter.addEventListener('change', function() {
        filterByDate(this.value);
    });

    // Print receipt
    printBtn.addEventListener('click', function() {
        window.print();
    });

    // Download receipt as PDF (placeholder - would need a PDF library in a real app)
    downloadBtn.addEventListener('click', function() {
        alert('La funcionalidad de descarga estará disponible pronto.');
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && receiptModal.classList.contains('active')) {
            closeReceipt();
        }
    });
});