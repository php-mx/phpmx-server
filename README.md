# PHPMX - SERVER

Módulo de servidor para criação de APIs e aplicações web modernas com PHPMX.

---

## Dependência

- [phpmx-core](https://github.com/php-mx/phpmx-core)

---

## Instalação

A instalação é feita em um projeto **vazio** ou junto ao **phpmx-core**, utilizando apenas dois comandos no terminal:

```bash
composer require phpmx/server
.\vendor\bin\mx install
```

Você pode verificar se tudo está pronto executando o comando abaixo:

```bash
php mx
```

---

## Estrutura de Pastas

Este pacote adiciona quatro itens estruturais ao seu projeto:

- **middleware**: Middlewares para tratamento de requisições e respostas.
- **routes**: Definição das rotas da aplicação/API.
- **source/Controller**: Controladores da aplicação/API.
- **index.php**: Arquivo principal de entrada da aplicação.

### middleware

Defina middlewares úteis para seu projeto. Middlewares são funções que processam a requisição antes ou depois do controlador principal.

```php
return function($request, $next) {
    // Lógica do middleware
    return $next($request);
};
```

Você pode criar novos middlewares utilizando o comando:

```php
php mx create.middleware minhaMiddleware
```

### routes

Defina suas rotas HTTP (GET, POST, etc) neste diretório. Você é livre para criar qualquer arquivo PHP que sua organização exigir. Dentro dos arquivos, utilize a classe **Router** para declarar suas rotas:

```php
Route::get('/home', 'controller:method',['middleware1','middleware2']);
```

### source/Controller

Coloque aqui seus controladores. Cada controlador deve ser uma classe PHP responsável por tratar as requisições de uma rota específica ou grupo de rotas. Organize seus controladores conforme a lógica da sua aplicação.

Você pode criar novos controllers utilizando o comando:

```php
php mx create.controller meuController
```

### index.php

Arquivo principal de entrada da aplicação. Toda requisição HTTP deve ser direcionada para este arquivo.

---

## Documentação

- **Helper**

  - [constant](./.doc/helper/constant.md)
  - [environment](./.doc/helper/environment.md)
  - [function](./.doc/helper/function.md)
  - [middleware](./.doc/helper/middleware.md)
  - [routes](./.doc/helper/routes.md)

- **Source**

  - [Assets](./.doc/source/Assets.md)
  - [Input](./.doc/source/Input.md)
  - [Jwt](./.doc/source/Jwt.md)
  - [Middleware](./.doc/source/Middleware.md)
  - [Request](./.doc/source/Request.md)
  - [Response](./.doc/source/Response.md)
  - [Router](./.doc/source/Router.md)

- **Terminal**

  - [create](./.doc/terminal/create.md)
  - [help](./.doc/terminal/help.md)
  - [server](./.doc/terminal/server.md)

---

[phpmx](https://github.com/php-mx) | [phpmx-core](https://github.com/php-mx/phpmx-core) | [phpmx-server](https://github.com/php-mx/phpmx-server) | [phpmx-datalayer](https://github.com/php-mx/phpmx-datalayer) | [phpmx-view](https://github.com/php-mx/phpmx-view)
