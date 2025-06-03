<?php
session_start();
require_once 'database.php';

// Verificar si el usuario está logueado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'kiosquero') {
    header("Location: ../login.php");
    exit();
}

// Procesar formulario de edición de producto
if (isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    // Validar datos
    if (!empty($_POST['id']) && !empty($_POST['nombre']) && !empty($_POST['descripcion']) && 
        isset($_POST['stock']) && !empty($_POST['tipo_producto']) && isset($_POST['precio'])) {
        
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $stock = (int)$_POST['stock'];
        $tipo_producto = $_POST['tipo_producto'];
        $precio = (float)$_POST['precio'];
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        
        // Procesar URL de imagen externa si se proporciona
        if (isset($_POST['url_imagen']) && !empty($_POST['url_imagen'])) {
            $url_imagen = trim($_POST['url_imagen']);
            
            // Verificar que la URL sea válida
            if (filter_var($url_imagen, FILTER_VALIDATE_URL)) {
                // Usar directamente la URL externa
                $url = $url_imagen;
            } else {
                $_SESSION['mensaje'] = "La URL de imagen proporcionada no es válida.";
                $_SESSION['tipo_mensaje'] = "warning";
                header("Location: dashboard.php");
                exit();
            }
        }
        
        // Procesar la imagen si se ha subido una y no se ha proporcionado una URL externa
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0 && empty($_POST['url_imagen'])) {
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
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $_SESSION['mensaje'] = "Formato de imagen no válido. Por favor, use JPG, PNG o GIF.";
                $_SESSION['tipo_mensaje'] = "warning";
                header("Location: dashboard.php");
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
    
    // Redirigir de vuelta al dashboard
    header("Location: dashboard.php");
    exit();
}

// Si llega aquí sin acción válida, redirigir al dashboard
header("Location: dashboard.php");
exit();
?>
