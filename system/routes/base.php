<?php

use PhpMx\Router;

Router::get('assets/...', \Controller\Base\Assets::class);
Router::get('download/...', \Controller\Base\Download::class);

Router::get('favicon.ico', \Controller\Base\Favicon::class);
Router::get('robots.txt', \Controller\Base\Robots::class);
Router::get('sitemap.xml', \Controller\Base\Sitemap::class);

Router::get('', STS_OK);
