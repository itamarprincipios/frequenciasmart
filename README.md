# EduTrack — Sistema de Frequência Escolar (PHP Puro)

Sistema de controle de frequência escolar sem frameworks. **PHP puro + MySQL + PDO.**

## 🚀 Deploy Rápido (Hostinger / cPanel)

1. **Crie o banco de dados** no phpMyAdmin e importe `banco.sql`
2. **Edite `config.php`** com suas credenciais:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'seu_banco');
   define('DB_USER', 'seu_usuario');
   define('DB_PASS', 'sua_senha');
   ```
3. **Faça upload** de todos os arquivos para `public_html/` (ou subpasta)
4. **Acesse** seu domínio — será redirecionado para o login

## 🔑 Acesso Inicial

| Campo | Valor |
|---|---|
| E-mail | `admin@edutrack.com` |
| Senha | `admin123` |

> ⚠️ **Troque a senha** após o primeiro acesso!

## 📁 Estrutura

```
├── index.php              ← Roteador principal
├── config.php             ← Configurações do banco e sessão
├── db.php                 ← Conexão PDO
├── auth.php               ← Funções de autenticação
├── helpers.php            ← Funções utilitárias
├── .htaccess              ← Rewrite rules (Apache)
├── banco.sql              ← Script de criação do banco
│
├── layout/
│   ├── header.php         ← Sidebar + topbar
│   └── footer.php
│
├── pages/                 ← Páginas da aplicação
│   ├── login.php
│   ├── dashboard.php
│   ├── orientadora.php
│   ├── turmas.php
│   ├── turmas_qrcode.php
│   ├── usuarios.php
│   ├── frequencias.php
│   ├── frequencia_lancar.php
│   ├── alunos_index.php
│   ├── alunos_form.php
│   ├── alunos_qrcode.php
│   └── 403.php
│
├── actions/               ← Handlers de formulários POST
│   ├── login_post.php
│   ├── logout.php
│   ├── frequencia_registrar.php
│   ├── alunos_store.php
│   ├── alunos_update.php
│   └── alunos_destroy.php
│
└── services/
    └── AlertaService.php  ← Lógica de alertas automáticos
```

## 👥 Perfis de Acesso

| Role | Acesso |
|---|---|
| DIRETOR | Tudo, incluindo usuários |
| VICE | Dashboard, turmas, alunos, frequências |
| ORIENTADORA | Alertas, alunos, frequências |
| ASSISTENTE | Lançar e visualizar frequências |

## 📋 Funcionalidades

- ✅ Login/logout com sessão segura
- ✅ Dashboard com gráficos de faltas por turma
- ✅ Lançamento de frequência via QR Code (câmera) ou manual
- ✅ CRUD completo de alunos
- ✅ Geração de QR Codes para alunos e turmas (impressão)
- ✅ Alertas automáticos (3 faltas consecutivas ou 10/mês)
- ✅ Painel da orientadora com filtros
- ✅ Visualização de histórico de frequências

## ⚙️ Requisitos

- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Servidor Apache com `mod_rewrite` habilitado
