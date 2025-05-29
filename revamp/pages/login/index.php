<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /revamp/pages/index/');
    exit();
}
?>
<?php
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/kioscoDin/revamp';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/auth.css">
    <title>Iniciar Sesión - Kiosco</title>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Iniciar Sesión</h2>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            <form action="<?php echo $base_url; ?>/includes/login_handler.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                    </div>
                </div>
                <button type="submit" class="auth-button">Iniciar Sesión</button>
            </form>
            <div class="auth-footer">
                ¿No tienes una cuenta? <a href="<?php echo $base_url; ?>/pages/register/">Regístrate aquí</a>
            </div>
        </div>
    </div>
    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const icon = event.currentTarget;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>