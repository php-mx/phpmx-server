# Router

Classe utilitária estática para definição, organização e resolução de rotas HTTP no ecossistema PHPMX.

```php
use PhpMx\Router;
```

## Visão geral

A classe `Router` centraliza o registro e a resolução das rotas da aplicação, associando caminhos, métodos HTTP, middlewares e controladores/respostas. Todos os métodos são estáticos.

---

## Métodos principais

### get / post / put / delete

Adicionam rotas para responder aos métodos HTTP correspondentes.

```php
Router::get($rota, $resposta, $middlewares = []): void
Router::post($rota, $resposta, $middlewares = []): void
Router::put($rota, $resposta, $middlewares = []): void
Router::delete($rota, $resposta, $middlewares = []): void
```

### path

Define um prefixo de caminho para um grupo de rotas.

```php
Router::path($path, function() { ... }): void
```

### middleware

Define middlewares padrão para um grupo de rotas.

```php
Router::middleware($middlewares, function() { ... }): void
```

### group

Define um grupo de rotas com caminho e middlewares padrão.

```php
Router::group($path, $middlewares, function() { ... }): void
```

### solve

Resolve a rota da requisição atual, executando middlewares e controladores.

```php
Router::solve($globalMiddlewares = []): void
```
