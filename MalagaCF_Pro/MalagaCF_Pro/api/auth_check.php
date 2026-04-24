<?php
/**
 * Helper: Verificar autenticación
 * Incluir al inicio de cualquier endpoint que requiera sesión activa.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => true, 'message' => 'Debes iniciar sesión']);
    exit;
}

$currentUserId = $_SESSION['user_id'];
