# Tarefa: Refatoração da Autenticação

## Contexto

Projeto Laravel API RESTful com Breeze (modo API) + Sanctum.  
Sem frontend — todas as respostas devem ser JSON.  
A rota `POST /api/register` atualmente é pública e deve ser **removida do acesso externo**.  
O login atualmente usa **e-mail**, e deve ser alterado para usar **username**.

---

## O que deve ser feito

### 1. Remover o registro como rota pública

- Remova (ou comente) a rota `POST /api/register` do arquivo de rotas (`routes/api.php` ou onde estiver registrada pelo Breeze).
- O registro de novos usuários **não deve ser acessível por nenhuma rota HTTP pública ou autenticada**.
- A criação de usuários será feita exclusivamente via **Seeder** ou **Artisan Tinker**.

---

### 2. Adicionar o campo `username` à tabela `users`

Altere a migration de criação da tabela `users` **ou** crie uma nova migration, conforme o estado atual do banco:

**Campos esperados na tabela `users` ao final:**

| Campo        | Tipo         | Restrições                        |
|--------------|--------------|-----------------------------------|
| `id`         | bigint PK    | auto increment                    |
| `name`       | varchar      | nullable (nome completo, não único)|
| `username`   | varchar      | NOT NULL, UNIQUE                  |
| `email`      | varchar      | NOT NULL, UNIQUE                  |
| `password`   | varchar      | NOT NULL, hashed                  |
| timestamps   | —            | `created_at`, `updated_at`        |

> Se a migration original ainda não foi executada em produção, edite-a diretamente.
> Se já foi executada, crie uma nova migration: `add_username_to_users_table`.

---

### 3. Atualizar a model `User`

No arquivo `app/Models/User.php`:

- Adicione `username` ao array `$fillable`.
- Mantenha `email` no `$fillable` (ainda é obrigatório e único).
- `name` deve permanecer no `$fillable` como opcional.
- Não remova `email` da model — ele ainda é armazenado e deve ser único.

---

### 4. Alterar o login para usar `username`

No arquivo `app/Http/Requests/Auth/LoginRequest.php`:

- Altere a validação do campo de login de `email` para `username`.
- A regra de validação deve ser: `required|string`.
- No método `authenticate()`, altere a tentativa de autenticação:
    - **Antes:** `Auth::attempt(['email' => $this->email, 'password' => $this->password])`
    - **Depois:** `Auth::attempt(['username' => $this->username, 'password' => $this->password])`

No arquivo `app/Http/Controllers/Auth/AuthenticatedSessionController.php`:

- Confirme que o controller usa o `LoginRequest` — nenhuma alteração direta deve ser necessária além da que foi feita no Request.

**Exemplo do body esperado no `POST /api/login` após a alteração:**
```json
{
    "username": "admin",
    "password": "sua_senha"
}
```

---

### 5. Criar o Seeder do usuário administrador

Crie o arquivo `database/seeders/AdminUserSeeder.php`:

**Requisitos do seeder:**
- Usar `firstOrCreate` para evitar duplicação ao rodar múltiplas vezes.
- O campo `username` deve ser `admin`.
- O campo `email` deve ser `admin@admin.com`.
- O campo `name` pode ser `Administrador`.
- A senha deve ser lida da variável de ambiente `ADMIN_PASSWORD` com fallback para `password`.
- A senha deve ser armazenada com `bcrypt()` ou `Hash::make()`.

**Exemplo de implementação esperada:**
```php
User::firstOrCreate(
    ['username' => 'admin'],
    [
        'name'     => 'Administrador',
        'email'    => 'admin@admin.com',
        'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
    ]
);
```

Registre o `AdminUserSeeder` no `DatabaseSeeder.php`:
```php
$this->call([
    AdminUserSeeder::class,
]);
```

---

### 6. Atualizar o `.env.example`

Adicione a linha abaixo ao `.env.example` para documentar a variável esperada:

```
ADMIN_PASSWORD=
```

---

## Arquivos esperados para criação ou modificação

| Arquivo                                                        | Ação        |
|----------------------------------------------------------------|-------------|
| `routes/api.php`                                               | Modificar   |
| `database/migrations/xxxx_create_users_table.php`             | Modificar ou criar nova migration |
| `app/Models/User.php`                                          | Modificar   |
| `app/Http/Requests/Auth/LoginRequest.php`                      | Modificar   |
| `database/seeders/AdminUserSeeder.php`                         | Criar       |
| `database/seeders/DatabaseSeeder.php`                          | Modificar   |
| `.env.example`                                                 | Modificar   |

---

## O que NÃO deve ser feito

- Não criar nenhuma rota de registro pública ou protegida.
- Não remover o campo `email` da tabela ou da model.
- Não alterar o comportamento do logout, reset de senha ou verificação de e-mail.
- Não criar controllers ou classes adicionais — apenas modificar os existentes.

---

## Verificação final

Após as alterações, o fluxo esperado é:

1. Rodar `php artisan db:seed --class=AdminUserSeeder` cria o usuário admin.
2. `POST /api/login` com `{ "username": "admin", "password": "..." }` retorna o token Sanctum.
3. `POST /api/register` retorna **404** ou não existe mais nas rotas.
4. `GET /api/user` com Bearer Token retorna os dados do usuário autenticado.
