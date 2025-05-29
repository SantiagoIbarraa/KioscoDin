<?php
session_start();
require_once 'db_connect.php';

function getProductCategories($conn) {
    $sql = "SELECT REPLACE(REPLACE(REPLACE(SUBSTRING(COLUMN_TYPE, 6, LENGTH(COLUMN_TYPE) - 6), ')' , ''), '\'', ''), ',', ', ') AS enum_values 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'productos' 
            AND COLUMN_NAME = 'tipo_producto'";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $enum_values = $row['enum_values'];
    
    $categories = array_map('trim', explode(',', $enum_values));
    
    return $categories;
}

function getProductsByCategory($conn, $category) {
    $sql = "SELECT * FROM productos WHERE tipo_producto = ? AND stock > 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

$categories = getProductCategories($conn);
$category_products = [];

foreach ($categories as $category) {
    $category_products[$category] = getProductsByCategory($conn, $category);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Kiosco Saludable</title>
    <script src="script.js" defer></script>
</head>
<body>
    <header>
        <div class="menu-icon">
            <i class="fas fa-bars"></i>
        </div>
        <h1>Kiosco Saludable</h1>
        <div class="user-menu">
            <a href="/kioscoDin/revamp/includes/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </header>
    
    <nav class="side-nav">
        <div class="nav-header">
            <h3>Menú</h3>
            <span class="close-nav">&times;</span>
        </div>
        <ul class="nav-links">
            <li><a href="../index/index.php"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="../historial/index.html"><i class="fas fa-history"></i> Historial</a></li>
            <li><a href="../comprobantes/index.html"><i class="fas fa-receipt"></i>Comprobantes</a></li>
        </ul>
    </nav>
    
    <div class="overlay"></div>
    
    <div class="floating-cart">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count">0</span>
    </div>
    
    <div class="cart-panel">
        <div class="cart-header">
            <h3>Tu Carrito</h3>
            <button class="close-cart">&times;</button>
        </div>
        <div class="cart-items">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Tu carrito está vacío</p>
            </div>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span class="total-amount">0.00$</span>
            </div>
            <button class="checkout-btn">Pagar Ahora</button>
        </div>
    </div>

    <div class="search-container">
        <input type="text" placeholder="Buscar productos..." class="search-input">
        <button class="search-button"><i class="fas fa-search"></i></button>
    </div>

    <div class="categories">
        <div class="category-buttons">
            <button class="category-btn active" data-category="all">Todos</button>
            <?php foreach ($categories as $category): ?>
                <button class="category-btn" data-category="<?php echo $category; ?>"><?php echo ucfirst($category); ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="products-grid">
        <?php 
        $has_products = false;
        foreach ($category_products as $products) { 
            if (!empty($products)) {
                $has_products = true;
                foreach ($products as $product) { ?>
                    <div class="product-card" data-id="<?php echo $product['id']; ?>">
                        <img src="https://placehold.co/600x400" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['nombre']); ?></h3>
                            <p class="price"><?php echo number_format($product['precio'], 2, '.', ','); ?>$</p>
                            <p class="description"><?php echo htmlspecialchars($product['descripcion']); ?></p>
                            <div class="quantity-control">
                                <button class="quantity-btn minus">−</button>
                                <span class="quantity">1</span>
                                <button class="quantity-btn plus">+</button>
                            </div>
                            <button class="add-to-cart" 
                                    data-id="<?php echo $product['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($product['nombre']); ?>" 
                                    data-category="<?php echo htmlspecialchars($product['tipo_producto']); ?>"
                                    data-price="<?php echo $product['precio']; ?>">
                                <i class="fas fa-shopping-cart"></i> Añadir al Carrito
                            </button>
                        </div>
                    </div>
                <?php 
                }
            }
        } 
        if (!$has_products) { ?>
            <p>No hay productos disponibles en este momento.</p>
        <?php } ?>
    </div>
    <div id="overlay"></div>
    
    <!-- Payment Modal -->
    <div class="payment-modal" id="paymentModal">
        <div class="payment-modal-content">
            <div class="payment-modal-header">
                <h3>Confirmar Pedido</h3>
                <span class="close-payment-modal">&times;</span>
            </div>
            <div class="payment-modal-body">
                <div class="order-summary">
                    <h4>Resumen del Pedido</h4>
                    <div id="orderItems">
                        <!-- Order items will be inserted here by JavaScript -->
                    </div>
                    <div class="order-total">
                        <strong>Total: </strong>
                        <span id="orderTotal">0.00$</span>
                    </div>
                </div>
                <div class="payment-options">
                    <h4>Método de Pago</h4>
                    <div class="payment-methods">
                        <div class="payment-method" data-method="efectivo">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Efectivo</span>
                        </div>
                        <div class="payment-method" data-method="tarjeta">
                            <i class="fas fa-credit-card"></i>
                            <span>Tarjeta</span>
                        </div>
                        <div class="payment-method" data-method="transferencia">
                            <i class="fas fa-university"></i>
                            <span>Transferencia</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="payment-modal-footer">
                <button class="btn btn-secondary" id="cancelPayment">Cancelar</button>
                <button class="btn btn-primary" id="confirmPayment">Confirmar Pago</button>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="script.js"></script>
</body>
</html>