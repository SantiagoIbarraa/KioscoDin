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
                <svg style="transform: translateY(0.6vh);" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M5 9.77746V16.2C5 17.8802 5 18.7203 5.32698 19.362C5.6146 19.9265 6.07354 20.3854 6.63803 20.673C7.27976 21 8.11984 21 9.8 21H14.2C15.8802 21 16.7202 21 17.362 20.673C17.9265 20.3854 18.3854 19.9265 18.673 19.362C19 18.7203 19 17.8802 19 16.2V5.00002M21 12L15.5668 5.96399C14.3311 4.59122 13.7133 3.90484 12.9856 3.65144C12.3466 3.42888 11.651 3.42893 11.0119 3.65159C10.2843 3.90509 9.66661 4.59157 8.43114 5.96452L3 12M14 21V15H10V21" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>                Inicio
            </a></li>
            <li><a href="#">
            <svg style="transform: translateY(0.6vh);" width="20" height="20" viewBox="0 0 24 24" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><defs><style>.cls-1{fill:none;stroke:#020202;stroke-miterlimit:10;stroke-width:1.91px;}</style></defs><circle class="cls-1" cx="12" cy="7.25" r="5.73"></circle><path class="cls-1" d="M1.5,23.48l.37-2.05A10.3,10.3,0,0,1,12,13h0a10.3,10.3,0,0,1,10.13,8.45l.37,2.05"></path></g></svg>
            Mi Cuenta
            </a></li>
            <li><a href="#">
                <svg style="transform: translateY(0.6vh);" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
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
