<?php
/**
 * API: Área de socio / Abonos
 * GET  - Obtener suscripciones del usuario + tipos disponibles
 * POST - Crear nueva suscripción
 */
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../../database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Suscripciones activas del usuario
        $stmt = $pdo->prepare("
            SELECT us.*, st.nombre AS tipo_nombre, st.precio, st.descripcion, st.zona_estadio
            FROM user_subscriptions us
            JOIN subscription_types st ON us.subscription_type_id = st.id
            WHERE us.user_id = ?
            ORDER BY us.fecha_inicio DESC
        ");
        $stmt->execute([$currentUserId]);
        $subscriptions = $stmt->fetchAll();

        // Tipos de abono disponibles
        $stmt2 = $pdo->query("SELECT * FROM subscription_types ORDER BY precio ASC");
        $types = $stmt2->fetchAll();

        echo json_encode([
            'error' => false,
            'subscriptions' => $subscriptions,
            'types' => $types
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Error al obtener suscripciones']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $typeId      = (int)($data['subscription_type_id'] ?? 0);
    $metodoPago  = $data['metodo_pago'] ?? '';
    $ibanMasked  = $data['iban_masked'] ?? null;

    if (!$typeId || !in_array($metodoPago, ['tarjeta', 'transferencia', 'domiciliacion'])) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => 'Datos incompletos']);
        exit;
    }

    try {
        // Verificar que el tipo existe
        $stmt = $pdo->prepare("SELECT * FROM subscription_types WHERE id = ?");
        $stmt->execute([$typeId]);
        $type = $stmt->fetch();

        if (!$type) {
            http_response_code(404);
            echo json_encode(['error' => true, 'message' => 'Tipo de abono no encontrado']);
            exit;
        }

        // Verificar si ya tiene un abono activo de este tipo
        $stmt2 = $pdo->prepare("
            SELECT id FROM user_subscriptions
            WHERE user_id = ? AND subscription_type_id = ? AND estado = 'activo'
        ");
        $stmt2->execute([$currentUserId, $typeId]);
        if ($stmt2->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => true, 'message' => 'Ya tienes este abono activo']);
            exit;
        }

        $fechaInicio = date('Y-m-d');
        $fechaFin = date('Y-m-d', strtotime('+1 year'));

        $stmt3 = $pdo->prepare("
            INSERT INTO user_subscriptions (user_id, subscription_type_id, season, fecha_inicio, fecha_fin, estado, metodo_pago, iban_masked)
            VALUES (?, ?, '2025/2026', ?, ?, 'activo', ?, ?)
        ");
        $stmt3->execute([$currentUserId, $typeId, $fechaInicio, $fechaFin, $metodoPago, $ibanMasked]);

        echo json_encode([
            'error' => false,
            'message' => "¡Abono {$type['nombre']} activado correctamente!",
            'subscription_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => 'Error al procesar el abono']);
    }
}
