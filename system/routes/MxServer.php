<?php

use PhpMx\Router;

Router::get('assets/...', \Controller\MxServer\Assets::class);
Router::get('download/...', \Controller\MxServer\Download::class);

Router::get('favicon.ico', \Controller\MxServer\Favicon::class);
Router::get('robots.txt', \Controller\MxServer\Robots::class);
Router::get('sitemap.xml', \Controller\MxServer\Sitemap::class);

Router::get('captcha', \Controller\MxServer\Captcha::class);

Router::get('', STS_OK);
