<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

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

        Terminal::echo("Controller [#greenB:#] created successfully [#whiteD:#]", [
            $class,
            $file
        ]);
    }
};
