# 🎫 Laravel Helpdesk API — Sistema de Gerenciamento de Chamados

API RESTful para gerenciamento centralizado de chamados (leads) de síndicos. Construída com **Laravel 11** e autenticação segura via **Laravel Sanctum** com tokens de longa duração.

**Status:** ✅ Produção | **Versão:** 1.0.0 | **Última Atualização:** Março 2026

---

## 📋 Índice Rápido

1. [Visão Geral](#visão-geral)
2. [Stack Tecnológico](#-stack-tecnológico)
3. [Pré-requisitos](#-pré-requisitos)
4. [Instalação Rápida](#-instalação-rápida)
5. [Executando](#-executando-a-aplicação)
6. [Autenticação](#-autenticação)
7. [Endpoints da API](#-endpoints-da-api)
   - [Login & Autorização](#autenticação)
   - [Perfil do Usuário](#gerenciamento-de-usuário-perfil-e-conta)
   - [Tickets](#tickets-chamados)
   - [Notas Internas](#notas-internas)
8. [Keep-Alive (Manter Conectado)](#-mantendo-o-usuário-conectado-keep-alive)
9. [Troubleshooting](#-troubleshooting-resolução-de-problemas)
10. [FAQ](#-faq)
11. [Contato & Suporte](#-contato--suporte)


---

## 👁️ Visão Geral

Sistema completo de gerenciamento de chamados técnicos, permitindo:

✅ **Criar** novos chamados (públicos - sem autenticação)  
✅ **Gerenciar** chamados (alterar status, atualizar dados)  
✅ **Adicionar notas** internas para rastrear progresso  
✅ **Autenticação segura** com JWT/Sanctum  
✅ **Rate limiting** para proteção contra abuso  
✅ **Soft delete** para recuperação de dados  
✅ **Soft delete permanente** (hard delete) quando necessário  

---

## 🛠️ Stack Tecnológico

| Componente | Tecnologia | Versão |
|-----------|-----------|--------|
| **Framework** | Laravel | 11.x |
| **Autenticação** | Laravel Sanctum | Nativa |
| **Banco de Dados** | MySQL | 8.0+ |
| **PHP** | PHP | 8.2+ |
| **API** | RESTful | JSON |
| **Rate Limiting** | Laravel | Nativo |
| **Validação** | Laravel Rules | Nativa |



API centralizada para gerenciamento de chamados técnicos de síndicos. Administradores podem visualizar, atualizar, anotare arquivar chamados via requisições HTTP. Suporta criação pública de tickets com rate limiting e autenticação segura para gerenciamento.

---

## ⚙️ Pré-requisitos

Antes de começar, você precisará ter o seguinte instalado em sua máquina:
- [PHP](https://www.php.net/downloads.php) (versão 8.2 ou superior)
- [Composer](https://getcomposer.org/download/)
- [MySQL](https://dev.mysql.com/downloads/mysql/)
- [Git](https://git-scm.com/downloads)

---

## 🚀 Instalação Rápida

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

### Gerenciamento de Usuário (Perfil e Conta)

#### 1. Obter Perfil do Usuário
- **Endpoint:** `GET /api/profile`
- **Exemplo `curl`:**
  ```bash
  curl -X GET {HOST}/api/profile \
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

#### 2. Atualizar Perfil do Usuário
- **Endpoint:** `PUT /api/profile`
- **Campos atualizáveis:**
  - `name`: Nome completo
  - `email`: Endereço de email
  - `username`: Nome de usuário
- **Exemplo `curl`:**
  ```bash
  curl -X PUT {HOST}/api/profile \
    -H "Authorization: Bearer {TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{
      "name": "João Administrador",
      "email": "joao@admin.com",
      "username": "joao_admin"
    }'
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Perfil atualizado com sucesso.",
    "data": {
      "id": 1,
      "name": "João Administrador",
      "username": "joao_admin",
      "email": "joao@admin.com",
      "email_verified_at": null,
      "created_at": "2026-03-16T19:53:18.000000Z",
      "updated_at": "2026-03-16T20:15:00.000000Z"
    }
  }
  ```
- **Respostas de Erro:**
  - **422 Unprocessable Entity** (email ou username já existem)

#### 3. Alterar Senha
- **Endpoint:** `POST /api/change-password`
- **Campos Obrigatórios:**
  - `current_password`: Senha atual do usuário
  - `password`: Nova senha
  - `password_confirmation`: Confirmação da nova senha (deve ser igual a `password`)
- **Requisitos da Nova Senha:**
  - Mínimo 8 caracteres
  - Pelo menos 1 letra maiúscula
  - Pelo menos 1 número
  - Pelo menos 1 caractere especial
- **Exemplo `curl`:**
  ```bash
  curl -X POST {HOST}/api/change-password \
    -H "Authorization: Bearer {TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{
      "current_password": "senha_atual_123",
      "password": "NovaSenh@123",
      "password_confirmation": "NovaSenh@123"
    }'
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Senha alterada com sucesso."
  }
  ```
- **Respostas de Erro:**
  - **422 Unprocessable Entity** (senha atual incorreta ou validação falha)

#### 4. Renovar Token (Manter Conectado)
Esta rota permite manter o usuário conectado renovando o token de acesso.

- **Endpoint:** `POST /api/refresh-token`
- **Como Funciona:**
  - Cria um novo token de acesso
  - O novo token tem a mesma duração que um token normal
  - Pode ser chamado periodicamente para evitar expiração
  - **Ideal para usar a cada 24 horas ou quando o token está próximo de expirar**
- **Exemplo `curl`:**
  ```bash
  curl -X POST {HOST}/api/refresh-token \
    -H "Authorization: Bearer {TOKEN}"
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Token renovado com sucesso.",
    "token": "2|NeW...",
    "token_type": "Bearer"
  }
  ```

**Implementação no Frontend (React/Vue):**
```javascript
// Exemplo com JavaScript
async function refreshToken(currentToken) {
  const response = await fetch('/api/refresh-token', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${currentToken}`,
      'Content-Type': 'application/json'
    }
  });
  
  if (response.ok) {
    const data = await response.json();
    // Salvar o novo token
    localStorage.setItem('token', data.token);
    return data.token;
  }
}

// Chamar a cada 24 horas ou periodicamente
setInterval(() => {
  const token = localStorage.getItem('token');
  if (token) {
    refreshToken(token);
  }
}, 24 * 60 * 60 * 1000); // 24 horas
```

#### 5. Deletar Conta
- **Endpoint:** `DELETE /api/account`
- **Atenção:** Esta ação é permanente e não pode ser desfeita.
- **Exemplo `curl`:**
  ```bash
  curl -X DELETE {HOST}/api/account \
    -H "Authorization: Bearer {TOKEN}" \
    -H "Content-Type: application/json" \
    -d '{
      "password": "sua_senha_atual"
    }'
  ```
- **Resposta de Sucesso (200 OK):**
  ```json
  {
    "message": "Conta deletada com sucesso."
  }
  ```
- **Resposta de Erro (422):**
  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "password": [
        "A senha está incorreta."
      ]
    }
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

## 🔄 Mantendo o Usuário Conectado (Keep-Alive)

Para manter uma sessão ativa e evitar que o token expire, use o endpoint `/api/refresh-token`. Este endpoint gera um novo token de acesso sem exigir que o usuário faça login novamente.

### Fluxo de Renovação

1. **Usuário faz login** → Recebe um token
2. **Token é armazenado** → No localStorage/sessionStorage
3. **Periodicamente** → Chamar `/api/refresh-token` para renovar
4. **Novo token** → Substitui o anterior
5. **Sessão continua ativa** → Até o logout

### Exemplos de Implementação

#### React/TypeScript
```javascript
// hooks/useAuthToken.ts
import { useEffect, useRef } from 'react';

export const useAuthToken = (token: string | null) => {
  const refreshIntervalRef = useRef<NodeJS.Timeout | null>(null);

  useEffect(() => {
    if (!token) return;

    // Renovar token a cada 23 horas (token dura 24 horas)
    const REFRESH_INTERVAL = 23 * 60 * 60 * 1000;

    const refreshToken = async () => {
      try {
        const response = await fetch('/api/refresh-token', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
        });

        if (!response.ok) {
          console.error('Falha ao renovar token');
          return;
        }

        const data = await response.json();
        const newToken = data.token;

        // Atualizar token no localStorage
        localStorage.setItem('authToken', newToken);
        
        // Disparar evento para componentes atualizarem
        window.dispatchEvent(new CustomEvent('tokenRefreshed', { detail: { token: newToken } }));
      } catch (error) {
        console.error('Erro ao renovar token:', error);
      }
    };

    // Chamar imediatamente e depois a cada intervalo
    refreshToken();
    refreshIntervalRef.current = setInterval(refreshToken, REFRESH_INTERVAL);

    return () => {
      if (refreshIntervalRef.current) {
        clearInterval(refreshIntervalRef.current);
      }
    };
  }, [token]);
};

// Usar no componente App
import { useAuthToken } from './hooks/useAuthToken';

export function App() {
  const [token, setToken] = useState(() => 
    localStorage.getItem('authToken')
  );

  useAuthToken(token);

  return (
    // ... seu app
  );
}
```

#### Vue 3/Composables
```javascript
// composables/useAuthToken.js
import { ref, onMounted, onUnmounted } from 'vue';

export const useAuthToken = (token) => {
  const refreshIntervalRef = ref(null);

  onMounted(() => {
    if (!token?.value) return;

    const REFRESH_INTERVAL = 23 * 60 * 60 * 1000; // 23 horas

    const refreshToken = async () => {
      try {
        const response = await fetch('/api/refresh-token', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token.value}`,
            'Content-Type': 'application/json',
          },
        });

        if (!response.ok) {
          console.error('Falha ao renovar token');
          return;
        }

        const data = await response.json();
        token.value = data.token;
        localStorage.setItem('authToken', data.token);
      } catch (error) {
        console.error('Erro ao renovar token:', error);
      }
    };

    // Renovar imediatamente e depois periodicamente
    refreshToken();
    refreshIntervalRef.value = setInterval(refreshToken, REFRESH_INTERVAL);
  });

  onUnmounted(() => {
    if (refreshIntervalRef.value) {
      clearInterval(refreshIntervalRef.value);
    }
  });
};

// App.vue
<script setup>
import { ref } from 'vue';
import { useAuthToken } from '@/composables/useAuthToken';

const token = ref(localStorage.getItem('authToken'));
useAuthToken(token);
</script>
```

#### JavaScript Vanilla
```javascript
// auth-manager.js
class AuthManager {
  constructor() {
    this.token = localStorage.getItem('authToken');
    this.refreshInterval = null;
    this.REFRESH_TIME = 23 * 60 * 60 * 1000; // 23 horas
  }

  async refreshToken() {
    if (!this.token) return;

    try {
      const response = await fetch('/api/refresh-token', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Content-Type': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Falha ao renovar token');
      }

      const data = await response.json();
      this.token = data.token;
      
      // Atualizar localStorage
      localStorage.setItem('authToken', this.token);
      
      // Notificar outros abas/componentes
      window.dispatchEvent(new CustomEvent('tokenUpdated', {
        detail: { token: this.token }
      }));

      console.log('✅ Token renovado com sucesso');
    } catch (error) {
      console.error('❌ Erro ao renovar token:', error);
      this.logout();
    }
  }

  startAutoRefresh() {
    // Renovar imediatamente
    this.refreshToken();
    
    // Depois renovar periodicamente
    this.refreshInterval = setInterval(() => {
      this.refreshToken();
    }, this.REFRESH_TIME);
  }

  stopAutoRefresh() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
  }

  logout() {
    this.stopAutoRefresh();
    localStorage.removeItem('authToken');
    this.token = null;
    // Redirecionar para login
    window.location.href = '/login';
  }

  getToken() {
    return this.token;
  }
}

// Uso
const auth = new AuthManager();
auth.startAutoRefresh();

// Recuperar token para requisições
const token = auth.getToken();

// Parar renovação
// auth.stopAutoRefresh();
```

### Requisições com Token Renovado

Após renovar o token, certifique-se de usar o novo token em todas as requisições:

```javascript
// axios/fetch com interceptor (recomendado)
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('authToken');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Usar em toda a aplicação
api.get('/profile'); // Usa o token mais recente automaticamente
```

### Quando Renovar o Token

- **A cada 24 horas** - Recomendado para aplicações de longa execução
- **Ao abrir a aplicação** - Garantir sessão válida
- **Antes de expirar** - Se souber a data de expiração
- **Manual** - Permitir que o usuário "refresque" manualmente

### Checklist de Implementação

- [ ] Implementar hook/composable para renovação automática
- [ ] Armazenar token em localStorage/sessionStorage
- [ ] Configurar renovação a cada 23 horas
- [ ] Usar interceptors para adicionar token automaticamente
- [ ] Disparar eventos para sincronizar múltiplas abas
- [ ] Limpar interval ao fazer logout
- [ ] Testar renovação com requisições protegidas

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

---

## ❓ FAQ

### P: Qual é o tempo de expiração do token?
**R:** Tokens de acesso são válidos por **24 horas**. Use o endpoint `/api/refresh-token` para renovar antes de expirar.

### P: Posso criar tickets sem autenticação?
**R:** Sim! O endpoint `POST /api/tickets` é público e requer apenas os dados do formulário. A autenticação é opcional.

### P: Quantas tentativas de login posso fazer?
**R:** Não há limite específico para login, mas há rate limiting de **5 requisições por minuto por IP** no endpoint de criação de tickets.

### P: Como sincronizar múltiplas abas/dispositivos?
**R:** Implemente listeners para o evento `tokenRefreshed` ou `tokenUpdated`:
```javascript
window.addEventListener('tokenRefreshed', (event) => {
  const newToken = event.detail.token;
  localStorage.setItem('authToken', newToken);
  // Recarregar dados se necessário
});
```

### P: Posso deletar um ticket permanentemente?
**R:** Sim, existem dois tipos de delete:
- **Soft Delete** (`DELETE /api/tickets/{id}`) - Marca como deletado, pode ser recuperado
- **Hard Delete** (`DELETE /api/tickets/{id}/force`) - Remove permanentemente do banco

### P: Qual é o tamanho máximo de uma nota?
**R:** Não há limite imposto, mas é recomendado manter notas concisas (até 2000 caracteres).

### P: Preciso fazer login a cada vez que reabro a aplicação?
**R:** Não! Use `localStorage.getItem('authToken')` para recuperar o token salvo. Se estiver expirado, use `/api/refresh-token`.

### P: Como filtrar tickets por data?
**R:** Use os query params `from` e `to`:
```
GET /api/tickets?from=2026-03-01&to=2026-03-31
```

### P: Posso atualizar apenas alguns campos de um ticket?
**R:** Sim! O endpoint `PUT /api/tickets/{id}` aceita atualizações parciais. Envie apenas os campos que quer alterar.

### P: Como saber quantos tickets existem em cada status?
**R:** Use o endpoint `GET /api/tickets/stats` que retorna um resumo dos tickets por status.

---

## 📞 Contato & Suporte

### Reportar um Bug
Se encontrar um problema, abra uma **issue** no repositório com:
- Descrição clara do problema
- Passos para reproduzir
- Mensagem de erro (se houver)
- Versão do PHP e Laravel

### Solicitar uma Feature
Tenha uma ideia para melhorar a API? Crie uma **discussão** ou **issue** com:
- Descrição da feature
- Caso de uso
- Exemplos de implementação (opcional)

### Documentação Interna
- 📄 **Modelos:** `/app/Models/`
- 🎯 **Controllers:** `/app/Http/Controllers/`
- 📋 **Requests:** `/app/Http/Requests/`
- 🔀 **Routes:** `/routes/api.php`
- 🌱 **Migrations:** `/database/migrations/`
- 🎲 **Seeders:** `/database/seeders/`

### Links Úteis
- 🔗 [Documentação Laravel](https://laravel.com/docs)
- 🔗 [Laravel Sanctum](https://laravel.com/docs/sanctum)
- 🔗 [REST API Best Practices](https://restfulapi.net/)
- 🔗 [HTTP Status Codes](https://httpwg.org/specs/rfc7231.html#status.codes)

---

**Desenvolvido com ❤️ usando Laravel**  
**Última atualização:** Março de 2026

