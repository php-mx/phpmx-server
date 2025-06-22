# Response

Classe utilitária estática para manipulação e envio da resposta HTTP no ecossistema PHPMX.

```php
use PhpMx\Response;
```

## Visão geral

A classe `Response` centraliza o controle de status, headers, conteúdo, tipo, cache e envio da resposta HTTP. Todos os métodos são estáticos.

---

## Métodos principais

### status

Define o status HTTP da resposta.

```php
Response::status(int $status, bool $replace = true): void
```

### header

Define um ou mais cabeçalhos para a resposta.

```php
Response::header(string|array $name, ?string $value = null): void
```

### type

Define o content-type da resposta.

```php
Response::type(string $type, bool $replace = true): void
```

### content

Define o conteúdo da resposta.

```php
Response::content(mixed $content, bool $replace = true): void
```

### cache

Define o tempo de cache da resposta.

```php
Response::cache(null|bool|string $strToTime): void
```

### download

Define se a resposta deve ser enviada como download.

```php
Response::download(null|bool|string $download): void
```

### send

Envia a resposta ao navegador do cliente e finaliza a execução.

```php
Response::send(): never
```

### getStatus

Retorna o status atual da resposta.

```php
Response::getStatus(): ?int
```

### getContent

Retorna o conteúdo atual da resposta.

```php
Response::getContent(): mixed
```

### checkType

Verifica se o tipo da resposta é um dos tipos informados.

```php
Response::checkType(...$types): bool
```
