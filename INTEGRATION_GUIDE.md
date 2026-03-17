# 🔗 Guia de Integração Frontend — Laravel Helpdesk API

Exemplos práticos de integração da API em aplicações frontend.

---

## 📌 Índice

1. [Configuração Básica](#configuração-básica)
2. [Autenticação](#autenticação)
3. [Requisições Autenticadas](#requisições-autenticadas)
4. [Keep-Alive / Renovação de Token](#keep-alive--renovação-de-token)
5. [Exemplos por Framework](#exemplos-por-framework)
6. [Tratamento de Erros](#tratamento-de-erros)
7. [Interceptors](#interceptors)

---

## 🔧 Configuração Básica

### URL Base da API
```javascript
const API_BASE_URL = 'http://localhost:8000/api';
const TOKEN_KEY = 'authToken';
```

### Headers Padrão
```javascript
const defaultHeaders = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
};
```

---

## 🔐 Autenticação

### 1. Fazer Login

#### JavaScript Vanilla
```javascript
async function login(username, password) {
  try {
    const response = await fetch(`${API_BASE_URL}/login`, {
      method: 'POST',
      headers: defaultHeaders,
      body: JSON.stringify({ username, password })
    });

    if (!response.ok) {
      throw new Error('Login falhou');
    }

    const data = await response.json();
    
    // Salvar token
    localStorage.setItem(TOKEN_KEY, data.token);
    
    return {
      success: true,
      token: data.token,
      message: data.message
    };
  } catch (error) {
    return {
      success: false,
      error: error.message
    };
  }
}

// Usar
const result = await login('admin', 'password123');
if (result.success) {
  console.log('✅ Login realizado!', result.token);
} else {
  console.error('❌ Erro:', result.error);
}
```

#### Axios
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: API_BASE_URL
});

async function login(username, password) {
  try {
    const response = await api.post('/login', {
      username,
      password
    });

    localStorage.setItem(TOKEN_KEY, response.data.token);
    
    return {
      success: true,
      token: response.data.token
    };
  } catch (error) {
    return {
      success: false,
      error: error.response?.data?.message || error.message
    };
  }
}
```

---

### 2. Fazer Logout

#### JavaScript Vanilla
```javascript
async function logout() {
  const token = localStorage.getItem(TOKEN_KEY);
  
  try {
    await fetch(`${API_BASE_URL}/logout`, {
      method: 'POST',
      headers: {
        ...defaultHeaders,
        'Authorization': `Bearer ${token}`
      }
    });

    localStorage.removeItem(TOKEN_KEY);
    console.log('✅ Logout realizado');
    
    // Redirecionar para login
    window.location.href = '/login';
  } catch (error) {
    console.error('❌ Erro ao fazer logout:', error);
  }
}
```

---

## 📡 Requisições Autenticadas

### Obter Token do Storage
```javascript
function getToken() {
  return localStorage.getItem(TOKEN_KEY);
}

function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token);
}

function removeToken() {
  localStorage.removeItem(TOKEN_KEY);
}
```

### Fazer Requisição Autenticada

#### JavaScript Vanilla
```javascript
async function apiRequest(url, method = 'GET', body = null) {
  const token = getToken();
  
  if (!token) {
    throw new Error('Token não encontrado. Faça login.');
  }

  const options = {
    method,
    headers: {
      ...defaultHeaders,
      'Authorization': `Bearer ${token}`
    }
  };

  if (body) {
    options.body = JSON.stringify(body);
  }

  const response = await fetch(`${API_BASE_URL}${url}`, options);

  if (response.status === 401) {
    // Token expirado
    removeToken();
    window.location.href = '/login';
    throw new Error('Sessão expirada');
  }

  if (!response.ok) {
    throw new Error(`Erro ${response.status}: ${response.statusText}`);
  }

  return await response.json();
}

// Usar
const tickets = await apiRequest('/tickets');
const ticket = await apiRequest('/tickets/1');
await apiRequest('/tickets/1', 'PUT', { status: 'verifying' });
```

#### Axios
```javascript
const api = axios.create({
  baseURL: API_BASE_URL
});

// Adicionar token automaticamente
api.interceptors.request.use((config) => {
  const token = getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Usar
const tickets = await api.get('/tickets');
const ticket = await api.get('/tickets/1');
await api.put('/tickets/1', { status: 'verifying' });
```

---

## 🔄 Keep-Alive / Renovação de Token

### Renovar Token Automaticamente

#### JavaScript Vanilla
```javascript
class AuthManager {
  constructor() {
    this.token = getToken();
    this.refreshInterval = null;
    this.REFRESH_TIME = 23 * 60 * 60 * 1000; // 23 horas
    this.REFRESH_WARNING = 30 * 60 * 1000;   // Avisar 30 min antes
  }

  // Iniciar renovação automática
  startAutoRefresh() {
    // Renovar imediatamente se token existe
    if (this.token) {
      this.refreshToken();
    }

    // Renovar periodicamente
    this.refreshInterval = setInterval(() => {
      if (this.token) {
        this.refreshToken();
      }
    }, this.REFRESH_TIME);
  }

  // Renovar token
  async refreshToken() {
    const token = getToken();
    
    if (!token) return;

    try {
      const response = await fetch(`${API_BASE_URL}/refresh-token`, {
        method: 'POST',
        headers: {
          ...defaultHeaders,
          'Authorization': `Bearer ${token}`
        }
      });

      if (!response.ok) {
        throw new Error('Falha ao renovar token');
      }

      const data = await response.json();
      const newToken = data.token;

      // Atualizar token
      setToken(newToken);
      this.token = newToken;

      // Notificar outros abas/componentes
      window.dispatchEvent(new CustomEvent('tokenRefreshed', {
        detail: { token: newToken }
      }));

      console.log('✅ Token renovado com sucesso');
    } catch (error) {
      console.error('❌ Erro ao renovar token:', error);
      this.logout();
    }
  }

  // Parar renovação automática
  stopAutoRefresh() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
  }

  // Logout
  logout() {
    this.stopAutoRefresh();
    removeToken();
    this.token = null;
    window.location.href = '/login';
  }

  // Verificar se está autenticado
  isAuthenticated() {
    return !!this.token;
  }
}

// Usar
const auth = new AuthManager();
auth.startAutoRefresh();

// Sincronizar com outros abas
window.addEventListener('tokenRefreshed', (event) => {
  const newToken = event.detail.token;
  console.log('Novo token em outro aba:', newToken);
});

// Ao fazer logout
auth.logout();
```

---

## 📚 Exemplos por Framework

### ⚛️ React + Hooks

#### Componente de Login
```javascript
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

export default function LoginPage() {
  const navigate = useNavigate();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const response = await fetch('http://localhost:8000/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username, password })
      });

      if (!response.ok) {
        const data = await response.json();
        throw new Error(data.message || 'Erro ao fazer login');
      }

      const data = await response.json();
      localStorage.setItem('authToken', data.token);
      
      navigate('/dashboard');
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleLogin}>
      <input
        type="text"
        placeholder="Username"
        value={username}
        onChange={(e) => setUsername(e.target.value)}
        required
      />
      <input
        type="password"
        placeholder="Senha"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        required
      />
      {error && <p style={{ color: 'red' }}>{error}</p>}
      <button type="submit" disabled={loading}>
        {loading ? 'Conectando...' : 'Login'}
      </button>
    </form>
  );
}
```

#### Hook Personalizado
```javascript
// hooks/useAuth.js
import { useState, useEffect } from 'react';

const API_BASE_URL = 'http://localhost:8000/api';

export function useAuth() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [token, setTokenState] = useState(() => 
    localStorage.getItem('authToken')
  );

  useEffect(() => {
    // Verificar autenticação ao carregar
    if (token) {
      fetchUser();
    } else {
      setLoading(false);
    }
  }, [token]);

  // Buscar dados do usuário
  async function fetchUser() {
    try {
      const response = await fetch(`${API_BASE_URL}/user`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      if (!response.ok) throw new Error('Falha ao carregar usuário');
      
      const data = await response.json();
      setUser(data);
    } catch (error) {
      console.error('Erro:', error);
      logout();
    } finally {
      setLoading(false);
    }
  }

  // Login
  async function login(username, password) {
    try {
      const response = await fetch(`${API_BASE_URL}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
      });

      if (!response.ok) {
        throw new Error('Credenciais inválidas');
      }

      const data = await response.json();
      const newToken = data.token;
      
      localStorage.setItem('authToken', newToken);
      setTokenState(newToken);
      
      return { success: true };
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  // Logout
  async function logout() {
    try {
      await fetch(`${API_BASE_URL}/logout`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` }
      });
    } catch (error) {
      console.error('Erro ao fazer logout:', error);
    } finally {
      localStorage.removeItem('authToken');
      setTokenState(null);
      setUser(null);
    }
  }

  // Renovar token
  async function refreshToken() {
    try {
      const response = await fetch(`${API_BASE_URL}/refresh-token`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` }
      });

      if (!response.ok) throw new Error('Falha ao renovar token');

      const data = await response.json();
      localStorage.setItem('authToken', data.token);
      setTokenState(data.token);
      
      return true;
    } catch (error) {
      logout();
      return false;
    }
  }

  return {
    user,
    loading,
    token,
    isAuthenticated: !!token && !!user,
    login,
    logout,
    refreshToken
  };
}

// Usar em componente
function App() {
  const { isAuthenticated, loading } = useAuth();

  if (loading) return <div>Carregando...</div>;

  return isAuthenticated ? <Dashboard /> : <LoginPage />;
}
```

#### Componente com Requisição Autenticada
```javascript
import { useState, useEffect } from 'react';
import { useAuth } from './hooks/useAuth';

export default function TicketsPage() {
  const { token } = useAuth();
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (token) {
      fetchTickets();
    }
  }, [token]);

  async function fetchTickets() {
    try {
      const response = await fetch('http://localhost:8000/api/tickets', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      if (!response.ok) throw new Error('Falha ao carregar tickets');

      const data = await response.json();
      setTickets(data.data);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  if (loading) return <div>Carregando...</div>;
  if (error) return <div style={{ color: 'red' }}>{error}</div>;

  return (
    <div>
      <h1>Tickets ({tickets.length})</h1>
      <ul>
        {tickets.map(ticket => (
          <li key={ticket.id}>
            <strong>{ticket.syndic_name}</strong> - {ticket.status}
          </li>
        ))}
      </ul>
    </div>
  );
}
```

---

### 🖖 Vue 3 + Composables

#### Composable de Autenticação
```javascript
// composables/useAuth.js
import { ref, computed } from 'vue';

const API_BASE_URL = 'http://localhost:8000/api';
const token = ref(localStorage.getItem('authToken'));
const user = ref(null);
const loading = ref(false);

export function useAuth() {
  const isAuthenticated = computed(() => !!token.value && !!user.value);

  async function login(username, password) {
    loading.value = true;
    try {
      const response = await fetch(`${API_BASE_URL}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
      });

      if (!response.ok) {
        const data = await response.json();
        throw new Error(data.message || 'Login falhou');
      }

      const data = await response.json();
      token.value = data.token;
      localStorage.setItem('authToken', token.value);

      return { success: true };
    } catch (error) {
      return { success: false, error: error.message };
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    try {
      await fetch(`${API_BASE_URL}/logout`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token.value}` }
      });
    } catch (error) {
      console.error('Erro ao fazer logout:', error);
    } finally {
      token.value = null;
      user.value = null;
      localStorage.removeItem('authToken');
    }
  }

  async function fetchUser() {
    try {
      const response = await fetch(`${API_BASE_URL}/user`, {
        headers: { 'Authorization': `Bearer ${token.value}` }
      });

      if (!response.ok) throw new Error('Falha ao carregar usuário');

      const data = await response.json();
      user.value = data;
    } catch (error) {
      console.error('Erro:', error);
      logout();
    }
  }

  return {
    token,
    user,
    loading,
    isAuthenticated,
    login,
    logout,
    fetchUser
  };
}
```

#### Componente Vue
```vue
<template>
  <div>
    <h1>Tickets</h1>
    <button @click="fetchTickets" :disabled="loading">
      {{ loading ? 'Carregando...' : 'Carregar Tickets' }}
    </button>
    
    <ul>
      <li v-for="ticket in tickets" :key="ticket.id">
        {{ ticket.syndic_name }} - {{ ticket.status }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';

const { token } = useAuth();
const tickets = ref([]);
const loading = ref(false);

const API_BASE_URL = 'http://localhost:8000/api';

async function fetchTickets() {
  loading.value = true;
  try {
    const response = await fetch(`${API_BASE_URL}/tickets`, {
      headers: {
        'Authorization': `Bearer ${token.value}`
      }
    });

    if (!response.ok) throw new Error('Falha ao carregar');

    const data = await response.json();
    tickets.value = data.data;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchTickets();
});
</script>
```

---

## ⚠️ Tratamento de Erros

### Padrão de Resposta de Erro
```javascript
// Erro de validação (422)
{
  "message": "Validação falhou",
  "errors": {
    "email": ["Email inválido"],
    "password": ["Senha muito curta"]
  }
}

// Erro de autenticação (401)
{
  "message": "Token inválido ou expirado"
}

// Erro de não autorizado (403)
{
  "message": "Você não tem permissão"
}

// Erro de servidor (500)
{
  "message": "Erro interno do servidor"
}
```

### Tratamento Centralizado
```javascript
function handleApiError(error, response) {
  if (error.response?.status === 401) {
    // Token expirado
    localStorage.removeItem('authToken');
    window.location.href = '/login';
    return 'Sessão expirada. Faça login novamente.';
  }

  if (error.response?.status === 422) {
    // Erro de validação
    const errors = error.response.data.errors;
    return Object.values(errors).flat().join(', ');
  }

  if (error.response?.status === 429) {
    // Rate limit
    return 'Muitas requisições. Aguarde um momento.';
  }

  return error.response?.data?.message || error.message || 'Erro desconhecido';
}
```

---

## 🔌 Interceptors (Axios)

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api'
});

// Interceptor de Requisição
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('authToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Interceptor de Resposta
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    // Se 401 e ainda não tentou refresh
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const response = await api.post('/refresh-token');
        const newToken = response.data.token;

        localStorage.setItem('authToken', newToken);
        
        // Repetir requisição original com novo token
        originalRequest.headers.Authorization = `Bearer ${newToken}`;
        return api(originalRequest);
      } catch (refreshError) {
        // Logout
        localStorage.removeItem('authToken');
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export default api;
```

---

## 📋 Checklist de Integração

- [ ] Configurar URL base da API
- [ ] Implementar login e salvar token
- [ ] Adicionar token em requisições autenticadas
- [ ] Implementar logout
- [ ] Configurar renovação automática de token
- [ ] Tratar erros 401 (token expirado)
- [ ] Sincronizar múltiplas abas
- [ ] Testar fluxo completo
- [ ] Documentar para o time

---

**Última atualização:** Março 2026

