<?php
/**
 * API: Favoritos del usuario
 * GET    - Obtener productos favoritos
 * POST   - Añadir producto a favoritos { product_id }
 * DELETE - Quitar producto de favoritos { product_id }
 */
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT uf.id AS fav_id, uf.created_at AS fav_date,
                   p.id AS product_id, p.nombre, p.precio, p.imagen_url, p.categoria, p.stock
            FROM user_favorites uf
            JOIN products p ON uf.product_id = p.id
            WHERE uf.user_id = ?
            ORDER BY uf.created_at DESC
        ");
        $stmt->execute([$currentUserId]);
        $favorites = $stmt->fetchAll();

        echo json_encode([
            'error' => false,
            'favorites' => $favorites,
            'total' => count($favorites)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Error al obtener favoritos']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($data['product_id'] ?? 0);

    if (!$productId) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Producto no especificado']);
        exit;
    }

    try {
        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$currentUserId, $productId]);

        if ($stmt->fetch()) {
            echo json_encode(['error' => false, 'message' => 'Ya está en favoritos', 'action' => 'exists']);
            exit;
        }

        $pdo->prepare("INSERT INTO user_favorites (user_id, product_id) VALUES (?, ?)")
            ->execute([$currentUserId, $productId]);

        echo json_encode(['error' => false, 'message' => 'Añadido a favoritos', 'action' => 'added']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Error al añadir a favoritos']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = (int)($data['product_id'] ?? 0);

    if (!$productId) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Producto no especificado']);
        exit;
    }

    try {
        $pdo->prepare("DELETE FROM user_favorites WHERE user_id = ? AND product_id = ?")
            ->execute([$currentUserId, $productId]);

        echo json_encode(['error' => false, 'message' => 'Eliminado de favoritos', 'action' => 'removed']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Error al eliminar de favoritos']);
    }
}
