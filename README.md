# BeTalent
## Como Executar

1. Antes de iniciar o projeto voce precisa ter o [docker](https://docs.docker.com/get-started/get-docker) e [docker compose](https://docs.docker.com/compose/install) instalados. Caso já os possua, avance para o próximo passo.
2. Para iniciar o projeto rode o comando abaixo:
   ```bash
    docker compose up -d && docker compose exec app php artisan migrate && docker compose exec app php artisan db:seed
   ```
3. Para rodar os tests:
   ```bash
    docker compose exec app ./vendor/bin/phpunit tests
   ```


---

## Endpoints

> Rotas marcadas com **[Auth]** exigem o header `Authorization: Bearer {token}`.  
> Rotas marcadas com **[Admin/Manager]** exigem role `admin` ou `manager`.  
> Rotas marcadas com **[Admin/Manager/Finance]** exigem role `admin`, `manager` ou `finance`.

---

### Autenticação

#### POST /auth/login
Input:
```json
{ "email": "user@example.com", "password": "secret123" }
```
Output:
```json
{ "access_token": "eyJ...", "token_type": "bearer", "expires_in": 3600 }
```
Status code: `200`

---

#### POST /auth/logout `[Auth]`
Output:
```json
{ "message": "Successfully logged out" }
```
Status code: `200`

---

#### POST /auth/refresh `[Auth]`
Output:
```json
{ "access_token": "eyJ...", "token_type": "bearer", "expires_in": 3600 }
```
Status code: `200`

---

#### GET /auth/me `[Auth]`
Output:
```json
{ "id": 1, "name": "John Doe", "email": "user@example.com", "role": "admin" }
```
Status code: `200`

---

### Usuários `[Auth]`

#### GET /users
Output:
```json
{ "data": [{ "id": 1, "name": "John Doe", "email": "user@example.com", "role": "admin" }], "total": 1 }
```
Status code: `200`

---

#### POST /users
Input:
```json
{ "name": "John Doe", "email": "user@example.com", "password": "secret123", "role": "manager" }
```
Output:
```json
{ "id": 1, "name": "John Doe", "email": "user@example.com", "role": "manager" }
```
Status code: `201`

---

#### GET /users/{id}
Output:
```json
{ "id": 1, "name": "John Doe", "email": "user@example.com", "role": "admin" }
```
Status code: `200`

---

#### PUT /users/{id}
Input (todos os campos são opcionais):
```json
{ "name": "Jane Doe", "email": "jane@example.com", "password": "newpass123", "role": "finance" }
```
Output:
```json
{ "id": 1, "name": "Jane Doe", "email": "jane@example.com", "role": "finance" }
```
Status code: `200`

---

#### DELETE /users/{id}
Output: `null`  
Status code: `204`

---

### Produtos `[Auth]` `[Admin/Manager]`

#### GET /products
Output:
```json
{ "data": [{ "id": 1, "name": "Product A", "amount": 5000 }], "total": 1 }
```
Status code: `200`

---

#### POST /products
Input:
```json
{ "name": "Product A", "amount": 5000 }
```
Output:
```json
{ "id": 1, "name": "Product A", "amount": 5000 }
```
Status code: `201`

---

#### GET /products/{id}
Output:
```json
{ "id": 1, "name": "Product A", "amount": 5000 }
```
Status code: `200`

---

#### PUT /products/{id}
Input (todos os campos são opcionais):
```json
{ "name": "Product B", "amount": 7500 }
```
Output:
```json
{ "id": 1, "name": "Product B", "amount": 7500 }
```
Status code: `200`

---

#### DELETE /products/{id}
Output: `null`  
Status code: `204`

---

### Clientes `[Auth]` `[Admin/Manager/Finance]`

#### GET /clients
Output:
```json
{ "data": [{ "id": 1, "name": "Jane Doe", "email": "jane@example.com" }], "total": 1 }
```
Status code: `200`

---

#### GET /clients/{id}
Output:
```json
{
  "id": 1,
  "name": "Jane Doe",
  "email": "jane@example.com",
  "transactions": [{ "id": 1, "amount": 10000, "status": "paid" }]
}
```
Status code: `200`

---

### Transações

#### POST /transactions (público)
Input:
```json
{
  "product_id": 1,
  "quantity": 2,
  "name": "Jane Doe",
  "email": "jane@example.com",
  "cardNumber": "1234567890123456",
  "cvv": "123"
}
```
Output:
```json
{
  "id": 1,
  "amount": 10000,
  "status": "paid",
  "card_last_numbers": "3456",
  "client": { "id": 1, "name": "Jane Doe" },
  "gateway": { "id": 1, "name": "Gateway1" },
  "products": [{ "id": 1, "name": "Product A" }]
}
```
Status code: `201`

---

#### GET /transactions `[Auth]` `[Admin/Manager/Finance]`
Output:
```json
{ "data": [{ "id": 1, "amount": 10000, "status": "paid", "client": {}, "gateway": {} }], "total": 1 }
```
Status code: `200`

---

#### GET /transactions/{id} `[Auth]` `[Admin/Manager/Finance]`
Output:
```json
{ "id": 1, "amount": 10000, "status": "paid", "client": {}, "gateway": {}, "products": [] }
```
Status code: `200`

---

#### POST /transactions/{id}/refund `[Auth]` `[Admin/Manager/Finance]`
Output:
```json
{ "message": "Refund successful", "transaction": { "id": 1, "status": "refunded" } }
```
Status code: `200`

---

### Gateways `[Auth]` `[Admin/Manager]`

#### POST /gateways/{id}/activate
Output:
```json
{ "message": "Gateway activated", "gateway": { "id": 1, "is_active": true } }
```
Status code: `200`

---

#### POST /gateways/{id}/deactivate
Output:
```json
{ "message": "Gateway deactivated", "gateway": { "id": 1, "is_active": false } }
```
Status code: `200`

---

#### PUT /gateways/{id}/priority
Input:
```json
{ "priority": 2 }
```
Output:
```json
{ "message": "Gateway priority updated", "gateway": { "id": 1, "priority": 2 } }
```
Status code: `200`
