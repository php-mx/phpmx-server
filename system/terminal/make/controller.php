<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

/**
 * Gera um novo arquivo de Controller com namespace e estrutura baseados no caminho informado.
 * @param string $controller Nome do controller em dot.notation ou com separadores de caminho.
 * @param string|null $method Nome do método inicial a ser gerado no controller.
 */
return new class {

    function __invoke($controller, $method = null)
    {
        $controller = str_replace('.', '/', $controller);
        $controller = str_replace('\\', '/', $controller);
        $controller = explode('/', $controller);
        $controller = array_map(fn($v) => ucfirst($v), $controller);

        $file = path('source/Controller', ...$controller) . '.php';

        if (File::check($file))
            throw new Exception("File [$file] already exists");

        $class = array_pop($controller);
        $namespace = implode('\\', ['Controller', ...$controller]);
        $method = $method ?? '__invoke';

        $template = Path::seekForFile('storage/template/terminal/controller.txt');
        $template = Import::content($template, [
            'class' => $class,
            'namespace' => $namespace,
            'method' => $method,
        ]);

        File::create($file, $template);

        Terminal::echol("File [#c:p,#] created successfully", $file);
    }
};
