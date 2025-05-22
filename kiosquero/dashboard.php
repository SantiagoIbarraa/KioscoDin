<?php
session_start();
require_once 'database.php';

// Verificar si el usuario está logueado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'kiosquero') {
    header("Location: ../login.php");
    exit();
}

// Obtener productos para mostrar en el panel
$query_productos = "SELECT id, nombre, tipo_producto, stock, precio FROM productos ORDER BY nombre ASC LIMIT 6";
$productos_recientes = $conn->query($query_productos);

// Contar total de productos por tipo
$query_tipos = "SELECT tipo_producto, COUNT(*) as cantidad FROM productos GROUP BY tipo_producto";
$tipos_productos = $conn->query($query_tipos);

// Obtener datos para estadísticas
$query_pedidos_pendientes = "SELECT COUNT(*) as cantidad FROM pedidos WHERE estado_pedido = 'vigente'";
$pedidos_pendientes = $conn->query($query_pedidos_pendientes)->fetch_assoc()['cantidad'];

$query_total_productos = "SELECT COUNT(*) as cantidad FROM productos";
$total_productos = $conn->query($query_total_productos)->fetch_assoc()['cantidad'];

$query_productos_stock_bajo = "SELECT COUNT(*) as cantidad FROM productos WHERE stock < 10";
$productos_stock_bajo = $conn->query($query_productos_stock_bajo)->fetch_assoc()['cantidad'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - KiosQuiero</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style_dashboard.css">
</head>
<body>
<?php include 'header.php'; ?>

    <main class="main-content">
        <div class="dashboard-header">
            <h2>Panel de Control</h2>
        </div>
        
        <!-- Resumen de estadísticas -->
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon pedidos-icon">
                    <i class="icon">📋</i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pedidos_pendientes; ?></h3>
                    <p>Pedidos Pendientes</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon productos-icon">
                    <i class="icon">🍎</i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_productos; ?></h3>
                    <p>Productos Totales</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stock-icon">
                    <i class="icon">⚠️</i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $productos_stock_bajo; ?></h3>
                    <p>Productos con Stock Bajo</p>
                </div>
            </div>
        </div>
        
        <!-- Sección de Productos -->
        <div class="products-section">
            <div class="section-header">
                <h3>Productos Recientes</h3>
                <a href="products.php" class="btn-view-all">Ver todos</a>
            </div>
            
            <div class="products-overview">
                <?php if ($tipos_productos->num_rows > 0): ?>
                    <div class="product-categories">
                        <?php while($tipo = $tipos_productos->fetch_assoc()): ?>
                            <div class="category-card">
                                <h4><?php echo ucfirst($tipo['tipo_producto']); ?></h4>
                                <p class="category-count"><?php echo $tipo['cantidad']; ?> productos</p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($productos_recientes->num_rows > 0): ?>
                    <div class="products-grid">
                        <?php while($producto = $productos_recientes->fetch_assoc()): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php 
                                    if (!empty($producto['url'])): 
                                    ?>
                                        <img src="../<?php echo htmlspecialchars($producto['url']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <?php else: ?>
                                        <div class="no-image"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                                    <p class="product-type"><?php echo ucfirst($producto['tipo_producto']); ?></p>
                                    <div class="product-details">
                                        <span class="product-stock">Stock: <?php echo $producto['stock']; ?></span>
                                        <span class="product-price">$<?php echo number_format($producto['precio'], 2); ?></span>
                                    </div>
                                    <button class="btn-edit-product" onclick="abrirModalEditar(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars(addslashes($producto['nombre'])); ?>', '<?php echo htmlspecialchars(addslashes($producto['descripcion'] ?? '')); ?>', <?php echo $producto['stock']; ?>, '<?php echo $producto['tipo_producto']; ?>', <?php echo $producto['precio']; ?>, '<?php echo htmlspecialchars(addslashes($producto['url'] ?? '')); ?>')">Editar</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No hay productos registrados.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="quick-actions">
            <div class="section-header">
                <h3>Acciones Rápidas</h3>
            </div>
            <div class="actions-buttons">
                <a href="products.php" class="action-btn">
                    <span class="icon">➕</span>
                    <span class="text">Agregar Producto</span>
                </a>
                <a href="index.php" class="action-btn">
                    <span class="icon">📦</span>
                    <span class="text">Ver Pedidos</span>
                </a>
                <a href="reviews.php" class="action-btn">
                    <span class="icon">⭐</span>
                    <span class="text">Ver Reseñas</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Modal de Edición de Producto -->
    <div id="modalEditarProducto" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Producto</h2>
                <span class="close-modal" onclick="cerrarModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formEditarProducto" action="procesar_producto.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="producto_id" name="id">
                    <input type="hidden" name="accion" value="editar">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto:</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock:</label>
                        <input type="number" id="stock" name="stock" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_producto">Tipo de Producto:</label>
                        <select id="tipo_producto" name="tipo_producto" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="ensalada">Ensalada</option>
                            <option value="carne">Carne</option>
                            <option value="bebida">Bebida</option>
                            <option value="extra">Extra</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio">Precio:</label>
                        <input type="number" id="precio" name="precio" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen_modal">Imagen del producto:</label>
                        <input type="file" id="imagen_modal" name="imagen" accept="image/*">
                        <small class="form-text">Seleccione una imagen para el producto (JPG, PNG o GIF)</small>
                        <div id="imagen-preview-modal" style="margin-top: 10px; display: none;">
                            <p>Imagen actual:</p>
                            <img id="imagen-actual" src="" alt="Imagen del producto" style="max-width: 200px; max-height: 200px;">
                        </div>
                        <input type="hidden" id="url" name="url">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Actualizar Producto</button>
                        <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Funciones para manejar el modal
        function abrirModalEditar(id, nombre, descripcion, stock, tipo, precio, url) {
            document.getElementById('producto_id').value = id;
            document.getElementById('nombre').value = nombre;
            document.getElementById('descripcion').value = descripcion;
            document.getElementById('stock').value = stock;
            document.getElementById('tipo_producto').value = tipo;
            document.getElementById('precio').value = precio;
            document.getElementById('url').value = url || '';
            
            // Mostrar la imagen actual si existe
            if (url && url.trim() !== '') {
                document.getElementById('imagen-preview-modal').style.display = 'block';
                document.getElementById('imagen-actual').src = '../' + url;
            } else {
                document.getElementById('imagen-preview-modal').style.display = 'none';
            }
            
            document.getElementById('modalEditarProducto').style.display = 'block';
        }
        
        function cerrarModal() {
            document.getElementById('modalEditarProducto').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            var modal = document.getElementById('modalEditarProducto');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>
