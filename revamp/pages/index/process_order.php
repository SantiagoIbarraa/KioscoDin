<?php
header('Content-Type: application/json');
session_start();

require_once 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$json = file_get_contents('php://input');
$data = json_decode($json, true);
if (!isset($data['userId'], $data['deliveryTime'], $data['paymentMethod'], $data['items']) || empty($data['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos de pedido incompletos']);
    exit;
}

$response = [];

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, entrega_pedido, estado_pedido, estado_pago, medio_pago) 
                          VALUES (?, ?, 'por entregar', 'sin pagar', ?)");
    
    $deliveryTime = date('Y-m-d H:i:s', strtotime($data['deliveryTime']));
    
    $stmt->bind_param("iss", $data['userId'], $deliveryTime, $data['paymentMethod']);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al crear el pedido: " . $conn->error);
    }
    
    $orderId = $conn->insert_id;
    
    $stmt = $conn->prepare("INSERT INTO detalles_pedidos (id_pedido, id_producto, cantidad, precio) 
                          VALUES (?, ?, ?, ?)");
    
    foreach ($data['items'] as $item) {
        $productId = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al agregar los productos al pedido: " . $conn->error);
        }
    }
    
    $conn->commit();
    
    $response = [
        'success' => true,
        'orderId' => $orderId,
        'message' => 'Pedido creado exitosamente'
    ];
    
} catch (Exception $e) {
    $conn->rollback();
    
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
