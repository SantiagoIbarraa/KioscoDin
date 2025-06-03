<?php
session_start();

// Guardar un mensaje flash antes de destruir la sesión
$flash_message = "Sesión cerrada correctamente";
$flash_type = "success";

// Destruir todas las variables de sesión
$_SESSION = array();

// Establecer mensaje flash para la siguiente página
$_SESSION['flash_message'] = $flash_message;
$_SESSION['flash_message_type'] = $flash_type;
$_SESSION['mensaje'] = $flash_message; // Para compatibilidad con versión original
$_SESSION['tipo_mensaje'] = $flash_type; // Para compatibilidad con versión original

// Si se desea destruir la sesión completamente, borrar también la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión (pero mantener el mensaje flash)
session_write_close();

// Redirigir al login
header("Location: login.php");
exit();
?>
