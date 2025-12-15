<?php

use PhpMx\Router;

chdir(__DIR__);

require_once "./vendor/autoload.php";

$router = new Router(["cors", "encaps"]);
$router->solve();
