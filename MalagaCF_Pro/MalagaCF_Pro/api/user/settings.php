<?php
/**
 * API: Configuración del usuario
 * POST - Cambiar contraseña { old_password, new_password }
 * DELETE - Eliminar cuenta { password }
 */
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'change_password';

    if ($action === 'change_password') {
        $oldPass = $data['old_password'] ?? '';
        $newPass = $data['new_password'] ?? '';

        if (strlen($newPass) < 8) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$currentUserId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($oldPass, $user['password_hash'])) {
                http_response_code(403);
                echo json_encode(['error' => true, 'message' => 'La contraseña actual es incorrecta']);
                exit;
            }

            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
                ->execute([$newHash, $currentUserId]);

            echo json_encode(['error' => false, 'message' => 'Contraseña actualizada correctamente']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Error al cambiar la contraseña']);
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $password = $data['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$currentUserId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            http_response_code(403);
            echo json_encode(['error' => true, 'message' => 'Contraseña incorrecta']);
            exit;
        }

        // Eliminar usuario (CASCADE eliminará registros relacionados)
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$currentUserId]);

        // Destruir sesión
        session_destroy();

        echo json_encode(['error' => false, 'message' => 'Cuenta eliminada correctamente']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Error al eliminar la cuenta']);
    }
}
