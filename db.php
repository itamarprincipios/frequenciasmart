<?php
// ===================================================
// db.php — Conexão PDO singleton
// ===================================================

function pdo(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:monospace;color:red;padding:2rem">Erro de conexão com o banco: ' . htmlspecialchars($e->getMessage()) . '</div>');
        }
    }

    return $pdo;
}

/**
 * Executa uma query e retorna todos os resultados
 */
function db_all(string $sql, array $params = []): array {
    $stmt = pdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Executa uma query e retorna um único resultado
 */
function db_one(string $sql, array $params = []): ?object {
    $stmt = pdo()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Executa uma query de escrita (INSERT/UPDATE/DELETE) e retorna número de linhas afetadas
 */
function db_run(string $sql, array $params = []): int {
    $stmt = pdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Executa INSERT e retorna o last insert ID
 */
function db_insert(string $sql, array $params = []): int {
    $stmt = pdo()->prepare($sql);
    $stmt->execute($params);
    return (int) pdo()->lastInsertId();
}
