<?php

use PhpMx\Request;

/**
 * Middlewares interceptam a requisição antes (e opcionalmente depois) da ação de uma rota.
 * Ficam em system/middleware/ e são referenciados por dot.notation (ex: 'auth', 'group.admin').
 * São registrados globalmente em Router::solve() ou por rota via terceiro parâmetro.
 * @example auth
 * @example group.admin
 * @see Router::solve()
 * @see Router::get()
 */
return new class {

    function __invoke(Closure $next)
    {
        // Lógica executada ANTES da rota
        $token = Request::header('Authorization');

        if (is_blank($token))
            throw new Exception('', STS_UNAUTHORIZED);

        // Disponibiliza dados para os controllers via Request
        Request::set_body('token', $token);

        // Chama o próximo middleware ou a ação da rota
        $response = $next();

        // Lógica executada APÓS a rota (opcional)
        return $response;
    }
};
