<?php

namespace Controller\MxServer;

use PhpMx\Response;

class Sitemap
{
    function __invoke()
    {
        Response::type('xml');
        Response::content('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        Response::send();
    }
}
