<?php

namespace Controller\MxServer;

use PhpMx\Path;
use PhpMx\Request;

class Assets
{
    function __invoke()
    {
        $file = Path::seekForFile('library/assets', ...Request::route());
        \PhpMx\Assets::send($file);
    }
}
