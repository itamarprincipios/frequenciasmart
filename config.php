<?php
// ===================================================
// config.php — Configurações do FrequenciaSmart
// Detecta automaticamente: local (XAMPP) ou produção (Hostinger)
// ===================================================

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = in_array($host, ['localhost', '127.0.0.1', 'frequenciasmart.local']);

if ($isLocal) {
    // ---- AMBIENTE LOCAL (XAMPP) ----
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'frequenciasmart');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('APP_URL',  'http://localhost');
} else {
    // ---- PRODUÇÃO (Hostinger) ----
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u199671261_smartsheets1');
    define('DB_USER', 'u199671261_smart1');
    define('DB_PASS', 'Anacris@1979');
    define('APP_URL',  'https://frequenciasmart.cloud'); // Mantenha a URL ou atualize se necessário
}

define('DB_CHARSET',        'utf8mb4');
define('APP_NAME',          'FrequenciaSmart');
define('SESSION_LIFETIME',  7200); // 2 horas
