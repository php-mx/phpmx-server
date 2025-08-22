<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

return new class extends Terminal {

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

        self::echo("Controller [$file] created successfully");
        self::echo('[[#]]', $file);
    }
};
