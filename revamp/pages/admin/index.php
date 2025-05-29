<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /revamp/pages/login/');
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
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #4a6bff;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .welcome {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 2rem;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .card p {
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="welcome">Panel de Administración</div>
            <a href="<?php echo $base_url; ?>/includes/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </a>
        </div>
    </header>
    
    <div class="container">
        <div class="dashboard">
            <div class="card">
                <h3>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                <p>Has iniciado sesión como <strong>Administrador</strong>.</p>
                <p>Desde aquí puedes gestionar todos los aspectos del sistema.</p>
            </div>
            <div class="card">
                <h3>Usuarios</h3>
                <p>Gestiona los usuarios del sistema, sus roles y permisos.</p>
            </div>
            <div class="card">
                <h3>Configuración</h3>
                <p>Configura los parámetros generales del sistema.</p>
            </div>
        </div>
    </div>
</body>
</html>
