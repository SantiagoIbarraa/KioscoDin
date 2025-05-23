<?php
// UI Components for Usuario

function renderHeader() {
    echo <<<HTML
    <div class="header">
        <div class="burger-menu" id="burgerMenu">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <h1>KIOSCO SALUDABLE</h1>
    </div>
HTML;
}

function renderNavbar() {
    echo <<<HTML
    <div class="side-menu" id="sideMenu">
        <h3>Menú</h3>
        <ul>
            <li><a href="#">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l2-2m0 0l7-7 7 7m-9 2v6a2 2 0 002 2h4a2 2 0 002-2v-6m-6 0h6"/></svg>
                Inicio
            </a></li>
            <li><a href="#">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a8.38 8.38 0 0113 0"/></svg>
                Mi Cuenta
            </a></li>
            <li><a href="#">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Historial de Pedidos
            </a></li>
        </ul>
    </div>
HTML;
}

function renderCartIcon() {
    echo <<<HTML
    <div class="cart-icon" id="cartIcon">
        <div class="cart-counter" id="cartCounter">0</div>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
        </svg>
    </div>
HTML;
}

function renderCartModal() {
    echo <<<HTML
    <div class="cart-modal" id="cartModal">
        <div class="cart-content">
            <div class="cart-header">
                <h2 class="cart-title">PEDIDO</h2>
                <button class="close-cart" id="closeCart">&times;</button>
            </div>
            <div class="cart-items" id="cartItems">
                <!-- Cart items will be added here dynamically -->
            </div>
            <div class="cart-total">
                <span>Total:</span>
                <span id="cartTotal">$0.00</span>
            </div>
            <button class="cart-checkout">Finalizar Pedido</button>
        </div>
    </div>
HTML;
}

function renderStylesheetLinks() {
    echo <<<HTML
    <link rel="stylesheet" href="style.css">
HTML;
}
