-- ============================================================
-- migracao_multi_escola.sql
-- Transformação para suporte multi-escola (SaaS)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Criar a tabela de escolas
CREATE TABLE IF NOT EXISTS `escolas` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome`       VARCHAR(255) NOT NULL,
    `slug`       VARCHAR(100) NOT NULL UNIQUE,
    `ativa`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Inserir a primeira escola
INSERT IGNORE INTO `escolas` (`id`, `nome`, `slug`, `ativa`) VALUES
(1, 'Escola Hildemar Pereira de Figueredo', 'hildemar', 1);

-- 3. Adicionar escola_id nas tabelas existentes
-- Users
ALTER TABLE `users` ADD COLUMN `escola_id` INT UNSIGNED NULL AFTER `id`;
UPDATE `users` SET `escola_id` = 1;
ALTER TABLE `users` MODIFY `escola_id` INT UNSIGNED NOT NULL, 
ADD FOREIGN KEY (`escola_id`) REFERENCES `escolas`(`id`) ON DELETE CASCADE;

-- Turmas
ALTER TABLE `turmas` ADD COLUMN `escola_id` INT UNSIGNED NULL AFTER `id`;
UPDATE `turmas` SET `escola_id` = 1;
ALTER TABLE `turmas` MODIFY `escola_id` INT UNSIGNED NOT NULL, 
ADD FOREIGN KEY (`escola_id`) REFERENCES `escolas`(`id`) ON DELETE CASCADE;

-- Alunos
ALTER TABLE `alunos` ADD COLUMN `escola_id` INT UNSIGNED NULL AFTER `id`;
UPDATE `alunos` SET `escola_id` = 1;
ALTER TABLE `alunos` MODIFY `escola_id` INT UNSIGNED NOT NULL, 
ADD FOREIGN KEY (`escola_id`) REFERENCES `escolas`(`id`) ON DELETE CASCADE;

-- Frequencias
ALTER TABLE `frequencias` ADD COLUMN `escola_id` INT UNSIGNED NULL AFTER `id`;
UPDATE `frequencias` SET `escola_id` = 1;
ALTER TABLE `frequencias` MODIFY `escola_id` INT UNSIGNED NOT NULL, 
ADD FOREIGN KEY (`escola_id`) REFERENCES `escolas`(`id`) ON DELETE CASCADE;

-- Alertas
ALTER TABLE `alertas` ADD COLUMN `escola_id` INT UNSIGNED NULL AFTER `id`;
UPDATE `alertas` SET `escola_id` = 1;
ALTER TABLE `alertas` MODIFY `escola_id` INT UNSIGNED NOT NULL, 
ADD FOREIGN KEY (`escola_id`) REFERENCES `escolas`(`id`) ON DELETE CASCADE;

-- Notificacoes
ALTER TABLE `notificacoes` ADD COLUMN `escola_id` INT UNSIGNED NULL AFTER `id`;
UPDATE `notificacoes` SET `escola_id` = 1;
ALTER TABLE `notificacoes` MODIFY `escola_id` INT UNSIGNED NOT NULL, 
ADD FOREIGN KEY (`escola_id`) REFERENCES `escolas`(`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
