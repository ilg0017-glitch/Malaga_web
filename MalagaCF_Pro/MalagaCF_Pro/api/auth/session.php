<?php
/**
 * API: Comprobar sesión activa
 * GET /api/auth/session.php
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (isset($_SESSION['user_id'])) {
    // Cargar datos completos del usuario desde la BD
    require_once __DIR__ . '/../../database/db_connection.php';
    
    try {
        $stmt = $pdo->prepare("SELECT id, nombre, apellidos, email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'id'        => (int)$user['id'],
                    'nombre'    => $user['nombre'],
                    'apellidos' => $user['apellidos'],
                    'email'     => $user['email']
                ]
            ]);
        } else {
            // El usuario ya no existe en la BD
            session_destroy();
            echo json_encode(['authenticated' => false, 'user' => null]);
        }
    } catch (PDOException $e) {
        echo json_encode(['authenticated' => false, 'user' => null]);
    }
} else {
    echo json_encode(['authenticated' => false, 'user' => null]);
}
