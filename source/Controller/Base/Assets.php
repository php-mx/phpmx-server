<?php

namespace Controller\Base;

use PhpMx\Assets as MxAssets;
use PhpMx\Path;
use PhpMx\Request;

class Assets
{
    function default()
    {
        $file = Path::seekFile('storage/assets', ...Request::route());
        MxAssets::send($file);
    }
}
