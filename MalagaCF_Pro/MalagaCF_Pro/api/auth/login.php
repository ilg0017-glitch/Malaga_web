<?php
/**
 * API: Login de usuario
 * POST /api/auth/login.php
 * Body JSON: { email, password }
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Método no permitido']);
    exit;
}

// Cargar conexión a BD
require_once __DIR__ . '/../../database/db_connection.php';

// Leer datos del body
$data = json_decode(file_get_contents('php://input'), true);

$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// ── Validaciones básicas ──────────────────────────────────────────────────
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Email no válido', 'field' => 'email']);
    exit;
}

if (empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'La contraseña es obligatoria', 'field' => 'password']);
    exit;
}

// ── Buscar usuario ────────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("SELECT id, nombre, apellidos, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => true, 'message' => 'Email o contraseña incorrectos']);
        exit;
    }
    
    // ── Iniciar sesión ────────────────────────────────────────────────────
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['nombre'];
    $_SESSION['user_email'] = $user['email'];
    
    echo json_encode([
        'error'   => false,
        'message' => '¡Bienvenido de nuevo!',
        'user'    => [
            'id'        => (int)$user['id'],
            'nombre'    => $user['nombre'],
            'apellidos' => $user['apellidos'],
            'email'     => $user['email']
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Error del servidor']);
}
