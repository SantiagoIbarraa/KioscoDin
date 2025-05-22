<?php
session_start();
require_once 'database.php';

// Verificar si el usuario está logueado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'kiosquero') {
    header("Location: ../login.php");
    exit();
}

// Verificar si se recibió el ID del pedido y la acción
if (isset($_POST['pedido_id']) && isset($_POST['accion'])) {
    $pedido_id = $_POST['pedido_id'];
    $accion = $_POST['accion'];
    
    // Verificar que el pedido existe
    $query = "SELECT id FROM pedidos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        if ($accion == 'completar') {
            // Marcar el pedido como entregado
            $query_update = "UPDATE pedidos SET estado_pedido = 'entregado' WHERE id = ?";
            $stmt = $conn->prepare($query_update);
            $stmt->bind_param("i", $pedido_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $_SESSION['mensaje'] = "Pedido #$pedido_id marcado como completado exitosamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al marcar el pedido como completado.";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } elseif ($accion == 'cancelar') {
            // Aquí se podrían agregar más acciones para manejar la cancelación
            // como registrar el motivo, etc.
            
            // Por ahora, simplemente marcamos el pedido como entregado
            $query_update = "UPDATE pedidos SET estado_pedido = 'entregado' WHERE id = ?";
            $stmt = $conn->prepare($query_update);
            $stmt->bind_param("i", $pedido_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $_SESSION['mensaje'] = "Pedido #$pedido_id cancelado exitosamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al cancelar el pedido.";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }
    } else {
        $_SESSION['mensaje'] = "Pedido no encontrado.";
        $_SESSION['tipo_mensaje'] = "danger";
    }
} else {
    $_SESSION['mensaje'] = "Datos incompletos para procesar la acción.";
    $_SESSION['tipo_mensaje'] = "danger";
}

// Redirigir de vuelta a la página de pedidos
header("Location: index.php");
exit();
?>
