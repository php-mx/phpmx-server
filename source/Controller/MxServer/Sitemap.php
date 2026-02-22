<?php

namespace Controller\MxServer;

use PhpMx\Assets;
use PhpMx\File;
use PhpMx\Path;
use PhpMx\Response;

class Sitemap
{
    /** Gera a estrutura inicial do mapa do site para indexação em motores de busca */
    function __invoke()
    {
        $file = path('storage/assets/sitemap.xml');

        if (!File::check($file)) {
            Response::cache(false);
            $file = Path::seekForFile('storage/assets/sitemap.xml');
        }

        Assets::send($file);
    }
}
