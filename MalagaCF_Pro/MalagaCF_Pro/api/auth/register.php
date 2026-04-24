<?php
/**
 * API: Registro de usuario
 * POST /api/auth/register.php
 * Body JSON: { nombre, apellidos, email, password }
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

$nombre    = trim($data['nombre'] ?? '');
$apellidos = trim($data['apellidos'] ?? '');
$email     = trim($data['email'] ?? '');
$password  = $data['password'] ?? '';

// ── Validaciones ───────────────────────────────────────────────────────────
$errors = [];

if (strlen($nombre) < 2) {
    $errors[] = 'El nombre debe tener al menos 2 caracteres';
}
if (strlen($apellidos) < 2) {
    $errors[] = 'Los apellidos deben tener al menos 2 caracteres';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'El email no es válido';
}
if (strlen($password) < 8) {
    $errors[] = 'La contraseña debe tener al menos 8 caracteres';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => $errors[0], 'errors' => $errors]);
    exit;
}

// ── Comprobar si el email ya existe ────────────────────────────────────────
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => true, 'message' => 'Este email ya está registrado', 'field' => 'email']);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Error al verificar el email']);
    exit;
}

// ── Insertar usuario ──────────────────────────────────────────────────────
try {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (nombre, apellidos, email, password_hash, aceptacion_privacidad)
        VALUES (?, ?, ?, ?, 1)
    ");
    $stmt->execute([$nombre, $apellidos, $email, $passwordHash]);
    
    $userId = $pdo->lastInsertId();
    
    // Iniciar sesión automáticamente tras registro
    $_SESSION['user_id']    = $userId;
    $_SESSION['user_name']  = $nombre;
    $_SESSION['user_email'] = $email;
    
    http_response_code(201);
    echo json_encode([
        'error'   => false,
        'message' => 'Cuenta creada correctamente',
        'user'    => [
            'id'        => (int)$userId,
            'nombre'    => $nombre,
            'apellidos' => $apellidos,
            'email'     => $email
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Error al crear la cuenta: ' . $e->getMessage()]);
}
