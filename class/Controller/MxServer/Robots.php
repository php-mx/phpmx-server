<?php

namespace Controller\MxServer;

use PhpMx\Context;

class Robots extends Context
{
    function __invoke()
    {
        $this->response->type('text');
        $this->response->content("User-agent: *\nDisallow: /");
        $this->response->send();
    }
}
