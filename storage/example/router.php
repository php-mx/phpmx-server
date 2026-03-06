<?php

/*
 * Rotas são declaradas em arquivos PHP dentro de system/router/.
 * Todos os arquivos do diretório são carregados automaticamente pelo framework.
 * Use o comando make.route para criar uma nova rota via terminal.
 *
 * Padrões de template de rota:
 *   [#name]  - segmento dinâmico nomeado, acessível via Request::route('name')
 *   [#]      - segmento dinâmico sem nome
 *   ...      - wildcard, captura todo o restante do caminho
 */

use PhpMx\Router;

// Rota GET simples — invoca Controller\Users::__invoke()
Router::get('users', Controller\Users::class);

// Rota com parâmetro nomeado — [#id] é injetado em __construct ou __invoke pelo nome
Router::get('users/[#id]', [Controller\Users::class, 'show']);

// Responde GET e POST na mesma rota
Router::add('users', Controller\Users::class);

// Responde todos os métodos HTTP (GET, POST, PUT, DELETE)
Router::full('users/[#id]', Controller\Users::class);

// Responde com status HTTP diretamente, sem controller
Router::get('ping', STS_OK);

// Rota com middleware individual
Router::get('profile', Controller\Profile::class, ['auth']);

// Grupo com prefixo de path e middlewares compartilhados
Router::group('api', ['auth', 'throttle'], function () {
    Router::get('profile', Controller\Profile::class);
    Router::put('profile', [Controller\Profile::class, 'update']);
});

// Prefixo de path sem middlewares
Router::path('v1', function () {
    Router::get('status', STS_OK);
});

// Wildcard — captura qualquer sub-caminho após 'files/'
// Os segmentos extras ficam disponíveis em Request::route()
Router::get('files/...', Controller\Files::class);
