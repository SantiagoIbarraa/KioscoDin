<?php
session_start();

// Verificar si el usuario está logueado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: ../login.php");
    exit();
}

// Incluir conexión a la base de datos
require_once 'db_connect.php';

// Verificar si se proporcionó un ID de producto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$producto_id = $_GET['id'];

// Obtener información del producto
$sql = "SELECT * FROM productos WHERE id = ? AND stock > 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Producto no encontrado o sin stock
    header("Location: index.php");
    exit();
}

$producto = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - Kiosco Saludable</title>
    <?php include 'ui.php'; renderStylesheetLinks(); ?>
    <style>
        .producto-detalle {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .producto-imagen {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .producto-info {
            margin-bottom: 30px;
        }
        
        .producto-info h2 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .producto-descripcion {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .producto-stock {
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        
        .producto-precio {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        
        .acciones {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-agregar {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn-agregar:hover {
            background-color: #45a049;
        }
        
        .btn-volver {
            background-color: #f1f1f1;
            color: #333;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn-volver:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <?php renderHeader(); ?>
    
    <div class="main-content">
        <div class="producto-detalle">
            <div class="producto-info">
                <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                <div class="producto-imagen">
                    <img src="https://placehold.co/800x400" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                </div>
                <p class="producto-descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                <p class="producto-precio">$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></p>
                <p class="producto-stock">Disponible: <?php echo $producto['stock']; ?> unidades</p>
                
                <div class="acciones">
                    <button class="btn-agregar" onclick="agregarAlCarrito(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>')">
                        Agregar al pedido
                    </button>
                    <a href="index.php" class="btn-volver">Volver</a>
                </div>
            </div>
        </div>
        
        <?php renderCartIcon(); ?>
        <?php renderNavbar(); ?>
    </div>
    
    <?php renderCartModal(); ?>
    
    <script src="script.js"></script>
    <script>
        function agregarAlCarrito(id, nombre) {
            // Aquí puedes implementar la lógica para agregar al carrito
            // Por ahora, solo mostraremos un mensaje
            alert('Producto "' + nombre + '" agregado al pedido');
            
            // Obtener precio del producto
            const precio = <?php echo $producto['precio']; ?>;
            
            // Incrementar contador del carrito (ejemplo básico)
            let contador = document.getElementById('cartCounter');
            contador.textContent = parseInt(contador.textContent) + 1;
        }
    </script>
</body>
</html>
