<?php
session_start();

// Verificar si el usuario está logueado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: ../login.php");
    exit();
}

// Incluir conexión a la base de datos
require_once 'db_connect.php';

// Obtener productos por tipo
function getProductsByType($conn, $tipo) {
    $sql = "SELECT * FROM productos WHERE tipo_producto = ? AND stock > 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tipo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos = [];
    while($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    return $productos;
}

// Obtener productos por tipo
$ensaladas = getProductsByType($conn, 'ensalada');
$carnes = getProductsByType($conn, 'carne');
$bebidas = getProductsByType($conn, 'bebida');
$extras = getProductsByType($conn, 'extra');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosco Saludable</title>
    <?php include 'ui.php'; renderStylesheetLinks(); ?>
</head>
<body>
    <?php renderHeader(); ?>
    
    <div class="main-content">
        <h2 class="section-title">Ensaladas Preparadas</h2>
        <div class="category-container">
            <?php if (count($ensaladas) > 0): ?>
                <?php foreach ($ensaladas as $ensalada): ?>
                    <div class="category" data-price="<?php echo $ensalada['precio']; ?>">
                        <div class="category-image">
                            <img src="https://placehold.co/600x400" alt="<?php echo htmlspecialchars($ensalada['nombre']); ?>">
                        </div>
                        <div class="category-info">
                            <h3><?php echo htmlspecialchars($ensalada['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($ensalada['descripcion']); ?></p>
                            <p class="precio">$<?php echo number_format($ensalada['precio'], 2, ',', '.'); ?></p>
                            <a href="producto.php?id=<?php echo $ensalada['id']; ?>" class="go-btn">Elegir »</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay ensaladas disponibles en este momento.</p>
            <?php endif; ?>
        </div>
        
        <?php if (count($carnes) > 0): ?>
        <h2 class="section-title">Carnes</h2>
        <div class="category-container">
            <?php foreach ($carnes as $carne): ?>
                <div class="category" data-price="<?php echo $carne['precio']; ?>">
                    <div class="category-image">
                        <img src="https://placehold.co/600x400" alt="<?php echo htmlspecialchars($carne['nombre']); ?>">
                    </div>
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($carne['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($carne['descripcion']); ?></p>
                        <p class="precio">$<?php echo number_format($carne['precio'], 2, ',', '.'); ?></p>
                        <a href="producto.php?id=<?php echo $carne['id']; ?>" class="go-btn">Elegir »</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (count($bebidas) > 0): ?>
        <h2 class="section-title">Bebidas Saludables</h2>
        <div class="category-container">
            <?php foreach ($bebidas as $bebida): ?>
                <div class="category" data-price="<?php echo $bebida['precio']; ?>">
                    <div class="category-image">
                        <img src="https://placehold.co/600x400" alt="<?php echo htmlspecialchars($bebida['nombre']); ?>">
                    </div>
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($bebida['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($bebida['descripcion']); ?></p>
                        <p class="precio">$<?php echo number_format($bebida['precio'], 2, ',', '.'); ?></p>
                        <a href="producto.php?id=<?php echo $bebida['id']; ?>" class="go-btn">Elegir »</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (count($extras) > 0): ?>
        <h2 class="section-title">Extras Saludables</h2>
        <div class="category-container">
            <?php foreach ($extras as $extra): ?>
                <div class="category" data-price="<?php echo $extra['precio']; ?>">
                    <div class="category-image">
                        <img src="https://placehold.co/600x400" alt="<?php echo htmlspecialchars($extra['nombre']); ?>">
                    </div>
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($extra['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($extra['descripcion']); ?></p>
                        <p class="precio">$<?php echo number_format($extra['precio'], 2, ',', '.'); ?></p>
                        <a href="producto.php?id=<?php echo $extra['id']; ?>" class="go-btn">Elegir »</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php renderCartIcon(); ?>
        
        <?php renderNavbar(); ?>
    </div>
    
    <?php renderCartModal(); ?>

    <!-- Confirm Order Modal -->
    <dialog id="confirmOrderModal">
        <form method="dialog" id="confirmOrderForm">
            <h2>Confirmar Pedido</h2>
            <div id="confirmOrderItems"></div>
            <div class="modal-section">
                <label for="orderNote">Nota para el pedido:</label>
                <textarea id="orderNote" name="orderNote" rows="2" maxlength="200" placeholder="Agrega una nota..."></textarea>
            </div>
            <div class="modal-section">
                <label>Método de pago:</label>
                <div class="payment-methods">
                    <button type="button" class="payment-btn" data-method="Efectivo">Efectivo</button>
                    <button type="button" class="payment-btn" data-method="Tarjeta">Tarjeta</button>
                    <button type="button" class="payment-btn" data-method="MercadoPago">MercadoPago</button>
                </div>
            </div>
            <div class="modal-actions">
                <button id="payBtn" type="button" class="modal-pay">Pagar</button>
                <button id="cancelOrderBtn" type="button" class="modal-cancel">Cancelar</button>
            </div>
        </form>
    </dialog>
    <script src="script.js"></script>
</body>
</html>