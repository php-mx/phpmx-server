<?php

use PhpMx\Request;
use PhpMx\Response;

/** Configura as permissões de CORS */
return new class {

    function __invoke(Closure $next)
    {
        Response::header('Mx-Cors', 'true');

        Response::header('Access-Control-Allow-Origin', '*');
        Response::header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        Response::header('Access-Control-Allow-Headers', 'X-Requested-With');

        if (Request::server('HTTP_ORIGIN')) {
            Response::header('Access-Control-Allow-Origin', Request::server('HTTP_ORIGIN'));
            Response::header('Access-Control-Allow-Credentials', 'true');
            Response::header('Access-Control-Max-Age', 86400);
        }

        if (Request::type('OPTIONS')) {
            if (Request::server('HTTP_ACCESS_CONTROL_REQUEST_METHOD'))
                Response::header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

            if (Request::server('HTTP_ACCESS_CONTROL_REQUEST_HEADERS'))
                Response::header('Access-Control-Allow-Headers', Request::server('HTTP_ACCESS_CONTROL_REQUEST_HEADERS'));

            Response::status(STS_OK);
            Response::send();
        }

        return $next();
    }
};
