<?php

namespace Controller\MxServer;

use PhpMx\Context;
use PhpMx\Path;

class Download extends Context
{
    function __invoke()
    {
        $file = Path::seekForFile('library/download', ...$this->request->route());
        \PhpMx\Assets::download($this->response, $file);
    }
}
