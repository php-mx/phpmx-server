<?php

namespace Controller\MxServer;

use PhpMx\Assets;
use PhpMx\Context;
use PhpMx\File;
use PhpMx\Path;

class Favicon extends Context
{
    function __invoke()
    {
        $file = path('library/assets/favicon.ico');

        if (!File::check($file)) {
            $this->response->cache(false);
            $file = Path::seekForFile('library/assets/favicon.ico');
        }
        Assets::send($this->response, $file);
    }
}
