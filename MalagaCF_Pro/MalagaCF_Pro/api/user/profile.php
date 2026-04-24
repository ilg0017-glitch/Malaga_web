<?php
/**
 * API: Perfil de usuario
 * GET  - Obtener datos del perfil
 * POST - Actualizar datos del perfil
 */
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT id, nombre, apellidos, email, telefono, dni_nie, fecha_nacimiento, created_at
            FROM users WHERE id = ?
        ");
        $stmt->execute([$currentUserId]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => true, 'message' => 'Usuario no encontrado']);
            exit;
        }

        // Obtener dirección por defecto
        $stmt2 = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
        $stmt2->execute([$currentUserId]);
        $address = $stmt2->fetch();

        echo json_encode([
            'error' => false,
            'user' => $user,
            'address' => $address ?: null
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Error al obtener el perfil']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $nombre    = trim($data['nombre'] ?? '');
    $apellidos = trim($data['apellidos'] ?? '');
    $telefono  = trim($data['telefono'] ?? '');
    $dni_nie   = trim($data['dni_nie'] ?? '');

    // Validaciones
    if (strlen($nombre) < 2) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'El nombre es obligatorio']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE users SET nombre = ?, apellidos = ?, telefono = ?, dni_nie = ?
            WHERE id = ?
        ");
        $stmt->execute([$nombre, $apellidos, $telefono ?: null, $dni_nie ?: null, $currentUserId]);

        // Actualizar nombre en sesión
        $_SESSION['user_name'] = $nombre;

        // Guardar/actualizar dirección si se envía
        if (!empty($data['direccion'])) {
            $stmtAddr = $pdo->prepare("SELECT id FROM addresses WHERE user_id = ? AND is_default = 1");
            $stmtAddr->execute([$currentUserId]);
            $existingAddr = $stmtAddr->fetch();

            if ($existingAddr) {
                $pdo->prepare("
                    UPDATE addresses SET direccion = ?, ciudad = ?, codigo_postal = ?, provincia = ?
                    WHERE id = ?
                ")->execute([
                    $data['direccion'], $data['ciudad'] ?? '', $data['codigo_postal'] ?? '', $data['provincia'] ?? '',
                    $existingAddr['id']
                ]);
            } else {
                $pdo->prepare("
                    INSERT INTO addresses (user_id, direccion, ciudad, codigo_postal, provincia, is_default)
                    VALUES (?, ?, ?, ?, ?, 1)
                ")->execute([
                    $currentUserId, $data['direccion'], $data['ciudad'] ?? '', $data['codigo_postal'] ?? '', $data['provincia'] ?? ''
                ]);
            }
        }

        echo json_encode(['error' => false, 'message' => 'Perfil actualizado correctamente']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
    }
}
