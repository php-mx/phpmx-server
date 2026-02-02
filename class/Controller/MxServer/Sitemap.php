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
        $file = path('library/assets/sitemap.xml');

        if (!File::check($file)) {
            Response::cache(false);
            $file = Path::seekForFile('library/assets/sitemap.xml');
        }

        Assets::send($file);
    }
}
