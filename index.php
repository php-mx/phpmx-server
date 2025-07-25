<?php

use PhpMx\Router;

chdir(__DIR__);

require_once "./vendor/autoload.php";

Router::solve(["cors", "encaps"]);
