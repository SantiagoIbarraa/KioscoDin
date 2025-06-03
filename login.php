<?php
session_start();

// Incluir la configuración de la base de datos
require_once 'revamp/includes/db_config.php';

// Verificar si el usuario ya está logueado (compatible con ambas versiones)
if (isset($_SESSION['usuario_id']) || isset($_SESSION['user_id'])) {
    // Obtener el rol del usuario (compatible con ambas versiones)
    $rol = $_SESSION['rol'] ?? $_SESSION['user_role'] ?? '';
    
    // Redirigir según el rol del usuario
    if ($rol === 'usuario') {
        header("Location: usuario/index.php");
    } elseif ($rol === 'kiosquero') {
        header("Location: kiosquero/index.php");
    } elseif ($rol === 'admin') {
        header("Location: administrador.php");
    }
    exit();
}

// Mostrar mensaje flash si existe
$flash_message = '';
$flash_type = '';

if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_message_type'])) {
    $flash_message = $_SESSION['flash_message'];
    $flash_type = $_SESSION['flash_message_type'];
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_message_type']);
} elseif (isset($_SESSION['mensaje']) && isset($_SESSION['tipo_mensaje'])) {
    $flash_message = $_SESSION['mensaje'];
    $flash_type = $_SESSION['tipo_mensaje'];
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Procesar el formulario de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexión a la base de datos ya establecida a través de db_config.php
    // $pdo está disponible desde el require_once anterior
    
    // Obtener datos del formulario
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Buscar usuario en la base de datos
    $stmt = $pdo->prepare("SELECT id, nombre, apellido, mail, contraseña, rol FROM usuario WHERE mail = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar contraseña (sólo texto plano por ahora)
        $password_verified = false;
        
        // Verificar contraseña en texto plano (versión original)
        if (isset($user['contraseña']) && $password === $user['contraseña']) {
            $password_verified = true;
        }
        
        if ($password_verified) {
            // Iniciar sesión (compatible con ambas versiones)
            // Variables de sesión para la versión original
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];
            
            // Variables de sesión para la versión revamp
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_role'] = $user['rol'];
            
            // Mensaje de éxito
            $_SESSION['flash_message'] = "Bienvenido/a, {$user['nombre']}!";
            $_SESSION['flash_message_type'] = "success";
            $_SESSION['mensaje'] = "Bienvenido/a, {$user['nombre']}!";
            $_SESSION['tipo_mensaje'] = "success";
            
            // Redirigir según el rol
            if ($user['rol'] === 'usuario') {
                header("Location: usuario/index.php");
            } elseif ($user['rol'] === 'kiosquero') {
                header("Location: kiosquero/index.php");
            } elseif ($user['rol'] === 'admin') {
                header("Location: administrador.php");
            }
            exit();
        } else {
            $error = "Credenciales inválidas. Por favor, inténtalo de nuevo.";
            $_SESSION['flash_message'] = "Credenciales inválidas. Por favor, inténtalo de nuevo.";
            $_SESSION['flash_message_type'] = "error";
        }
    } else {
        $error = "Usuario no encontrado. Por favor, verifica tu correo electrónico.";
        $_SESSION['flash_message'] = "Usuario no encontrado. Por favor, verifica tu correo electrónico.";
        $_SESSION['flash_message_type'] = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - KiosQuiero</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <form action="login.php" method="post">
        <h2>Iniciar Sesión</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Iniciar Sesión</button>
        
        <p class="register-link">¿No tienes una cuenta? <a href="register.php">Registrarse</a></p>
    </form>
</body>
</html>
