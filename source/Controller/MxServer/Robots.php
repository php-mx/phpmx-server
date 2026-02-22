<?php

namespace Controller\MxServer;

use PhpMx\Assets;
use PhpMx\File;
use PhpMx\Path;
use PhpMx\Response;

class Robots
{
    /** Configura as instruções para motores de busca bloqueando a indexação de todo o site */
    function __invoke()
    {
        $file = path('storage/assets/robots.txt');

        if (!File::check($file)) {
            Response::cache(false);
            $file = Path::seekForFile('storage/assets/robots.txt');
        }

        Assets::send($file);
    }
}
