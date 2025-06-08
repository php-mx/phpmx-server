<?php

namespace Controller\Base;

use PhpMx\Assets as MxAssets;
use PhpMx\File;
use PhpMx\Path;
use PhpMx\Response;

class Favicon
{
    function default()
    {
        $file = 'storage/assets/favicon.ico';

        if (!File::check($file)) {
            Response::cache(false);
            $file = Path::seekFile('storage/assets/favicon.ico');
        }

        MxAssets::send($file);
    }
}
