<?php
session_start();
require_once 'database.php';

// Verificar si el usuario está logueado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'kiosquero') {
    header("Location: ../login.php");
    exit();
}

// Procesar formulario de agregar/editar producto
if (isset($_POST['accion'])) {
    if ($_POST['accion'] == 'agregar') {
        // Validar datos
        if (!empty($_POST['nombre']) && !empty($_POST['descripcion']) && isset($_POST['stock']) && 
            !empty($_POST['tipo_producto']) && isset($_POST['precio'])) {
            
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $stock = (int)$_POST['stock'];
            $tipo_producto = $_POST['tipo_producto'];
            $precio = (float)$_POST['precio'];
            $url = isset($_POST['url']) ? $_POST['url'] : '';
            
            // Procesar la imagen si se ha subido una
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $imagen_nombre = $_FILES['imagen']['name'];
                $imagen_temp = $_FILES['imagen']['tmp_name'];
                $imagen_extension = strtolower(pathinfo($imagen_nombre, PATHINFO_EXTENSION));
                
                // Verificar que sea una imagen válida
                $extensiones_permitidas = array('jpg', 'jpeg', 'png', 'gif');
                
                if (in_array($imagen_extension, $extensiones_permitidas)) {
                    // Crear directorio de imágenes si no existe
                    $directorio_destino = '../images/productos/';
                    if (!file_exists($directorio_destino)) {
                        mkdir($directorio_destino, 0777, true);
                    }
                    
                    // Generar un nombre único para la imagen
                    $imagen_nombre_nuevo = 'producto_' . time() . '_' . rand(1000, 9999) . '.' . $imagen_extension;
                    $ruta_destino = $directorio_destino . $imagen_nombre_nuevo;
                    
                    // Mover la imagen al directorio de destino
                    if (move_uploaded_file($imagen_temp, $ruta_destino)) {
                        // Actualizar la URL en la base de datos
                        $url = 'images/productos/' . $imagen_nombre_nuevo;
                    } else {
                        $_SESSION['mensaje'] = "Error al subir la imagen. Por favor, inténtelo de nuevo.";
                        $_SESSION['tipo_mensaje'] = "danger";
                        header("Location: products.php");
                        exit();
                    }
                } else {
                    $_SESSION['mensaje'] = "Formato de imagen no válido. Por favor, use JPG, PNG o GIF.";
                    $_SESSION['tipo_mensaje'] = "warning";
                    header("Location: products.php");
                    exit();
                }
            }
            
            // Insertar nuevo producto
            $query = "INSERT INTO productos (nombre, descripcion, stock, tipo_producto, precio, url) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssisss", $nombre, $descripcion, $stock, $tipo_producto, $precio, $url);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Producto agregado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al agregar el producto: " . $conn->error;
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } else {
            $_SESSION['mensaje'] = "Por favor complete todos los campos obligatorios.";
            $_SESSION['tipo_mensaje'] = "warning";
        }
    } 
    elseif ($_POST['accion'] == 'editar') {
        if (!empty($_POST['id']) && !empty($_POST['nombre']) && !empty($_POST['descripcion']) && 
            isset($_POST['stock']) && !empty($_POST['tipo_producto']) && isset($_POST['precio'])) {
            
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $stock = (int)$_POST['stock'];
            $tipo_producto = $_POST['tipo_producto'];
            $precio = (float)$_POST['precio'];
            $url = isset($_POST['url']) ? $_POST['url'] : '';
            
            // Procesar la imagen si se ha subido una
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $imagen_nombre = $_FILES['imagen']['name'];
                $imagen_temp = $_FILES['imagen']['tmp_name'];
                $imagen_extension = strtolower(pathinfo($imagen_nombre, PATHINFO_EXTENSION));
                
                // Verificar que sea una imagen válida
                $extensiones_permitidas = array('jpg', 'jpeg', 'png', 'gif');
                
                if (in_array($imagen_extension, $extensiones_permitidas)) {
                    // Crear directorio de imágenes si no existe
                    $directorio_destino = '../images/productos/';
                    if (!file_exists($directorio_destino)) {
                        mkdir($directorio_destino, 0777, true);
                    }
                    
                    // Generar un nombre único para la imagen
                    $imagen_nombre_nuevo = 'producto_' . time() . '_' . rand(1000, 9999) . '.' . $imagen_extension;
                    $ruta_destino = $directorio_destino . $imagen_nombre_nuevo;
                    
                    // Mover la imagen al directorio de destino
                    if (move_uploaded_file($imagen_temp, $ruta_destino)) {
                        // Actualizar la URL en la base de datos
                        $url = 'images/productos/' . $imagen_nombre_nuevo;
                    } else {
                        $_SESSION['mensaje'] = "Error al subir la imagen. Por favor, inténtelo de nuevo.";
                        $_SESSION['tipo_mensaje'] = "danger";
                        header("Location: products.php");
                        exit();
                    }
                } else {
                    $_SESSION['mensaje'] = "Formato de imagen no válido. Por favor, use JPG, PNG o GIF.";
                    $_SESSION['tipo_mensaje'] = "warning";
                    header("Location: products.php");
                    exit();
                }
            }
            
            // Actualizar producto
            $query = "UPDATE productos 
                      SET nombre = ?, descripcion = ?, stock = ?, tipo_producto = ?, precio = ?, url = ? 
                      WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssisssi", $nombre, $descripcion, $stock, $tipo_producto, $precio, $url, $id);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Producto actualizado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar el producto: " . $conn->error;
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } else {
            $_SESSION['mensaje'] = "Por favor complete todos los campos obligatorios.";
            $_SESSION['tipo_mensaje'] = "warning";
        }
    }
    
    // Redirigir para evitar reenvío de formulario
    header("Location: products.php");
    exit();
}

// Procesar eliminación de producto
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    // Verificar que el producto existe
    $query = "SELECT id FROM productos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Verificar si el producto está en algún pedido
        $query_check = "SELECT COUNT(*) as count FROM detalles_pedidos WHERE id_producto = ?";
        $stmt = $conn->prepare($query_check);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'];
        
        if ($count > 0) {
            $_SESSION['mensaje'] = "No se puede eliminar el producto porque está asociado a pedidos existentes.";
            $_SESSION['tipo_mensaje'] = "warning";
        } else {
            // Eliminar el producto
            $query_delete = "DELETE FROM productos WHERE id = ?";
            $stmt = $conn->prepare($query_delete);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Producto eliminado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar el producto: " . $conn->error;
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }
    } else {
        $_SESSION['mensaje'] = "Producto no encontrado.";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    
    // Redirigir
    header("Location: products.php");
    exit();
}

// Obtener producto para editar
$producto_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    
    $query = "SELECT * FROM productos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto_editar = $stmt->get_result()->fetch_assoc();
    
    if (!$producto_editar) {
        $_SESSION['mensaje'] = "Producto no encontrado.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: products.php");
        exit();
    }
}

// Obtener lista de productos para mostrar
$query = "SELECT * FROM productos ORDER BY nombre ASC";
$productos = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - KiosQuiero</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .product-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-cancel {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .products-table th, .products-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .products-table th {
            background-color: #4CAF50;
            color: white;
        }
        
        .products-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .btn-edit, .btn-delete, .btn-view {
            padding: 6px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-edit {
            background-color: #2196F3;
            color: white;
        }
        
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        
        .btn-view {
            background-color: #ff9800;
            color: white;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        
        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
        }
        
        .alert-warning {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

    <main class="main-content">
        <h2><?php echo $producto_editar ? 'Editar Producto' : 'Gestión de Productos'; ?></h2>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?>">
                <?php 
                    echo $_SESSION['mensaje']; 
                    unset($_SESSION['mensaje']);
                    unset($_SESSION['tipo_mensaje']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="product-form">
            <h3><?php echo $producto_editar ? 'Editar Producto: ' . htmlspecialchars($producto_editar['nombre']) : 'Agregar Nuevo Producto'; ?></h3>
            <form action="products.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="<?php echo $producto_editar ? 'editar' : 'agregar'; ?>">
                <?php if ($producto_editar): ?>
                    <input type="hidden" name="id" value="<?php echo $producto_editar['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="nombre">Nombre del Producto:</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo $producto_editar ? htmlspecialchars($producto_editar['nombre']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3" required><?php echo $producto_editar ? htmlspecialchars($producto_editar['descripcion']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" min="0" required 
                           value="<?php echo $producto_editar ? $producto_editar['stock'] : '0'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="tipo_producto">Tipo de Producto:</label>
                    <select id="tipo_producto" name="tipo_producto" required>
                        <option value="">Seleccione un tipo</option>
                        <option value="ensalada" <?php echo ($producto_editar && $producto_editar['tipo_producto'] == 'ensalada') ? 'selected' : ''; ?>>Ensalada</option>
                        <option value="carne" <?php echo ($producto_editar && $producto_editar['tipo_producto'] == 'carne') ? 'selected' : ''; ?>>Carne</option>
                        <option value="bebida" <?php echo ($producto_editar && $producto_editar['tipo_producto'] == 'bebida') ? 'selected' : ''; ?>>Bebida</option>
                        <option value="extra" <?php echo ($producto_editar && $producto_editar['tipo_producto'] == 'extra') ? 'selected' : ''; ?>>Extra</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="precio">Precio:</label>
                    <input type="number" id="precio" name="precio" min="0" step="0.01" required 
                           value="<?php echo $producto_editar ? $producto_editar['precio'] : '0.00'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="imagen">Imagen del producto:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <small class="form-text">Seleccione una imagen para el producto (JPG, PNG o GIF)</small>
                    <?php if ($producto_editar && !empty($producto_editar['url'])): ?>
                        <div class="imagen-preview">
                            <p>Imagen actual:</p>
                            <img src="../<?php echo htmlspecialchars($producto_editar['url']); ?>" alt="<?php echo htmlspecialchars($producto_editar['nombre']); ?>" style="max-width: 200px; max-height: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="hidden" id="url" name="url" value="<?php echo $producto_editar ? htmlspecialchars($producto_editar['url']) : ''; ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <?php echo $producto_editar ? 'Actualizar Producto' : 'Agregar Producto'; ?>
                    </button>
                    <?php if ($producto_editar): ?>
                        <a href="products.php" class="btn-cancel">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <h3>Lista de Productos</h3>
        
        <?php if ($productos->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Stock</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($producto = $productos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $producto['id']; ?></td>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo ucfirst($producto['tipo_producto']); ?></td>
                                <td><?php echo $producto['stock']; ?></td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td>
                                    <a href="products.php?editar=<?php echo $producto['id']; ?>" class="btn-edit">Editar</a>
                                    <a href="products.php?eliminar=<?php echo $producto['id']; ?>" class="btn-delete" 
                                       onclick="return confirm('¿Está seguro de que desea eliminar este producto?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay productos registrados.</p>
        <?php endif; ?>
    </main>

    <script src="script.js"></script>
</body>
</html>
