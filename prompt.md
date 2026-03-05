WALKTHROUGH COMPLETO
Sistema de Frequência Escolar com QR Code + Alertas Inteligentes
1️⃣ VISÃO GERAL DO PRODUTO

Desenvolver um sistema Web + Mobile para controle diário de frequência escolar via QR Code, com:

Registro rápido de presença por turma

Controle automático de faltas

Geração de alertas inteligentes

Notificações em app mobile

Painel administrativo completo

O sistema será multi-usuário e institucional.

2️⃣ PERFIS DE USUÁRIO

Implementar controle de acesso baseado em papéis (RBAC).

1. ASSISTENTE

Funções:

Login no app mobile

Selecionar turma

Escanear QR Code

Marcar faltas

Salvar frequência

Visualizar histórico das turmas que atende

Não pode:

Alterar dados de alunos

Visualizar relatórios globais

Acessar alertas de outras turmas

2. ORIENTADORA ESCOLAR

Funções:

Visualizar frequência por aluno

Receber alertas automáticos

Visualizar histórico completo de faltas

Exportar relatórios

Registrar observações pedagógicas

3. DIRETOR

Funções:

Visualizar dashboard geral da escola

Receber todos os alertas

Visualizar ranking de frequência

Gerenciar turmas e usuários

Exportação de relatórios institucionais

4. VICE-DIRETOR

Funções:

Mesmo acesso do diretor

Sem permissão para excluir usuários ADMIN

3️⃣ ARQUITETURA DO SISTEMA
Frontend Web

React + TypeScript

Dashboard administrativo

Painel de relatórios

Mobile App

Flutter

Leitura de QR Code

Notificações push

Interface simplificada para assistente

Backend

Node.js + Express

API REST

JWT para autenticação

Middleware por perfil

Banco de Dados

PostgreSQL

Notificações

Firebase Cloud Messaging (push)

Serviço interno de alerta

4️⃣ MODELAGEM DO BANCO DE DADOS
Tabela Usuarios

id

nome

email

senha_hash

role (ASSISTENTE, ORIENTADORA, DIRETOR, VICE)

ativo

created_at

Tabela Turmas

id

nome

turno

ano_letivo

Tabela Alunos

id

nome

matricula

turma_id

ativo

Tabela Frequencias

id

aluno_id

turma_id

data

status (PRESENTE, FALTA)

registrado_por

created_at

Constraint:

Unique (aluno_id, data)

Tabela Alertas

id

aluno_id

tipo (CONSECUTIVA, INTERCALADA)

mes_referencia

enviado

created_at

Tabela Notificacoes

id

usuario_id

titulo

mensagem

lida (boolean)

created_at

5️⃣ FLUXO OPERACIONAL – ASSISTENTE

Login no app.

Seleciona turma.

Escaneia QR Code.

Sistema valida QR com turma.

Lista alunos.

Todos marcados como PRESENTE.

Assistente marca ausentes.

Salva.

Backend executa verificação de alertas.

6️⃣ LÓGICA DE ALERTA AUTOMÁTICO
Regra 1 – 3 Faltas Consecutivas

Função:

verificarFaltasConsecutivas(aluno_id)


Processo:

Buscar frequências ordenadas por data desc.

Contar faltas até encontrar primeiro presente.

Se contador >= 3:

Verificar se já existe alerta do tipo CONSECUTIVA no mês atual.

Se não existir:

Criar alerta.

Disparar notificações.

Regra 2 – 10 Faltas no Mês

Função:

verificarFaltasMensais(aluno_id)


Processo:

Buscar faltas do mês atual.

Se total >= 10:

Verificar se já existe alerta INTERCALADA no mês.

Se não existir:

Criar alerta.

Disparar notificações.

7️⃣ DISPARO DE NOTIFICAÇÕES

Quando um alerta for criado:

Buscar todos usuários com role:

ORIENTADORA

DIRETOR

VICE

Criar registro na tabela Notificacoes.

Enviar push notification via Firebase:

Mensagem padrão:

Título:
"Alerta de Frequência"

Corpo:
"O aluno {nome} atingiu o limite de faltas ({tipo})."

8️⃣ DASHBOARD WEB

Implementar:

Página Inicial (Direção)

Total de faltas no mês

Total de alertas ativos

Ranking alunos com mais faltas

Gráfico por turma

Página da Orientadora

Lista de alertas ativos

Filtro por turma

Histórico por aluno

9️⃣ QR CODE

Cada turma terá QR fixo contendo:

TURMA_ID:23
ANO:2026


Backend valida:

Se QR corresponde à turma selecionada.

Se ano letivo confere.

🔐 10️⃣ SEGURANÇA

Senhas com bcrypt.

JWT com expiração.

Middleware por role.

Validação de duplicidade de frequência por data.

Logs de auditoria.

HTTPS obrigatório.

11️⃣ PROCESSO NOTURNO AUTOMÁTICO

Criar job diário (node-cron):

Rodar às 23:59

Verificar novamente todos alunos

Garantir que nenhum alerta deixou de ser disparado

12️⃣ TESTES

Criar testes para:

3 faltas consecutivas.

10 faltas mensais.

Bloqueio de duplicidade.

Controle de acesso por perfil.

13️⃣ ENTREGA ESPERADA DA IA

A IA deve gerar:

Estrutura completa do projeto

Backend funcional

Frontend Web funcional

App Mobile funcional

Sistema de notificações push

Scripts de seed

Instruções de deploy

Desenvolver por módulos:

Banco e backend

Autenticação e perfis

Frequência

Alertas

Notificações

Dashboard

App Mobile

🔥 OBSERVAÇÃO ESTRATÉGICA

Esse sistema já nasce com potencial de virar:

Produto SaaS

Sistema municipal

Plataforma multi-escolas

Integração futura com boletim e desempenho