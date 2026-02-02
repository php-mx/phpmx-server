<?php

namespace Controller\MxServer;

use PhpMx\Path;
use PhpMx\Request;

class Download
{
    /** Gerencia e força o download de arquivos localizados na pasta de downloads da biblioteca */
    function __invoke()
    {
        $file = Path::seekForFile('library/download', ...Request::route());
        \PhpMx\Assets::download($file);
    }
}
