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
        $file = path('library/assets/robots.txt');

        if (!File::check($file)) {
            Response::cache(false);
            $file = Path::seekForFile('library/assets/robots.txt');
        }

        Assets::send($file);
    }
}
