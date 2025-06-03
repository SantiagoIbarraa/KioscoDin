<?php
session_start();

// Base URL para redirecciones
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/kioscoDin/revamp';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Verificar si existen las variables de sesión antiguas
    if (isset($_SESSION['usuario_id']) && $_SESSION['rol'] === 'admin') {
        // Migrar variables de sesión al nuevo formato
        $_SESSION['user_id'] = $_SESSION['usuario_id'];
        $_SESSION['user_name'] = $_SESSION['nombre'];
        $_SESSION['user_role'] = $_SESSION['rol'];
    } else {
        header("Location: $base_url/pages/login/");
        exit();
    }
}

// Incluir configuración de base de datos
require_once __DIR__ . '/revamp/includes/db_config.php';

// Usar PDO para la conexión
try {
    // Conexión a la base de datos usando PDO (ya configurado en db_config.php)
    // $pdo ya está disponible desde db_config.php
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener todos los usuarios con fecha de creación
try {
    $query = "SELECT id, nombre, apellido, mail, rol, fecha_creacion FROM usuario ORDER BY id";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estadísticas para el panel
    $query_total_usuarios = "SELECT COUNT(*) as total FROM usuario";
    $stmt_usuarios = $pdo->query($query_total_usuarios);
    $total_usuarios = $stmt_usuarios->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query_total_productos = "SELECT COUNT(*) as total FROM productos";
    $stmt_productos = $pdo->query($query_total_productos);
    $total_productos = $stmt_productos->fetch(PDO::FETCH_ASSOC)['total'];
    
    $query_total_pedidos = "SELECT COUNT(*) as total FROM pedidos";
    $stmt_pedidos = $pdo->query($query_total_pedidos);
    $total_pedidos = $stmt_pedidos->fetch(PDO::FETCH_ASSOC)['total'];
} catch(PDOException $e) {
    $error_message = "Error en la base de datos: " . $e->getMessage();
    $total_usuarios = 0;
    $total_productos = 0;
    $total_pedidos = 0;
}

// Obtener información de usuarios con sus pedidos
$usuarios_con_pedidos = [];
if (count($result) > 0) {
    foreach($result as $row) {
        // Consultar los pedidos de cada usuario
        try {
            $query_pedidos = "SELECT id, entrega_pedido as fecha, estado_pedido as estado, 
                             (SELECT SUM(dp.precio * dp.cantidad) 
                              FROM detalles_pedidos dp 
                              WHERE dp.id_pedido = pedidos.id) as total 
                             FROM pedidos 
                             WHERE id_usuario = :usuario_id 
                             ORDER BY entrega_pedido DESC";
            $stmt_pedidos = $pdo->prepare($query_pedidos);
            $stmt_pedidos->bindParam(':usuario_id', $row['id'], PDO::PARAM_INT);
            $stmt_pedidos->execute();
            $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar los pedidos al usuario
            $row['pedidos'] = $pedidos;
        } catch(PDOException $e) {
            // Si hay un error, simplemente asignamos un array vacío
            $row['pedidos'] = [];
            // Opcionalmente, podemos registrar el error
            error_log("Error al obtener pedidos del usuario {$row['id']}: " . $e->getMessage());
        }
        
        // Asegurarse de que la fecha de creación tenga un formato válido o valor por defecto
        if (!isset($row['fecha_creacion']) || empty($row['fecha_creacion'])) {
            $row['fecha_creacion'] = 'No disponible';
        }
        
        $usuarios_con_pedidos[] = $row;
    }
}

// Verificar si hay mensajes de sesión
$mensaje = '';
$tipo_mensaje = '';
if (isset($_SESSION['mensaje']) && isset($_SESSION['tipo_mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'];
    // Limpiar los mensajes después de mostrarlos
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Kiosquero</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 5px;
            font-size: 0.9rem;
            border: none;
        }
        
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        
        .delete-btn:hover {
            background-color: #d32f2f;
        }
        
        .edit-btn {
            background-color: #4caf50;
            color: white;
        }
        
        .edit-btn:hover {
            background-color: #388e3c;
        }
        
        .view-btn {
            background-color: #2196f3;
            color: white;
        }
        
        .view-btn:hover {
            background-color: #1976d2;
        }
        
        .user-details-cell {
            padding: 0;
            background-color: #f9f9f9;
        }
        
        .user-details {
            padding: 15px;
        }
        
        .inner-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .inner-table th, .inner-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .inner-table th {
            background-color: #f0f0f0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .status-pendiente {
            background-color: #ffeb3b;
            color: #333;
        }
        
        .status-completado {
            background-color: #4caf50;
            color: white;
        }
        
        .status-cancelado {
            background-color: #f44336;
            color: white;
        }
        
        .user-edit-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .user-selector {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        
        .alert-error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        
        .alert-warning {
            background-color: #fcf8e3;
            color: #8a6d3b;
            border: 1px solid #faebcc;
        }
        
        .stats-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-right: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card:last-child {
            margin-right: 0;
        }
        
        .stat-card i {
            font-size: 2rem;
            color: #fbc02d;
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            font-size: 1.8rem;
            margin: 5px 0;
        }
        
        .stat-card p {
            color: #666;
            margin: 0;
        }
        
        .config-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .btn-primary {
            background-color: #fbc02d;
            color: #111;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #f9a825;
        }
        
        /* Modal de confirmación */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .modal-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }
        
        .btn-secondary {
            background-color: #ccc;
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn-danger {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 1.5rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <h1>Panel de Administración</h1>
        <div class="user-info">
            <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['nombre'] ?? 'Administrador'); ?></span>
            <a href="logout.php" class="logout-btn">Cerrar sesión</a>
        </div>
    </header>
    
    <div class="container">
        <div class="panel">
            <div class="sidebar">
                <h3>Menú</h3>
                <ul>
                    <li><a href="#" class="active" id="menu-usuarios">Usuarios</a></li>
                    <li><a href="#" id="menu-editar-usuario">Editar Usuario</a></li>
                    <li><a href="#" id="menu-estadisticas">Estadísticas</a></li>
                    <li><a href="#" id="menu-configuracion">Configuración</a></li>
                </ul>
            </div>
            
            <div class="content">
                <!-- Mensajes de alerta -->
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Sección de Usuarios -->
                <div id="seccion-usuarios" class="seccion">
                    <h2>Gestión de Usuarios</h2>
                    <p>Aquí puedes ver y administrar todos los usuarios registrados en el sistema.</p>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($usuarios_con_pedidos)): ?>
                                <?php foreach($usuarios_con_pedidos as $usuario): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['mail']); ?></td>
                                        <td>
                                            <span class="role-badge role-<?php echo $usuario['rol']; ?>">
                                                <?php echo ucfirst(htmlspecialchars($usuario['rol'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            if (isset($usuario['fecha_creacion']) && !empty($usuario['fecha_creacion'])) {
                                                echo date('d/m/Y', strtotime($usuario['fecha_creacion']));
                                            } else {
                                                echo 'No disponible';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button class="action-btn view-btn" onclick="verDetallesUsuario(<?php echo $usuario['id']; ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <button class="action-btn edit-btn" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Detalles del usuario (historial de pedidos) -->
                                    <tr id="detalles-usuario-<?php echo $usuario['id']; ?>" style="display: none;">
                                        <td colspan="5" class="user-details-cell">
                                            <div class="user-details">
                                                <h4>Historial de Pedidos</h4>
                                                <?php if (!empty($usuario['pedidos'])): ?>
                                                    <table class="inner-table">
                                                        <thead>
                                                            <tr>
                                                                <th>ID Pedido</th>
                                                                <th>Fecha</th>
                                                                <th>Estado</th>
                                                                <th>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach($usuario['pedidos'] as $pedido): ?>
                                                                <tr>
                                                                    <td>#<?php echo $pedido['id']; ?></td>
                                                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></td>
                                                                    <td>
                                                                        <span class="status-badge status-<?php echo strtolower($pedido['estado']); ?>">
                                                                            <?php echo ucfirst(htmlspecialchars($pedido['estado'])); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>$<?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                <?php else: ?>
                                                    <p>Este usuario no tiene pedidos registrados.</p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No hay usuarios registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Sección de Edición de Usuario -->
                <div id="seccion-editar-usuario" class="seccion" style="display: none;">
                    <h2>Edición de Usuario</h2>
                    <p>Aquí puedes editar la información de los usuarios o eliminarlos del sistema.</p>
                    
                    <div class="user-edit-container">
                        <div class="user-selector">
                            <h3>Seleccionar Usuario</h3>
                            <div class="form-group">
                                <label for="usuario-selector">Usuario:</label>
                                <select id="usuario-selector" class="form-control">
                                    <option value="">-- Seleccionar usuario --</option>
                                    <?php foreach($usuarios_con_pedidos as $usuario): ?>
                                        <?php if ($usuario['rol'] !== 'admin'): ?>
                                            <option value="<?php echo $usuario['id']; ?>">
                                                <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido'] . ' (' . $usuario['mail'] . ')'); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div id="formulario-edicion" style="display: none;">
                            <h3>Editar Información</h3>
                            <form id="form-editar-usuario" action="actualizar_usuario.php" method="post" class="config-form">
                                <input type="hidden" id="edit_id" name="id" value="">
                                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                <div class="form-group">
                                    <label for="edit_nombre">Nombre:</label>
                                    <input type="text" id="edit_nombre" name="nombre" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_apellido">Apellido:</label>
                                    <input type="text" id="edit_apellido" name="apellido" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_email">Email:</label>
                                    <input type="email" id="edit_email" name="email" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_rol">Rol:</label>
                                    <select id="edit_rol" name="rol" class="form-control">
                                        <option value="usuario">Usuario</option>
                                        <option value="kiosquero">Kiosquero</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="edit_password">Nueva Contraseña (dejar en blanco para mantener la actual):</label>
                                    <input type="password" id="edit_password" name="password" class="form-control">
                                </div>
                                
                                <button type="submit" class="btn-primary">Guardar Cambios</button>
                            </form>
                            <div class="form-group" style="margin-top: 20px;">
                                <button class="btn-danger" onclick="confirmarEliminar(document.getElementById('edit_id').value, document.getElementById('edit_nombre').value + ' ' + document.getElementById('edit_apellido').value)">Eliminar Usuario</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección de Estadísticas -->
                <div id="seccion-estadisticas" class="seccion" style="display: none;">
                    <h2>Estadísticas del Sistema</h2>
                    <p>Aquí puedes ver las estadísticas generales del sistema.</p>
                    
                    <div class="stats-cards">
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <h3><?php echo $total_usuarios; ?></h3>
                            <p>Usuarios Registrados</p>
                        </div>
                        
                        <div class="stat-card">
                            <i class="fas fa-shopping-basket"></i>
                            <h3><?php echo $total_productos; ?></h3>
                            <p>Productos en Catálogo</p>
                        </div>
                        
                        <div class="stat-card">
                            <i class="fas fa-receipt"></i>
                            <h3><?php echo $total_pedidos; ?></h3>
                            <p>Pedidos Totales</p>
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <h3>Distribución de Usuarios por Rol</h3>
                        <div style="background-color: #f5f5f5; padding: 20px; border-radius: 8px;">
                            <p style="text-align: center; color: #666;">Gráfico de distribución de usuarios por rol</p>
                            <!-- Aquí se podría implementar un gráfico real con una librería como Chart.js -->
                            <div style="display: flex; height: 200px; align-items: flex-end; justify-content: center; margin-top: 20px;">
                                <div style="width: 100px; background-color: #2196f3; margin: 0 10px; position: relative;">
                                    <div style="position: absolute; bottom: 100%; width: 100%; text-align: center; padding: 5px 0;">Usuarios</div>
                                </div>
                                <div style="width: 100px; background-color: #4caf50; margin: 0 10px; position: relative;">
                                    <div style="position: absolute; bottom: 100%; width: 100%; text-align: center; padding: 5px 0;">Kiosqueros</div>
                                </div>
                                <div style="width: 100px; background-color: #ffeb3b; margin: 0 10px; position: relative;">
                                    <div style="position: absolute; bottom: 100%; width: 100%; text-align: center; padding: 5px 0;">Admins</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección de Configuración -->
                <div id="seccion-configuracion" class="seccion" style="display: none;">
                    <h2>Configuración del Sistema</h2>
                    <p>Aquí puedes ajustar la configuración general del sistema.</p>
                    
                    <div class="config-form">
                        <h3>Configuración General</h3>
                        <form action="#" method="post">
                            <div class="form-group">
                                <label for="nombre_sitio">Nombre del Sitio:</label>
                                <input type="text" id="nombre_sitio" name="nombre_sitio" class="form-control" value="Kiosquero" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email_contacto">Email de Contacto:</label>
                                <input type="email" id="email_contacto" name="email_contacto" class="form-control" value="contacto@kiosquero.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="items_por_pagina">Ítems por Página:</label>
                                <select id="items_por_pagina" name="items_por_pagina" class="form-control">
                                    <option value="10">10</option>
                                    <option value="20" selected>20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Opciones de Visualización:</label>
                                <div>
                                    <input type="checkbox" id="mostrar_stock" name="mostrar_stock" checked>
                                    <label for="mostrar_stock">Mostrar stock en catálogo público</label>
                                </div>
                                <div>
                                    <input type="checkbox" id="mostrar_precios" name="mostrar_precios" checked>
                                    <label for="mostrar_precios">Mostrar precios sin iniciar sesión</label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-primary">Guardar Configuración</button>
                        </form>
                    </div>
                    
                    <div class="config-form" style="margin-top: 30px;">
                        <h3>Mantenimiento del Sistema</h3>
                        <p>Opciones para el mantenimiento y respaldo del sistema.</p>
                        
                        <div style="display: flex; gap: 15px; margin-top: 20px;">
                            <button class="btn-primary"><i class="fas fa-database"></i> Crear Respaldo</button>
                            <button class="btn-primary"><i class="fas fa-broom"></i> Limpiar Caché</button>
                            <button class="btn-primary"><i class="fas fa-history"></i> Ver Registros</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de confirmación para eliminar usuario -->
    <div id="modalConfirmacion" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <div class="modal-header">
                <h3>Confirmar eliminación</h3>
            </div>
            <p>¿Estás seguro que deseas eliminar al usuario <strong id="nombreUsuario"></strong>?</p>
            <p>Esta acción no se puede deshacer.</p>
            <div class="modal-footer">
                <form id="formEliminar" action="eliminar_usuario.php" method="post">
                    <input type="hidden" id="usuario_id" name="id" value="">
                    <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Funciones para manejar el modal de confirmación
        function confirmarEliminar(id, nombre) {
            document.getElementById('usuario_id').value = id;
            document.getElementById('nombreUsuario').textContent = nombre;
            document.getElementById('modalConfirmacion').style.display = 'block';
        }
        
        function cerrarModal() {
            document.getElementById('modalConfirmacion').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            var modal = document.getElementById('modalConfirmacion');
            if (event.target == modal) {
                cerrarModal();
            }
        }
        
        // Funciones para cambiar entre secciones
        document.addEventListener('DOMContentLoaded', function() {
            const menuUsuarios = document.getElementById('menu-usuarios');
            const menuEditarUsuario = document.getElementById('menu-editar-usuario');
            const menuEstadisticas = document.getElementById('menu-estadisticas');
            const menuConfiguracion = document.getElementById('menu-configuracion');
            
            const seccionUsuarios = document.getElementById('seccion-usuarios');
            const seccionEditarUsuario = document.getElementById('seccion-editar-usuario');
            const seccionEstadisticas = document.getElementById('seccion-estadisticas');
            const seccionConfiguracion = document.getElementById('seccion-configuracion');
            
            // Función para mostrar una sección y ocultar las demás
            function mostrarSeccion(seccion) {
                seccionUsuarios.style.display = 'none';
                seccionEditarUsuario.style.display = 'none';
                seccionEstadisticas.style.display = 'none';
                seccionConfiguracion.style.display = 'none';
                
                menuUsuarios.classList.remove('active');
                menuEditarUsuario.classList.remove('active');
                menuEstadisticas.classList.remove('active');
                menuConfiguracion.classList.remove('active');
                
                if (seccion === 'usuarios') {
                    seccionUsuarios.style.display = 'block';
                    menuUsuarios.classList.add('active');
                } else if (seccion === 'editar-usuario') {
                    seccionEditarUsuario.style.display = 'block';
                    menuEditarUsuario.classList.add('active');
                } else if (seccion === 'estadisticas') {
                    seccionEstadisticas.style.display = 'block';
                    menuEstadisticas.classList.add('active');
                } else if (seccion === 'configuracion') {
                    seccionConfiguracion.style.display = 'block';
                    menuConfiguracion.classList.add('active');
                }
            }
            
            // Asignar eventos a los elementos del menú
            menuUsuarios.addEventListener('click', function(e) {
                e.preventDefault();
                mostrarSeccion('usuarios');
            });
            
            menuEditarUsuario.addEventListener('click', function(e) {
                e.preventDefault();
                mostrarSeccion('editar-usuario');
            });
            
            menuEstadisticas.addEventListener('click', function(e) {
                e.preventDefault();
                mostrarSeccion('estadisticas');
            });
            
            menuConfiguracion.addEventListener('click', function(e) {
                e.preventDefault();
                mostrarSeccion('configuracion');
            });
            
            // Funcionalidad para ver detalles de usuario (historial de pedidos)
            window.verDetallesUsuario = function(userId) {
                const detallesRow = document.getElementById('detalles-usuario-' + userId);
                if (detallesRow.style.display === 'none' || detallesRow.style.display === '') {
                    detallesRow.style.display = 'table-row';
                } else {
                    detallesRow.style.display = 'none';
                }
            };
            
            // Funcionalidad para editar usuario
            window.editarUsuario = function(userId) {
                mostrarSeccion('editar-usuario');
                document.getElementById('usuario-selector').value = userId;
                cargarDatosUsuario(userId);
            };
            
            // Cargar datos de usuario seleccionado
            document.getElementById('usuario-selector').addEventListener('change', function() {
                const userId = this.value;
                if (userId) {
                    cargarDatosUsuario(userId);
                } else {
                    document.getElementById('formulario-edicion').style.display = 'none';
                }
            });
            
            function cargarDatosUsuario(userId) {
                // Buscar el usuario en los datos ya cargados
                <?php echo 'const usuarios = ' . json_encode($usuarios_con_pedidos) . ';'; ?>
                const usuario = usuarios.find(u => u.id == userId);
                
                if (usuario) {
                    document.getElementById('edit-usuario-id').value = usuario.id;
                    document.getElementById('edit-nombre').value = usuario.nombre;
                    document.getElementById('edit-apellido').value = usuario.apellido;
                    document.getElementById('edit-email').value = usuario.mail;
                    document.getElementById('edit-rol').value = usuario.rol;
                    document.getElementById('formulario-edicion').style.display = 'block';
                    
                    // Actualizar el botón de eliminar con los datos correctos
                    const btnEliminar = document.querySelector('#formulario-edicion .btn-danger');
                    btnEliminar.onclick = function() {
                        confirmarEliminar(usuario.id, usuario.nombre + ' ' + usuario.apellido);
                    };
                }
            }
        });
    </script>
</body>
</html>
