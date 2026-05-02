-- ============================================================
-- ajuste_super_admin_global.sql
-- Permite que Super Admins não tenham vínculo com escolas
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tornar a coluna escola_id anulável na tabela de usuários
ALTER TABLE `users` MODIFY `escola_id` INT UNSIGNED NULL;

-- 2. Desvincular todos os Super Admins de qualquer escola
UPDATE `users` SET `escola_id` = NULL WHERE is_super_admin = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- Verificação
SELECT id, nome, email, is_super_admin, escola_id FROM users WHERE is_super_admin = 1;
