<?php

use PhpMx\Router;

Router::get('', 'base.status');

Router::get('favicon.ico', 'base.favicon');
Router::get('robots.txt', 'base.robots');
Router::get('sitemap.xml', 'base.sitemap');

Router::get('captcha', 'base.captcha');

Router::get('assets/...', 'base.assets');
Router::get('download/...', 'base.download');
