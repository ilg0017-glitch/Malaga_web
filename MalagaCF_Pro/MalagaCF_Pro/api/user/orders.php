<?php
/**
 * API: Pedidos del usuario
 * GET - Obtener historial de pedidos con detalle de productos
 */
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Obtener pedidos del usuario
    $stmt = $pdo->prepare("
        SELECT id, fecha_pedido, total, estado
        FROM orders
        WHERE user_id = ?
        ORDER BY fecha_pedido DESC
    ");
    $stmt->execute([$currentUserId]);
    $orders = $stmt->fetchAll();

    // Para cada pedido, obtener sus items
    foreach ($orders as &$order) {
        $stmt2 = $pdo->prepare("
            SELECT oi.cantidad, oi.precio_unitario, p.nombre, p.imagen_url
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt2->execute([$order['id']]);
        $order['items'] = $stmt2->fetchAll();
    }

    echo json_encode([
        'error' => false,
        'orders' => $orders,
        'total_orders' => count($orders)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Error al obtener pedidos']);
}
