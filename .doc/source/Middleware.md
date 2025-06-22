# Middleware

Classe utilitária interna responsável pelo gerenciamento e execução da fila de middlewares no ecossistema PHPMX.

```php
use PhpMx\Middleware;
```

## Métodos principais

### run

Executa uma fila de middlewares e, ao final, a action desejada.

```php
Middleware::run(array $queue, callable $action): mixed
```

- Recebe uma lista de middlewares e uma ação final.
- Garante que cada middleware seja executado em ordem, passando o controle para o próximo via `$next()`.

## Funcionamento

- Middlewares podem ser declarados como closures, nomes de arquivos ou arrays de middlewares.
- A classe resolve e executa cada middleware, passando sempre o `$next` para o próximo da fila.
- Caso o middleware não seja encontrado ou não seja executável, uma exceção é lançada.

Para criar suas proprias middlewares utilize o comando abaixo

```php
php mx create.middleware minhaMiddleware
```

Isso vai criar um arquivo em **middleware\minhaMiddleware.php** com o conteúdo

```php

return new class {

    function __invoke(Closure $next)
    {
        return $next();
    }
};
```

Para utiliza-la, adicione o nome da middleware em uma rota

```php
Router::get('','controller:method',['minhaMiddleware']);
```
