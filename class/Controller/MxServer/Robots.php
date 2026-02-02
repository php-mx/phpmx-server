<?php

namespace Controller\MxServer;

use PhpMx\Response;

class Robots
{
    /** Configura as instruções para motores de busca bloqueando a indexação de todo o site */
    function __invoke()
    {
        Response::type('text');
        Response::content("User-agent: *\nDisallow: /");
        Response::send();
    }
}
