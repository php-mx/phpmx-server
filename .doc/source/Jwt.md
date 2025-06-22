# Jwt

Classe utilitária para geração, validação e leitura de tokens JWT (JSON Web Token) no ecossistema PHPMX.

```php
use PhpMx\Jwt;
```

## Métodos principais

### on

Gera um token JWT a partir de um payload.

```php
Jwt::on(mixed $payload, ?string $key = null): string
```

- Cria um token JWT assinado com a chave definida em `JWT` (ou informada via parâmetro).
- O payload pode ser qualquer array ou objeto serializável.

### off

Lê e valida um token JWT, retornando o conteúdo do payload.

```php
Jwt::off(mixed $token, ?string $key = null): mixed
```

- Retorna o conteúdo do token se válido, ou `false` se inválido.
- A chave de validação pode ser definida em `JWT` ou passada como parâmetro.

### check

Verifica se uma variável é um token JWT válido.

```php
Jwt::check(mixed $var, ?string $key = null): bool
```

- Retorna `true` se o token for válido, `false` caso contrário.

## Exemplo de uso

```php
$token = Jwt::on(['id' => 123, 'nome' => 'Andre']);
$dados = Jwt::off($token);
$valido = Jwt::check($token);
```

---

A chave padrão utilizada é definida pela variável de ambiente `JWT`.
