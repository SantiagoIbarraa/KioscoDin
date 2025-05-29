<?php
session_start();
require_once 'db_config.php';

$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/kioscoDin/revamp';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $sql = "SELECT id, nombre, contraseña, rol FROM usuario WHERE mail = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch();
            if ($password === $row['contraseña']) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['nombre'];
                $_SESSION['user_role'] = $row['rol'];
                
                // Redirect based on role
                switch($row['rol']) {
                    case 'admin':
                        header("Location: $base_url/pages/admin/");
                        break;
                    case 'kiosquero':
                        header("Location: $base_url/pages/kiosquero/");
                        break;
                    default:
                        header("Location: $base_url/pages/index/");
                }
                exit();
            } else {
                header("Location: $base_url/pages/login/?error=Credenciales+inválidas");
                exit();
            }
        } else {
            header("Location: $base_url/pages/login/?error=Usuario+no+encontrado");
            exit();
        }
    } catch(PDOException $e) {
        header("Location: $base_url/pages/login/?error=Error+de+base+de+datos");
        exit();
    }
} else {
    header("Location: $base_url/pages/login/");
    exit();
}
?>
