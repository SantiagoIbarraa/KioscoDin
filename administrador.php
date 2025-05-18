<?php
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "kiosquiero");

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener todos los usuarios
$query = "SELECT nombre, mail, rol FROM sesion ORDER BY rol, nombre";
$result = $conn->query($query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - KiosQuiero</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap');
        
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #fbc02d;
            color: #111;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 {
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 15px;
        }
        
        .logout-btn {
            background-color: #111;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .panel {
            display: flex;
            margin-top: 20px;
        }
        
        .sidebar {
            width: 250px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar h3 {
            margin-top: 0;
            border-bottom: 2px solid #fbc02d;
            padding-bottom: 10px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        
        .sidebar li {
            margin-bottom: 10px;
        }
        
        .sidebar a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background-color: #fff9c4;
        }
        
        .content {
            flex-grow: 1;
            margin-left: 20px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #fff9c4;
            color: #111;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .role-admin {
            background-color: #ffeb3b;
            color: #111;
        }
        
        .role-kiosquero {
            background-color: #4caf50;
            color: white;
        }
        
        .role-usuario {
            background-color: #2196f3;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <h1>Panel de Administración</h1>
        <div class="user-info">
            <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="logout.php" class="logout-btn">Cerrar sesión</a>
        </div>
    </header>
    
    <div class="container">
        <div class="panel">
            <div class="sidebar">
                <h3>Menú</h3>
                <ul>
                    <li><a href="#" class="active">Usuarios</a></li>
                    <li><a href="#">Estadísticas</a></li>
                    <li><a href="#">Configuración</a></li>
                </ul>
            </div>
            
            <div class="content">
                <h2>Gestión de Usuarios</h2>
                <p>Aquí puedes ver todos los usuarios registrados en el sistema.</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($row['mail']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $row['rol']; ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['rol'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No hay usuarios registrados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
