<?php
session_start();

// Verificar si el usuario está logueado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'kiosquero') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KiosQuiero</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="burger-menu" id="burgerMenu">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <h1>KIOSCO SALUDABLE</h1>
    </header>

    <nav class="side-menu" id="sideMenu">
        <h3>Menú</h3>
        <ul>
            <li><a href="index.html">Pedidos</a></li>
            <li><a href="reviews.html">Reseñas</a></li>
            <li><a href="stock.html">Stock</a></li>
            <li><a href="cuenta.html">Mi Cuenta</a></li>
            <li><a href="historial.html">Historial de Pedidos</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <h2>Ver pedidos</h2>
        
        <div class="orders-container">
            <div class="order-card" data-id="1">
                <h3>Pedido #1</h3>
                <p class="time">10:30</p>
            </div>
            
            <div class="order-card" data-id="2">
                <h3>Pedido #2</h3>
                <p class="time">11:15</p>
            </div>
            
            <div class="order-card" data-id="3">
                <h3>Pedido #3</h3>
                <p class="time">11:45</p>
            </div>
            
            <div class="order-card" data-id="4">
                <h3>Pedido #4</h3>
                <p class="time">11:45</p>
            </div>
        </div>

        <div class="order-details" id="orderDetails">
            <div class="details-header">
                <h2>DETALLES</h2>
                <button class="close-details" id="closeDetails">&times;</button>
            </div>
            <div class="details-content">
                <h3>Pedido <span class="order-id"></span></h3>
                <p>Entregar a: <span class="customer-name"></span></p>
                <p>Hora de retiro: <span class="pickup-time"></span></p>
                
                <div class="order-section">
                    <h4>ENSALADA</h4>
                    <ul class="items-list salad-items"></ul>
                </div>
                
                <div class="order-section">
                    <h4>BEBIDA</h4>
                    <ul class="items-list drink-items"></ul>
                </div>
                
                <div class="order-section">
                    <h4>NOTA</h4>
                    <ul class="items-list notes-items"></ul>
                </div>

                <div class="action-buttons">
                    <button class="btn-complete">Terminado</button>
                    <button class="btn-cancel">Cancelar pedido</button>
                </div>
            </div>
        </div>

        <div class="cancel-modal" id="cancelModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Cancelar Pedido</h2>
                    <button class="close-modal" id="closeCancelModal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Por favor, indique el motivo de la cancelación:</p>
                    <textarea id="cancelReason" rows="4" placeholder="Escriba el motivo aquí..."></textarea>
                    <div class="modal-buttons">
                        <button class="btn-confirm-cancel">Confirmar</button>
                        <button class="btn-back">Volver</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
<script src="script.js"></script>
</body>
</html>