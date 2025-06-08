<?php

use PhpMx\Router;

chdir(__DIR__);

date_default_timezone_set('America/Sao_Paulo');

require './vendor/autoload.php';

Router::solve(['cors', 'encaps']);
