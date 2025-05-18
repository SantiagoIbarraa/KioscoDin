<?php
session_start();

// Verificar si el usuario ya está logueado
if (isset($_SESSION['usuario_id'])) {
    // Redirigir según el rol del usuario
    if ($_SESSION['rol'] === 'usuario') {
        header("Location: usuario/index.php");
    } elseif ($_SESSION['rol'] === 'kiosquero') {
        header("Location: kiosquiero/index.php");
    } elseif ($_SESSION['rol'] === 'admin') {
        header("Location: administrador.php");
    }
    exit();
}

// Procesar el formulario de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexión a la base de datos
    $conn = new mysqli("localhost", "root", "", "kiosquiero");
    
    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Obtener datos del formulario
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Buscar usuario en la base de datos
    $stmt = $conn->prepare("SELECT nombre, mail, contraseña, rol FROM sesion WHERE mail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verificar contraseña en texto plano
        if ($password === $user['contraseña']) {
            // Iniciar sesión
            $_SESSION['usuario_id'] = $user['mail'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];
            
            // Redirigir según el rol
            if ($user['rol'] === 'usuario') {
                header("Location: usuario/index.php");
            } elseif ($user['rol'] === 'kiosquero') {
                header("Location: kiosquiero/index.php");
            } elseif ($user['rol'] === 'admin') {
                header("Location: administrador.php");
            }
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }
    
    $conn->close();
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
