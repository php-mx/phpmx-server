<?php

namespace Controller\Base;

use PhpMx\Response;

class Robots
{
    function __invoke()
    {
        Response::type('text');
        Response::content("User-agent: *\nDisallow: /");
        Response::send();
    }
}
