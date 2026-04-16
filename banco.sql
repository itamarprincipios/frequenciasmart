-- ============================================================
-- banco.sql — EduTrack: Script completo de criação do banco
-- Execute este arquivo no phpMyAdmin ou via MySQL CLI
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABELAS
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome`       VARCHAR(255) NOT NULL,
    `email`      VARCHAR(255) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('DIRETOR','VICE','ORIENTADORA','ASSISTENTE') NOT NULL DEFAULT 'ASSISTENTE',
    `ativo`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `turmas` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome`        VARCHAR(100) NOT NULL,
    `turno`       ENUM('MANHA','TARDE','NOITE') NOT NULL DEFAULT 'MANHA',
    `ano_letivo`  YEAR NOT NULL DEFAULT (YEAR(CURDATE())),
    `ativa`       TINYINT(1) NOT NULL DEFAULT 1,
    `qr_token`    VARCHAR(64) NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `alunos` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome`       VARCHAR(255) NOT NULL,
    `matricula`  VARCHAR(50) NOT NULL UNIQUE,
    `qr_token`   VARCHAR(64) NOT NULL UNIQUE,
    `turma_id`   INT UNSIGNED NOT NULL,
    `ativo`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`turma_id`) REFERENCES `turmas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `frequencias` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `aluno_id`       INT UNSIGNED NOT NULL,
    `turma_id`       INT UNSIGNED NOT NULL,
    `data`           DATE NOT NULL,
    `status`         ENUM('PRESENTE','FALTA') NOT NULL,
    `registrado_por` INT UNSIGNED NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_aluno_data` (`aluno_id`, `data`),
    FOREIGN KEY (`aluno_id`) REFERENCES `alunos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`turma_id`) REFERENCES `turmas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`registrado_por`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `alertas` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `aluno_id`       INT UNSIGNED NOT NULL,
    `tipo`           ENUM('CONSECUTIVA','INTERCALADA') NOT NULL,
    `mes_referencia` VARCHAR(7) NOT NULL COMMENT 'Formato: YYYY-MM',
    `enviado`        TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`aluno_id`) REFERENCES `alunos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `notificacoes` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT UNSIGNED NOT NULL,
    `titulo`     VARCHAR(255) NOT NULL,
    `mensagem`   TEXT NOT NULL,
    `lida`       TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

-- Usuário administrador
-- Senha padrão: admin123  (troque após o primeiro login!)
INSERT IGNORE INTO `users` (`nome`, `email`, `password`, `role`, `ativo`) VALUES
('Administrador', 'admin@edutrack.com', '$2y$10$cWk2G.ejVbwHGoYxwfPVKuoGzlUxUflUm9ZVkrKkDEFUk0O8qYMUS', 'DIRETOR', 1);

-- Turmas de exemplo
INSERT IGNORE INTO `turmas` (`nome`, `turno`, `ano_letivo`, `ativa`, `qr_token`) VALUES
('1º Ano A', 'MANHA', YEAR(CURDATE()), 1, 'TRM_A1B2C3D4E5'),
('2º Ano B', 'TARDE', YEAR(CURDATE()), 1, 'TRM_F6G7H8I9J0'),
('3º Ano C', 'NOITE', YEAR(CURDATE()), 1, 'TRM_K1L2M3N4O5');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- NOTA SOBRE A SENHA:
-- O hash acima corresponde a: admin123
-- Para gerar um novo hash, use este código PHP:
--   <?php echo password_hash('sua_nova_senha', PASSWORD_BCRYPT); ?>
-- ============================================================
