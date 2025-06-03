<?php
session_start();

// Incluir la configuración de la base de datos
require_once 'revamp/includes/db_config.php';

// Verificar si el usuario está logueado y es administrador
// Compatibilidad con ambas versiones de variables de sesión
if ((!isset($_SESSION['usuario_id']) && !isset($_SESSION['user_id'])) || 
    ($_SESSION['rol'] !== 'admin' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

// Verificar si se recibió un ID de usuario
if (!isset($_POST['id']) || empty($_POST['id'])) {
    $_SESSION['mensaje'] = "ID de usuario no válido.";
    $_SESSION['tipo_mensaje'] = "error";
    $_SESSION['flash_message'] = "ID de usuario no válido.";
    $_SESSION['flash_message_type'] = "error";
    
    // Redirigir a la página especificada o a administrador.php por defecto
    $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'administrador.php';
    header("Location: $redirect_url");
    exit();
}

$id_usuario = (int)$_POST['id'];

// Conexión a la base de datos ya establecida a través de db_config.php
// $pdo está disponible desde el require_once anterior

// Verificar que no se esté intentando eliminar un administrador
$query_check = "SELECT rol FROM usuario WHERE id = ?";
$stmt_check = $pdo->prepare($query_check);
$stmt_check->execute([$id_usuario]);

if ($stmt_check->rowCount() > 0) {
    $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    // No permitir eliminar administradores
    if ($row['rol'] === 'admin') {
        $_SESSION['mensaje'] = "No se pueden eliminar cuentas de administradores.";
        $_SESSION['tipo_mensaje'] = "error";
        $_SESSION['flash_message'] = "No se pueden eliminar cuentas de administradores.";
        $_SESSION['flash_message_type'] = "error";
        
        // Redirigir a la página especificada o a administrador.php por defecto
        $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'administrador.php';
        header("Location: $redirect_url");
        exit();
    }
    
    // Eliminar el usuario
    $query_delete = "DELETE FROM usuario WHERE id = ?";
    $stmt_delete = $pdo->prepare($query_delete);
    
    if ($stmt_delete->execute([$id_usuario])) {
        $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
        $_SESSION['flash_message'] = "Usuario eliminado correctamente.";
        $_SESSION['flash_message_type'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el usuario.";
        $_SESSION['tipo_mensaje'] = "error";
        $_SESSION['flash_message'] = "Error al eliminar el usuario.";
        $_SESSION['flash_message_type'] = "error";
    }
} else {
    $_SESSION['mensaje'] = "Usuario no encontrado.";
    $_SESSION['tipo_mensaje'] = "error";
    $_SESSION['flash_message'] = "Usuario no encontrado.";
    $_SESSION['flash_message_type'] = "error";
}

// Redirigir a la página especificada o a administrador.php por defecto
$redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'administrador.php';
header("Location: $redirect_url");
exit();
?>
