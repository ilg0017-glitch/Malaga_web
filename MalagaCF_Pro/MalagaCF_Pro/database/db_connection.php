<?php
/**
 * Conexión a la base de datos - Málaga CF Pro
 * 
 * Archivo de configuración para la conexión con MariaDB/MySQL.
 * IMPORTANTE: En producción, usa variables de entorno en lugar de credenciales hardcodeadas.
 */

// ── Configuración de la base de datos ──────────────────────────────────────
$db_config = [
    'host'     => 'localhost',
    'dbname'   => 'malaga_cf_db',
    'user'     => 'root',
    'password' => '',  // Cambiar en producción
    'charset'  => 'utf8mb4',
    'port'     => 3306
];

// ── Conexión con PDO ───────────────────────────────────────────────────────
try {
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], $options);

} catch (PDOException $e) {
    // En desarrollo, mostrar error; en producción, loguear y mostrar mensaje genérico
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
    ]);
    exit;
}
