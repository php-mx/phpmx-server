<?php

namespace Controller\MxServer;

use PhpMx\Context;
use PhpMx\Path;

class Assets extends Context
{
    function __invoke()
    {
        $file = Path::seekForFile('library/assets', ...$this->request->route());
        \PhpMx\Assets::send($this->response, $file);
    }
}
