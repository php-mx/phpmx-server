<?php

namespace Controller\Base;

use PhpMx\Assets;
use PhpMx\File;
use PhpMx\Path;
use PhpMx\Response;

class Favicon
{
    function __invoke()
    {
        $file = path('library/assets/favicon.ico');

        if (!File::check($file)) {
            Response::cache(false);
            $file = Path::seekForFile('library/assets/favicon.ico');
        }

        Assets::send($file);
    }
}
