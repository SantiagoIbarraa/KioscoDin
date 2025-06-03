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

// Verificar si se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexión a la base de datos ya establecida a través de db_config.php
    // $pdo está disponible desde el require_once anterior

    // Obtener los datos del formulario
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $password = $_POST['password'];

    // Validar que el usuario exista y no sea admin (para evitar cambios no deseados)
    $query_check = "SELECT rol FROM usuario WHERE id = ?";
    $stmt_check = $pdo->prepare($query_check);
    $stmt_check->execute([$id]);
    
    if ($stmt_check->rowCount() === 0) {
        $_SESSION['mensaje'] = "El usuario no existe.";
        $_SESSION['tipo_mensaje'] = "error";
        $_SESSION['flash_message'] = "El usuario no existe.";
        $_SESSION['flash_message_type'] = "error";
        
        // Redirigir a la página especificada o a administrador.php por defecto
        $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'administrador.php';
        header("Location: $redirect_url");
        exit();
    }
    
    $user_data = $stmt_check->fetch(PDO::FETCH_ASSOC);
    if ($user_data['rol'] === 'admin') {
        $_SESSION['mensaje'] = "No se pueden modificar usuarios administradores.";
        $_SESSION['tipo_mensaje'] = "error";
        $_SESSION['flash_message'] = "No se pueden modificar usuarios administradores.";
        $_SESSION['flash_message_type'] = "error";
        
        // Redirigir a la página especificada o a administrador.php por defecto
        $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'administrador.php';
        header("Location: $redirect_url");
        exit();
    }

    // Preparar la consulta SQL según si se cambió la contraseña o no
    if (!empty($password)) {
        // En la versión actual, las contraseñas se almacenan en texto plano
        // No aplicamos hash para mantener compatibilidad con el sistema actual
        
        $query = "UPDATE usuario SET nombre = ?, apellido = ?, mail = ?, rol = ?, contraseña = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([$nombre, $apellido, $email, $rol, $password, $id]);
    } else {
        // Actualizar sin cambiar la contraseña
        $query = "UPDATE usuario SET nombre = ?, apellido = ?, mail = ?, rol = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([$nombre, $apellido, $email, $rol, $id]);
    }

    // Verificar si la consulta se ejecutó correctamente
    if ($result) {
        $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
        $_SESSION['flash_message'] = "Usuario actualizado correctamente.";
        $_SESSION['flash_message_type'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar el usuario.";
        $_SESSION['tipo_mensaje'] = "error";
        $_SESSION['flash_message'] = "Error al actualizar el usuario.";
        $_SESSION['flash_message_type'] = "error";
    }

    // Redireccionar de vuelta a la página especificada o a administrador.php por defecto
    $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'administrador.php';
    header("Location: $redirect_url");
    exit();
} else {
    // Si se intenta acceder directamente sin enviar el formulario
    $_SESSION['mensaje'] = "Acceso incorrecto al formulario.";
    $_SESSION['tipo_mensaje'] = "error";
    $_SESSION['flash_message'] = "Acceso incorrecto al formulario.";
    $_SESSION['flash_message_type'] = "error";
    header("Location: administrador.php");
    exit();
}
?>
