<?php
// diagnostico.php — Diagnóstico temporário (REMOVER APÓS USO)
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo '<pre style="font-family:monospace;padding:1rem">';
echo '<strong>PHP Version:</strong> ' . phpversion() . "\n";
echo '<strong>Server:</strong> ' . ($_SERVER['SERVER_SOFTWARE'] ?? '?') . "\n";
echo '<strong>Host:</strong> ' . ($_SERVER['HTTP_HOST'] ?? '?') . "\n\n";

// Testa config.php
echo "--- Testando config.php ---\n";
try {
    require_once __DIR__ . '/config.php';
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "DB_USER: " . DB_USER . "\n";
    echo "config.php: OK\n\n";
} catch (Throwable $e) {
    echo "ERRO config.php: " . $e->getMessage() . "\n\n";
}

// Testa db.php
echo "--- Testando conexão PDO ---\n";
try {
    require_once __DIR__ . '/db.php';
    $test = db_one("SELECT COUNT(*) as total FROM users");
    echo "Conexão: OK — {$test->total} usuário(s) no banco\n\n";
} catch (Throwable $e) {
    echo "ERRO PDO: " . $e->getMessage() . "\n\n";
}

// Testa helpers
echo "--- Testando helpers.php ---\n";
try {
    require_once __DIR__ . '/helpers.php';
    echo "helpers.php: OK\n\n";
} catch (Throwable $e) {
    echo "ERRO helpers.php: " . $e->getMessage() . "\n\n";
}

echo "--- Extensões PHP ---\n";
echo "PDO: " . (extension_loaded('pdo') ? 'OK' : 'FALTANDO') . "\n";
echo "PDO_MySQL: " . (extension_loaded('pdo_mysql') ? 'OK' : 'FALTANDO') . "\n";
echo "JSON: " . (extension_loaded('json') ? 'OK' : 'FALTANDO') . "\n";
echo "Session: " . (extension_loaded('session') ? 'OK' : 'FALTANDO') . "\n";
echo '</pre>';
