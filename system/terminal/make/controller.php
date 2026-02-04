<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Router;
use PhpMx\Terminal;

/** Gera um novo arquivo de Controller com namespace e estrutura baseados no caminho informado */
return new class {

    function __invoke($controller)
    {
        $controller = str_replace('.', '/', $controller);
        $controller = explode('/', $controller);
        $controller = array_map(fn($v) => ucfirst($v), $controller);

        $file = path('class/Controller', ...$controller) . '.php';

        if (File::check($file))
            throw new Exception("File [$file] already exists");

        $class = array_pop($controller);
        $namespace = implode('\\', ['Controller', ...$controller]);

        $template = Path::seekForFile('library/template/terminal/controller.txt');
        $template = Import::content($template, [
            'class' => $class,
            'namespace' => $namespace
        ]);

        File::create($file, $template);

        Terminal::echol("File [#c:p,#] created successfully", $file);
    }
};
