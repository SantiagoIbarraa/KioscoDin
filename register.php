<?php

session_start(); 

// Verificar si el usuario ya está registrado
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

// Procesar el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexión a la base de datos
    $conn = new mysqli("localhost", "root", "", "kiosquiero");
    
    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Obtener datos del formulario
    $nombre = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']); // Contraseña en texto plano
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);
    $rol = $conn->real_escape_string($_POST['rol']);
    
    // Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden. Por favor, inténtalo de nuevo.";
    } else {
    
    // Verificar si el correo ya existe
    $check_email = $conn->prepare("SELECT mail FROM sesion WHERE mail = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Este correo electrónico ya está registrado. Por favor, utiliza otro.";
    } else {
        // Insertar nuevo usuario
        $stmt = $conn->prepare("INSERT INTO sesion (nombre, mail, contraseña, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $password, $rol);
        
        if ($stmt->execute()) {
            // Iniciar sesión
            $_SESSION['usuario_id'] = $email;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['rol'] = $rol;
            
            // Redirigir según el rol
            if ($rol === 'usuario') {
                header("Location: usuario/index.php");
            } elseif ($rol === 'kiosquero') {
                header("Location: kiosquiero/index.php");
            } elseif ($rol === 'admin') {
                header("Location: administrador.php");
            }
            exit();
        } else {
            $error = "Error al registrar: " . $stmt->error;
        }
    }
    }
    
    $conn->close();
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