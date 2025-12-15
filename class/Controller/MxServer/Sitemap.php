<?php

namespace Controller\MxServer;

use PhpMx\Context;

class Sitemap extends Context
{
    function __invoke()
    {
        $this->response->type('xml');
        $this->response->content('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        $this->response->send();
    }
}
