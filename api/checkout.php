<?php
// api/checkout.php - API untuk proses checkout
header('Content-Type: application/json');
require_once '../config.php';

try {
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (customer_name, email, phone, address, total_price) 
        VALUES (:name, :email, :phone, :address, :total)
    ");
    
    $stmt->execute([
        'name' => $data['customer_name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'address' => $data['address'],
        'total' => $data['total']
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // Insert order items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        VALUES (:order_id, :product_id, :quantity, :price)
    ");
    
    foreach ($data['items'] as $item) {
        $stmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['id'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'message' => 'Pesanan berhasil dibuat'
    ]);
    
} catch(PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>