<?php
// install.php — Script para instalação automática do banco de dados
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

echo "<h2>⚙️ Instalador do FrequenciaSmart</h2>";

$arquivoSql = __DIR__ . '/banco.sql';

if (!file_exists($arquivoSql)) {
    die("<p style='color:red'>❌ Arquivo banco.sql não encontrado.</p>");
}

try {
    $sql = file_get_contents($arquivoSql);
    
    // O PDO não executa múltiplos comandos com ; de uma vez por padrão em db_run
    // Então vamos dividir o SQL por ponto e vírgula
    $comandos = explode(';', $sql);
    
    $total = 0;
    $sucesso = 0;

    foreach ($comandos as $comando) {
        $comando = trim($comando);
        if ($comando) {
            $total++;
            try {
                db_run($comando);
                $sucesso++;
            } catch (Exception $e) {
                echo "<p style='color:orange'>⚠️ Erro no comando: " . htmlspecialchars(substr($comando, 0, 50)) . "... <br><b>Erro:</b> " . $e->getMessage() . "</p>";
            }
        }
    }

    echo "<hr>";
    echo "<p style='color:green; font-size: 1.2rem;'>✅ <b>Instalação concluída!</b></p>";
    echo "<p>Comandos executados: $sucesso de $total</p>";
    echo "<p>Agora você pode tentar fazer o login com <b>admin@frequenciasmart.com</b> / <b>admin123</b></p>";
    echo "<br><a href='/login' style='padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 5px;'>Ir para o Login</a>";
    
    // Opcional: Recomendar deletar este arquivo por segurança
    echo "<p style='margin-top: 2rem; color: #666; font-size: .8rem;'><i>Recomendação: Delete o arquivo install.php após o uso por questões de segurança.</i></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erro crítico: " . $e->getMessage() . "</p>";
}
