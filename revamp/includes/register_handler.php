<?php
session_start();
require_once 'db_config.php';

$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/kioscoDin/revamp';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        header("Location: $base_url/pages/register/?error=Las+contraseñas+no+coinciden");
        exit();
    }
    
    try {
        // Check if email already exists
        $sql = "SELECT id FROM usuario WHERE mail = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            header("Location: $base_url/pages/register/?error=El+correo+ya+está+registrado");
            exit();
        }
        
        $hashed_password = $password;
        
        $sql = "INSERT INTO usuario (nombre, apellido, contraseña, mail, rol) VALUES (:nombre, :apellido, :password, :email, 'usuario')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        // Auto-login after registration
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_role'] = 'usuario';
        
        header("Location: $base_url/pages/index/");
        exit();
        
    } catch(PDOException $e) {
        header("Location: $base_url/pages/register/?error=Error+de+base+de+datos");
        exit();
    }
} else {
    header("Location: $base_url/pages/register/");
    exit();
}
?>
