<?php
session_start();
require_once 'database.php';

// Verificar si el usuario está logueado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'kiosquero') {
    header("Location: ../login.php");
    exit();
}

// Obtener los pedidos vigentes
$query = "SELECT p.id, p.entrega_pedido, p.estado_pedido, p.estado_pago, u.nombre FROM pedidos p 
          JOIN usuario u ON p.id_usuario = u.id 
          WHERE p.estado_pedido = 'vigente'
          ORDER BY p.entrega_pedido ASC";
$result = $conn->query($query);

// Ya no necesitamos obtener datos de productos aquí porque los hemos movido a dashboard.php

// Si hay un pedido específico seleccionado
$pedido_seleccionado = isset($_GET['id']) ? $_GET['id'] : null;
$detalles_pedido = null;
$usuario_pedido = null;

if ($pedido_seleccionado) {
    // Obtener detalles del pedido
    $query_detalles = "SELECT dp.id, dp.cantidad, dp.precio, p.nombre, p.tipo_producto 
                       FROM detalles_pedidos dp 
                       JOIN productos p ON dp.id_producto = p.id 
                       WHERE dp.id_pedido = ?";
    $stmt = $conn->prepare($query_detalles);
    $stmt->bind_param("i", $pedido_seleccionado);
    $stmt->execute();
    $detalles = $stmt->get_result();
    
    // Obtener información del usuario
    $query_usuario = "SELECT p.id, p.entrega_pedido, u.nombre, u.apellido 
                     FROM pedidos p 
                     JOIN usuario u ON p.id_usuario = u.id 
                     WHERE p.id = ?";
    $stmt = $conn->prepare($query_usuario);
    $stmt->bind_param("i", $pedido_seleccionado);
    $stmt->execute();
    $usuario_pedido = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KiosQuiero</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style_dashboard.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="main-content">
        <h2>Ver pedidos</h2>
        
        <div class="orders-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="order-card" data-id="<?php echo $row['id']; ?>">
                        <h3>Pedido #<?php echo $row['id']; ?></h3>
                        <p class="time"><?php echo date('H:i', strtotime($row['entrega_pedido'])); ?></p>
                        <p class="status"><?php echo ucfirst($row['estado_pedido']); ?></p>
                        <a href="index.php?id=<?php echo $row['id']; ?>" class="btn-view">Ver detalles</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-orders">No hay pedidos pendientes en este momento.</p>
            <?php endif; ?>
        </div>

        <?php if ($pedido_seleccionado && $usuario_pedido): ?>
        <div class="order-details" id="orderDetails" style="display: block;">
            <div class="details-header">
                <h2>DETALLES</h2>
                <a href="index.php" class="close-details" id="closeDetails">&times;</a>
            </div>
            <div class="details-content">
                <h3>Pedido #<?php echo $usuario_pedido['id']; ?></h3>
                <p>Cliente: <span class="customer-name"><?php echo $usuario_pedido['nombre'] . ' ' . $usuario_pedido['apellido']; ?></span></p>
                <p>Hora de entrega: <span class="pickup-time"><?php echo date('H:i', strtotime($usuario_pedido['entrega_pedido'])); ?></span></p>
                
                <?php
                // Agrupar los productos por tipo
                $ensaladas = [];
                $bebidas = [];
                $extras = [];
                $carnes = [];
                
                while ($detalle = $detalles->fetch_assoc()) {
                    switch ($detalle['tipo_producto']) {
                        case 'ensalada':
                            $ensaladas[] = $detalle;
                            break;
                        case 'bebida':
                            $bebidas[] = $detalle;
                            break;
                        case 'extra':
                            $extras[] = $detalle;
                            break;
                        case 'carne':
                            $carnes[] = $detalle;
                            break;
                    }
                }
                ?>
                
                <?php if (!empty($ensaladas)): ?>
                <div class="order-section">
                    <h4>ENSALADAS</h4>
                    <ul class="items-list salad-items">
                        <?php foreach ($ensaladas as $item): ?>
                            <li>
                                <?php echo $item['cantidad']; ?> x <?php echo $item['nombre']; ?> - $<?php echo number_format($item['precio'], 2); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($carnes)): ?>
                <div class="order-section">
                    <h4>CARNES</h4>
                    <ul class="items-list meat-items">
                        <?php foreach ($carnes as $item): ?>
                            <li>
                                <?php echo $item['cantidad']; ?> x <?php echo $item['nombre']; ?> - $<?php echo number_format($item['precio'], 2); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($bebidas)): ?>
                <div class="order-section">
                    <h4>BEBIDAS</h4>
                    <ul class="items-list drink-items">
                        <?php foreach ($bebidas as $item): ?>
                            <li>
                                <?php echo $item['cantidad']; ?> x <?php echo $item['nombre']; ?> - $<?php echo number_format($item['precio'], 2); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($extras)): ?>
                <div class="order-section">
                    <h4>EXTRAS</h4>
                    <ul class="items-list extras-items">
                        <?php foreach ($extras as $item): ?>
                            <li>
                                <?php echo $item['cantidad']; ?> x <?php echo $item['nombre']; ?> - $<?php echo number_format($item['precio'], 2); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <form action="procesar_pedido.php" method="POST">
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido_seleccionado; ?>">
                        <button type="submit" name="accion" value="completar" class="btn-complete">Completar Pedido</button>
                        <button type="submit" name="accion" value="cancelar" class="btn-cancel">Cancelar Pedido</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

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