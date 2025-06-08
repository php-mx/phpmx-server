<?php

namespace Controller\Base;

use PhpMx\Assets as MxAssets;
use PhpMx\Path;
use PhpMx\Request;

class Download
{
    function default()
    {
        $file = Path::seekFile('storage/download', ...Request::route());
        MxAssets::download($file);
    }
}
