# 📋 Contexto do Projeto — Sistema de Gerenciamento de Chamados

> **Arquivo de workflow para uso como contexto em requisições ao assistente.**
> Atualize este arquivo sempre que novas funcionalidades forem implementadas, rotas criadas ou o estado do projeto mudar.

---

## 🧱 Stack e Ambiente

- **Framework:** Laravel (versão mais recente)
- **Tipo:** API RESTful — sem frontend, sem Blade
- **Autenticação:** Laravel Breeze (modo API) + Laravel Sanctum
- **Banco de dados:** MySQL
- **ORM:** Eloquent
- **Formato de resposta:** JSON

---

## 🎯 Objetivo do Sistema

API de gerenciamento de chamados voltada para controle interno de leads (síndicos de condomínios que entraram em contato pelo site). O administrador pode visualizar, atualizar, anotar e arquivar chamados de forma centralizada via requisições HTTP.

---

## 👤 Perfis de Usuário

| Perfil        | Descrição                                                   |
|---------------|-------------------------------------------------------------|
| Administrador | Único perfil atual. Gerencia todos os chamados e leads.     |

> **Obs:** Não há portal público para o síndico. Todo o acesso é interno/administrativo via API.

---

## ✅ Requisitos Funcionais

| ID   | Nome                                       | Status          | Descrição resumida                                                                 |
|------|--------------------------------------------|-----------------|------------------------------------------------------------------------------------|
| RF09 | Listagem Centralizada de Leads (Dashboard) | 🔲 Não iniciado | Endpoint que retorna todos os leads/chamados recebidos.                            |
| RF10 | Atualização de Status do Atendimento       | 🔲 Não iniciado | Endpoint para mudar o status de um chamado (ex: novo, em andamento, encerrado).   |
| RF11 | Registro de Observações Internas (Notas)   | 🔲 Não iniciado | Endpoint para anotar detalhes da conversa com o síndico.                          |
| RF12 | Exclusão ou Arquivamento de Chamados       | 🔲 Não iniciado | Endpoint para remover ou arquivar chamados de spam, teste ou fora do perfil.      |

> Legenda: 🔲 Não iniciado · 🔄 Em andamento · ✅ Concluído

---

## 🗺️ Rotas da Aplicação

### Rotas de Autenticação — Breeze API (Sanctum)

| Método    | URI                                     | Nome                  | Controller                                           | Middleware   | Status     |
|-----------|-----------------------------------------|-----------------------|------------------------------------------------------|--------------|------------|
| POST      | `/api/login`                            | `login`               | `Auth\AuthenticatedSessionController@store`          | guest        | ✅ Ativo   |
| POST      | `/api/logout`                           | `logout`              | `Auth\AuthenticatedSessionController@destroy`        | auth:sanctum | ✅ Ativo   |
| POST      | `/api/forgot-password`                  | `password.email`      | `Auth\PasswordResetLinkController@store`             | guest        | ✅ Ativo   |
| POST      | `/api/reset-password`                   | `password.store`      | `Auth\NewPasswordController@store`                   | guest        | ✅ Ativo   |
| POST      | `/api/email/verification-notification`  | `verification.send`   | `Auth\EmailVerificationNotificationController@store` | auth:sanctum | ✅ Ativo   |
| GET\|HEAD | `/api/verify-email/{id}/{hash}`         | `verification.verify` | `Auth\VerifyEmailController`                         | auth:sanctum | ✅ Ativo   |
| GET\|HEAD | `/api/user`                             | —                     | Closure — retorna `$request->user()`                 | auth:sanctum | ✅ Ativo   |
| GET\|HEAD | `/sanctum/csrf-cookie`                  | `sanctum.csrf-cookie` | `Laravel\Sanctum\CsrfCookieController@show`          | —            | ✅ Ativo   |
| POST      | `/api/register`                         | `register`            | `Auth\RegisteredUserController@store`                | —            | 🚫 Removida |

> ⚠️ O registro de usuários foi removido das rotas HTTP. Novos usuários são criados exclusivamente via **Seeder** ou **Artisan Tinker**.

### Rotas de Chamados (Tickets)

> ⚠️ Ainda não criadas. A model `Ticket` e os endpoints de chamados serão implementados em etapa futura.

| Método | URI | Nome | Controller / Action | Middleware | Status |
|--------|-----|------|---------------------|------------|--------|
| —      | —   | —    | —                   | —          | 🔲 Pendente |

---

## 🗃️ Models e Banco de Dados

### Implementadas

| Model  | Tabela  | Descrição                         | Migration          |
|--------|---------|-----------------------------------|--------------------|
| `User` | `users` | Usuário administrador do sistema. | ✅ Criada (Breeze) |

**Campos da tabela `users`:**

| Campo      | Tipo      | Restrições              |
|------------|-----------|-------------------------|
| `id`       | bigint PK | auto increment          |
| `name`     | varchar   | nullable, não único     |
| `username` | varchar   | NOT NULL, UNIQUE        |
| `email`    | varchar   | NOT NULL, UNIQUE        |
| `password` | varchar   | NOT NULL, hashed        |
| timestamps | —         | created_at, updated_at  |

### Pendentes

| Model    | Tabela    | Descrição                                    |
|----------|-----------|----------------------------------------------|
| `Ticket` | `tickets` | Chamado/lead recebido pelo site. (RF09–RF12) |

---

## 🔐 Estado da Autenticação

- [x] Laravel Breeze instalado (modo API)
- [x] Laravel Sanctum configurado
- [x] Login via `username` (não e-mail)
- [x] Rota `POST /api/register` removida do acesso público
- [x] Criação de usuários apenas via Seeder / Tinker
- [x] Seeder `AdminUserSeeder` criado (usuário: `admin`, senha via `ADMIN_PASSWORD` no `.env`)
- [x] Rota `GET /api/user` protegida por `auth:sanctum`
- [x] CSRF cookie disponível em `GET /sanctum/csrf-cookie`
- [ ] Validar se verificação de e-mail será usada (`MustVerifyEmail` na model `User`)
- [ ] Definir política de tokens Sanctum (expiração, revogação)

---

## 🔑 Exemplo de Login

```json
POST /api/login
{
    "username": "admin",
    "password": "sua_senha"
}
```

---

## 📌 Próximos Passos

- [ ] Aplicar e testar refatoração de autenticação (login por username, remoção do register)
- [ ] Rodar `php artisan db:seed --class=AdminUserSeeder`
- [ ] Criar migration e model `Ticket` com os campos necessários
- [ ] Criar endpoints de chamados (RF09–RF12) protegidos por `auth:sanctum`
- [ ] Documentar contrato da API (campos de request/response por endpoint)

---

## 📝 Convenções do Projeto

- Idioma do código: **inglês** (nomes de variáveis, métodos, rotas, campos do banco)
- Idioma das mensagens de resposta JSON: **português brasileiro**
- Todas as respostas retornam **JSON**
- Padrão de rotas: **resourceful** onde aplicável (`Route::apiResource`)
- Prefixo obrigatório: todas as rotas de negócio devem estar sob `/api/`
- Autenticação: todos os endpoints de chamados devem usar middleware `auth:sanctum`
- Autenticação stateless via **Bearer Token** (Sanctum token-based)

---

*Última atualização: autenticação refatorada — login por username, registro removido das rotas, AdminUserSeeder criado.*
