<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debug de Inicialização</h1>";

try {
    echo "1. Carregando config.php... ";
    require_once __DIR__ . '/config.php';
    echo "✅ OK<br>";

    echo "2. Carregando db.php... ";
    require_once __DIR__ . '/db.php';
    echo "✅ OK<br>";

    echo "3. Carregando aut.php... ";
    require_once __DIR__ . '/aut.php';
    echo "✅ OK<br>";

    echo "4. Carregando helpers.php... ";
    require_once __DIR__ . '/helpers.php';
    echo "✅ OK<br>";

    echo "5. Testando conexão com o Banco... ";
    $res = db_one("SELECT 1 as teste");
    if ($res) echo "✅ Conectado ao banco!<br>";

    echo "6. Verificando função usuario_logado()... ";
    if (function_exists('usuario_logado')) echo "✅ Função existe!<br>";

    echo "<h3>🎉 Tudo parece estar configurado corretamente!</h3>";

} catch (Exception $e) {
    echo "<br><b style='color:red'>❌ ERRO ENCONTRADO:</b> " . $e->getMessage();
}
