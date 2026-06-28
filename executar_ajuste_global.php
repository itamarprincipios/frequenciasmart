<?php
// executar_ajuste_global.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$sql = file_get_contents(__DIR__ . '/ajuste_super_admin_global.sql');

try {
    pdo()->exec($sql);
    echo "Sucesso: Super Admin desvinculado de escolas!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
