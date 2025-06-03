<?php
session_start();

// Incluir la configuración de la base de datos
require_once 'revamp/includes/db_config.php';

// Verificar si el usuario ya está registrado (compatible con ambas versiones)
if (isset($_SESSION['usuario_id']) || isset($_SESSION['user_id'])) {
    // Obtener el rol del usuario (compatible con ambas versiones)
    $rol = $_SESSION['rol'] ?? $_SESSION['user_role'] ?? '';
    
    // Redirigir según el rol del usuario
    if ($rol === 'usuario') {
        header("Location: usuario/index.php");
    } elseif ($rol === 'kiosquero') {
        header("Location: kiosquero/index.php"); // Corregido el nombre del directorio
    } elseif ($rol === 'admin') {
        header("Location: administrador.php");
    }
    exit();
}

// Procesar el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexión a la base de datos ya establecida a través de db_config.php
    // $pdo está disponible desde el require_once anterior
    
    // Obtener datos del formulario
    $nombre = trim($_POST['name']);
    $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Contraseña en texto plano
    $confirm_password = $_POST['confirm_password'];
    $rol = isset($_POST['rol']) ? trim($_POST['rol']) : 'usuario'; // Por defecto, rol usuario
    
    // Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden. Por favor, inténtalo de nuevo.";
        $_SESSION['flash_message'] = "Las contraseñas no coinciden. Por favor, inténtalo de nuevo.";
        $_SESSION['flash_message_type'] = "error";
    } else {
        // Verificar si el correo ya existe
        $check_email = $pdo->prepare("SELECT mail FROM usuario WHERE mail = ?");
        $check_email->execute([$email]);
        
        if ($check_email->rowCount() > 0) {
            $error = "Este correo electrónico ya está registrado. Por favor, utiliza otro.";
            $_SESSION['flash_message'] = "Este correo electrónico ya está registrado. Por favor, utiliza otro.";
            $_SESSION['flash_message_type'] = "error";
        } else {
            // Insertar nuevo usuario
            $stmt = $pdo->prepare("INSERT INTO usuario (nombre, apellido, mail, contraseña, rol, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$nombre, $apellido, $email, $password, $rol]);
        
            if ($result) {
                // Registro exitoso
                $_SESSION['flash_message'] = "Registro exitoso. Ahora puedes iniciar sesión.";
                $_SESSION['flash_message_type'] = "success";
                
                // Redirigir al login
                header("Location: login.php?registro=exitoso");
                exit();
            } else {
                $error = "Error al registrar el usuario.";
                $_SESSION['flash_message'] = "Error al registrar el usuario.";
                $_SESSION['flash_message_type'] = "error";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - KiosQuiero</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <form action="register.php" method="post">
        <h2>Crear Cuenta</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        
        <label for="confirm_password">Confirmar Contraseña:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        
        <label for="rol">Rol:</label>
        <select id="rol" name="rol" required>
            <option value="usuario">Usuario</option>
            <option value="kiosquero">Kiosquero</option>
            <option value="admin">Administrador</option>
        </select>

        <button type="submit">Registrarse</button>
        
        <p class="login-link">¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
    </form>
</body>
</html>