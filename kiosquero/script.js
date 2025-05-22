document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.getElementById('burgerMenu');
    const sideMenu = document.getElementById('sideMenu');
    const orderCards = document.querySelectorAll('.order-card');
    const orderDetails = document.getElementById('orderDetails');
    const closeDetails = document.getElementById('closeDetails');
    const cancelModal = document.getElementById('cancelModal');
    const closeCancelModal = document.getElementById('closeCancelModal');
    let menuOpen = false;
    let currentOrderId = null;
    
    // Verificar si estos elementos existen antes de agregar event listeners
    const btnCancel = document.querySelector('.btn-cancel');
    const btnBack = document.querySelector('.btn-back');
    const btnConfirmCancel = document.querySelector('.btn-confirm-cancel');

    // Ya no necesitamos los datos estáticos porque los datos vienen de PHP

    burgerMenu.addEventListener('click', function(e) {
        e.stopPropagation();
        this.classList.toggle('active');
        sideMenu.classList.toggle('active');
        menuOpen = !menuOpen;
    });

    document.addEventListener('click', function(e) {
        if (menuOpen && !sideMenu.contains(e.target) && e.target !== burgerMenu) {
            burgerMenu.classList.remove('active');
            sideMenu.classList.remove('active');
            menuOpen = false;
        }
    });

    // El manejo de tarjetas de pedidos ahora se hace con enlaces en PHP

    // El enlace closeDetails ahora es un enlace a index.php

    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            if (cancelModal) cancelModal.classList.add('active');
        });
    }

    if (btnBack) {
        btnBack.addEventListener('click', function() {
            if (cancelModal) cancelModal.classList.remove('active');
        });
    }

    if (closeCancelModal) {
        closeCancelModal.addEventListener('click', function() {
            if (cancelModal) cancelModal.classList.remove('active');
        });
    }

    if (btnConfirmCancel) {
        btnConfirmCancel.addEventListener('click', function() {
            const reason = document.getElementById('cancelReason').value;
            if (!reason.trim()) {
                alert('Por favor, ingrese un motivo para la cancelación');
                return;
            }
            
            // Ahora usamos el formulario para enviar la cancelación
            document.getElementById('cancelReason').value = '';
        });
    }

    // La función showOrderDetails ya no es necesaria porque los detalles se muestran con PHP

    // El botón complete ahora usa un formulario PHP

    // La funcionalidad para cerrar modales al hacer clic fuera ya no es necesaria
    // porque los detalles se muestran y ocultan con PHP
});
