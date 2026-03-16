# Laravel Helpdesk API — Sistema de Gerenciamento de Chamados

API RESTful para gerenciamento de chamados (leads), construída com Laravel e autenticação via Sanctum.

---

## 📋 Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Pré-requisitos](#-pré-requisitos)
- [Instalação e Setup](#-instalação-e-setup)
- [Executando a Aplicação](#-executando-a-aplicação)
- [Documentação da API (Endpoints)](#-documentação-da-api-endpoints)
  - [Autenticação](#autenticação)
  - [Tickets (Chamados)](#tickets-chamados)
  - [Notas Internas](#notas-internas)
- [Troubleshooting (Resolução de Problemas)](#-troubleshooting-resolução-de-problemas)

---

## 🎯 Sobre o Projeto

Esta é uma API RESTful para controle interno de leads (síndicos de condomínios). O sistema permite que um administrador visualize, atualize, anote e arquive chamados de forma centralizada via requisições HTTP.

- **Framework:** Laravel (versão mais recente)
- **Autenticação:** Laravel Breeze (modo API) + Laravel Sanctum
- **Banco de dados:** MySQL
- **Formato de resposta:** JSON

---

## ⚙️ Pré-requisitos

Antes de começar, você precisará ter o seguinte instalado em sua máquina:
- [PHP](https://www.php.net/downloads.php) (versão 8.2 ou superior)
- [Composer](https://getcomposer.org/download/)
- [MySQL](https://dev.mysql.com/downloads/mysql/)
- [Git](https://git-scm.com/downloads)

---

## 🚀 Instalação e Setup

Siga os passos abaixo para configurar o ambiente de desenvolvimento.

### 1. Clonar o Repositório
```bash
git clone <URL_DO_REPOSITORIO>
cd laravel-helpdesk-api
```

### 2. Instalar Dependências
```bash
composer install
```

### 3. Configurar o Ambiente

Copie o arquivo de ambiente de exemplo e configure suas variáveis.

```bash
cp .env.example .env
```

Abra o arquivo `.env` e configure as seguintes variáveis:

**Conexão com o Banco de Dados:**
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk_db # Nome do seu banco de dados
DB_USERNAME=root        # Seu usuário do MySQL
DB_PASSWORD=            # Sua senha do MySQL
```

**Senha do Usuário Administrador:**
```dotenv
# Defina uma senha segura para o usuário 'admin' que será criado
ADMIN_PASSWORD=sua_senha_segura_aqui
```

### 4. Gerar a Chave da Aplicação
```bash
php artisan key:generate
```

### 5. Executar as Migrations e Seeders

Estes comandos irão criar as tabelas no banco de dados e popular com o usuário administrador padrão.

```bash
# Cria as tabelas 'users', 'tickets', 'ticket_notes', etc.
php artisan migrate

# Cria o usuário 'admin' com a senha definida no .env
php artisan db:seed --class=AdminUserSeeder
```

---

## ▶️ Executando a Aplicação

Para iniciar o servidor de desenvolvimento local do Laravel, execute:

```bash
php artisan serve
```

A API estará disponível em `http://localhost:8000`.

---

## 🗺️ Documentação da API (Endpoints)

A seguir estão os endpoints disponíveis para teste, com exemplos de requisições `curl` e respostas.

> **Nota:** Substitua `{HOST}` por `http://localhost:8000` e `{TOKEN}` pelo Bearer Token obtido no login.

### Autenticação

#### 1. Login (Obter Token)
Para interagir com as rotas protegidas, primeiro obtenha um Bearer Token.

- **Endpoint:** `POST /api/login`
- **Exemplo `curl`:**
  ```bash
  curl -X POST {HOST}/api/login \
    -H "Content-Type: application/json" \
    -d '{
      "username": "admin",
      "password": "sua_senha_segura_aqui"
    }'
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "token": "1|DyF9...",
    "token_type": "Bearer"
  }
  ```
- **Resposta de Erro (422 Unprocessable Entity):**
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "username": [
        "auth.failed"
      ]
    }
  }
  ```

#### 2. Logout (Revogar Token)
- **Endpoint:** `POST /api/logout`
- **Exemplo `curl`:**
  ```bash
  curl -X POST {HOST}/api/logout \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso:** `204 No Content`

#### 3. Obter Usuário Autenticado
- **Endpoint:** `GET /api/user`
- **Exemplo `curl`:**
  ```bash
  curl -X GET {HOST}/api/user \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "id": 1,
    "name": "Administrador",
    "username": "admin",
    "email": "admin@admin.com",
    "email_verified_at": null,
    "created_at": "2026-03-16T19:53:18.000000Z",
    "updated_at": "2026-03-16T19:53:18.000000Z"
  }
  ```

---

### Tickets (Chamados)

#### 1. Criar Ticket (Rota Pública)
- **Endpoint:** `POST /api/tickets`
- **Rate Limit:** 5 requisições por minuto por IP.
- **Exemplo `curl`:**
  ```bash
  curl -X POST {HOST}/api/tickets \
    -H "Content-Type: application/json" \
    -d '{
      "syndic_name": "João Silva",
      "phone": "11999999999",
      "condominium_name": "Residencial das Flores",
      "zip_code": "01310-100",
      "email": "joao@email.com"
    }'
  ```
- **Resposta de Sucesso (201 Created):**
  ```json
  {
    "message": "Ticket criado com sucesso.",
    "data": {
      "syndic_name": "João Silva",
      "phone": "11999999999",
      "condominium_name": "Residencial das Flores",
      "zip_code": "01310-100",
      "email": "joao@email.com",
      "status": "open",
      "updated_at": "2026-03-16T20:08:19.000000Z",
      "created_at": "2026-03-16T20:08:19.000000Z",
      "id": 1
    }
  }
  ```
- **Respostas de Erro:**
  - **422 Unprocessable Entity** (dados inválidos)
  - **429 Too Many Requests** (limite de taxa excedido)

#### 2. Listar Tickets (Rota Protegida)
- **Endpoint:** `GET /api/tickets`
- **Query Params (Opcionais):**
  - `status`: `open`, `verifying`, `finished`
  - `search`: Busca por nome do síndico, condomínio ou email.
  - `from` / `to`: Filtro por data (YYYY-MM-DD).
  - `per_page`: Itens por página (padrão 15, máx 100).
- **Exemplo `curl`:**
  ```bash
  curl -X GET "{HOST}/api/tickets?status=open&search=residencial" \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "syndic_name": "João Silva",
        "phone": "11999999999",
        "condominium_name": "Residencial das Flores",
        "zip_code": "01310-100",
        "email": "joao@email.com",
        "status": "open",
        "deleted_at": null,
        "created_at": "2026-03-16T20:08:19.000000Z",
        "updated_at": "2026-03-16T20:08:19.000000Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
  ```

#### 3. Obter Estatísticas (Rota Protegida)
- **Endpoint:** `GET /api/tickets/stats`
- **Exemplo `curl`:**
  ```bash
  curl -X GET {HOST}/api/tickets/stats \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "data": {
      "open": 12,
      "verifying": 5,
      "finished": 30,
      "total": 47
    }
  }
  ```

#### 4. Exibir um Ticket (Rota Protegida)
- **Endpoint:** `GET /api/tickets/{id}`
- **Exemplo `curl`:**
  ```bash
  curl -X GET {HOST}/api/tickets/1 \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "id": 1,
    "syndic_name": "João Silva",
    "phone": "11999999999",
    "condominium_name": "Residencial das Flores",
    "zip_code": "01310-100",
    "email": "joao@email.com",
    "status": "open",
    "deleted_at": null,
    "created_at": "2026-03-16T20:08:19.000000Z",
    "updated_at": "2026-03-16T20:08:19.000000Z",
    "notes": [
      {
        "id": 1,
        "ticket_id": 1,
        "content": "Nota de exemplo.",
        "deleted_at": null,
        "created_at": "2026-03-16T20:09:00.000000Z",
        "updated_at": "2026-03-16T20:09:00.000000Z"
      }
    ]
  }
  ```
- **Resposta de Erro (404 Not Found):** Se o ID do ticket não existir.

#### 5. Atualizar um Ticket (Rota Protegida)
- **Endpoint:** `PUT /api/tickets/{id}`
- **Exemplo `curl`:**
  ```bash
  curl -X PUT {HOST}/api/tickets/1 \
    -H "Authorization: Bearer {TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{
      "status": "verifying",
      "syndic_name": "João da Silva"
    }'
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Ticket atualizado com sucesso.",
    "data": {
      "id": 1,
      "syndic_name": "João da Silva",
      "phone": "11999999999",
      "condominium_name": "Residencial das Flores",
      "zip_code": "01310-100",
      "email": "joao@email.com",
      "status": "verifying",
      "deleted_at": null,
      "created_at": "2026-03-16T20:08:19.000000Z",
      "updated_at": "2026-03-16T20:10:00.000000Z"
    }
  }
  ```

#### 6. Deletar um Ticket (Soft Delete)
- **Endpoint:** `DELETE /api/tickets/{id}`
- **Exemplo `curl`:**
  ```bash
  curl -X DELETE {HOST}/api/tickets/1 \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Ticket removido com sucesso."
  }
  ```

#### 7. Deletar um Ticket Permanentemente (Hard Delete)
- **Endpoint:** `DELETE /api/tickets/{id}/force`
- **Exemplo `curl`:**
  ```bash
  curl -X DELETE {HOST}/api/tickets/1/force \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Ticket removido com sucesso."
  }
  ```

---

### Notas Internas

#### 1. Criar Nota em um Ticket
- **Endpoint:** `POST /api/tickets/{id}/notes`
- **Exemplo `curl`:**
  ```bash
  curl -X POST {HOST}/api/tickets/1/notes \
    -H "Authorization: Bearer {TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{
      "content": "Cliente entrou em contato por telefone."
    }'
  ```
- **Resposta de Sucesso (201 Created):**
  ```json
  {
    "message": "Nota criada com sucesso.",
    "data": {
      "ticket_id": 1,
      "content": "Cliente entrou em contato por telefone.",
      "updated_at": "2026-03-16T20:11:00.000000Z",
      "created_at": "2026-03-16T20:11:00.000000Z",
      "id": 2
    }
  }
  ```

#### 2. Editar uma Nota
- **Endpoint:** `PUT /api/tickets/{id}/notes/{noteId}`
- **Exemplo `curl`:**
  ```bash
  curl -X PUT {HOST}/api/tickets/1/notes/1 \
    -H "Authorization: Bearer {TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{
      "content": "Conteúdo atualizado da nota."
    }'
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Nota atualizada com sucesso.",
    "data": {
      "id": 1,
      "ticket_id": 1,
      "content": "Conteúdo atualizado da nota.",
      "deleted_at": null,
      "created_at": "2026-03-16T20:09:00.000000Z",
      "updated_at": "2026-03-16T20:12:00.000000Z"
    }
  }
  ```
- **Resposta de Erro (404 Not Found):** Se a nota não pertencer ao ticket.

#### 3. Deletar uma Nota (Soft Delete)
- **Endpoint:** `DELETE /api/tickets/{id}/notes/{noteId}`
- **Exemplo `curl`:**
  ```bash
  curl -X DELETE {HOST}/api/tickets/1/notes/1 \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Nota removida com sucesso."
  }
  ```

#### 4. Deletar uma Nota Permanentemente (Hard Delete)
- **Endpoint:** `DELETE /api/tickets/{id}/notes/{noteId}/force`
- **Exemplo `curl`:**
  ```bash
  curl -X DELETE {HOST}/api/tickets/1/notes/1/force \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Nota removida com sucesso."
  }
  ```

---

## 🆘 Troubleshooting (Resolução de Problemas)

### 1. Erro de autenticação (`auth.failed`)
- **Causa:** Credenciais incorretas ou o seeder do admin não foi executado.
- **Solução:**
  1. Verifique se a senha no `.env` (`ADMIN_PASSWORD`) está correta.
  2. Execute `php artisan db:seed --class=AdminUserSeeder` novamente.

### 2. Erro `Unauthenticated`
- **Causa:** Token não enviado, inválido ou expirado.
- **Solução:**
  1. Certifique-se de enviar o header `Authorization: Bearer {SEU_TOKEN}`.
  2. Faça login novamente para obter um novo token.

### 3. Erro `Table 'tickets' doesn't exist`
- **Causa:** As migrations não foram executadas.
- **Solução:** Execute `php artisan migrate`.

### 4. Erro `429 Too Many Requests`
- **Causa:** Você excedeu o limite de 5 requisições por minuto na rota `POST /api/tickets`.
- **Solução:** Aguarde um minuto antes de tentar novamente.

Para mais detalhes, consulte os arquivos de documentação gerados no projeto.
