<?php

use PhpMx\Dir;
use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

return new class extends Terminal {

  function __invoke()
  {
    Dir::create('helper');
    Dir::create('helper/constant');
    Dir::create('helper/function');
    Dir::create('helper/script');
    Dir::create('middleware');
    Dir::create('routes');
    Dir::create('source');
    Dir::create('source/Controller');
    Dir::create('storage');
    Dir::create('storage/assets');
    Dir::create('storage/download');
    Dir::create('terminal');

    File::copy(path(dirname(__FILE__, 2), '.gitignore'), './.gitignore');
    File::copy(path(dirname(__FILE__, 2), 'helper/script/path.php'), './helper/script/path.php');

    File::copy(path(dirname(__FILE__, 2), 'index.php'), 'index.php');

    $templateEnv = Path::seekFile('storage/template/env.txt');
    $templateEnv = Import::content($templateEnv);
    File::create('./.env', $templateEnv);

    Terminal::run('composer');
  }
};
