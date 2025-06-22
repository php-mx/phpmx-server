# Request

Classe utilitária estática para acesso e manipulação dos dados da requisição HTTP no ecossistema PHPMX.

```php
use PhpMx\Request;
```

## Visão geral

A classe `Request` centraliza o acesso a todos os dados da requisição, como headers, query, body, arquivos, rota, tipo, host, SSL, etc. Todos os métodos são estáticos.

---

## Métodos principais

### type

Retorna ou compara o tipo da requisição (GET, POST, PUT, DELETE, OPTIONS, TERMINAL).

```php
Request::type(): string
Request::type('POST'): bool
```

### header

Retorna um header específico ou todos os headers da requisição.

```php
Request::header(): array
Request::header('Authorization'): string|null
```

### ssl

Retorna ou compara se a requisição está usando SSL.

```php
Request::ssl(): bool
Request::ssl(true): bool
```

### host

Retorna o host da requisição.

```php
Request::host(): string
```

### path

Retorna todos os caminhos da URI ou um caminho específico.

```php
Request::path(): array
Request::path(0): string|null
```

### query

Retorna todos os parâmetros de query ou um específico.

```php
Request::query(): array
Request::query('id'): mixed
```

### body

Retorna todos os dados enviados no corpo da requisição ou um campo específico.

```php
Request::body(): array
Request::body('campo'): mixed
```

### route

Retorna todos os parâmetros de rota ou um específico.

```php
Request::route(): array
Request::route('id'): mixed
```

### data

Retorna todos os dados recebidos (rota, query, body, file) ou um campo específico.

```php
Request::data(): array
Request::data('campo'): mixed
```

### file

Retorna todos os arquivos enviados ou um arquivo específico.

```php
Request::file(): array
Request::file('foto'): array
```

---

## Métodos de modificação

### set_header

Define o valor de um header da requisição.

```php
Request::set_header($name, $value): void
```

### set_query

Define o valor de um parâmetro de query.

```php
Request::set_query($name, $value): void
```

### set_body

Define o valor de um campo do corpo da requisição.

```php
Request::set_body($name, $value): void
```

### set_route

Define o valor de um parâmetro de rota.

```php
Request::set_route($name, $value): void
```
