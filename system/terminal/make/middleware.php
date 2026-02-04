<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

/** Cria um novo arquivo de middleware no diretório do sistema com base em um template padrão */
return new class {

    function __invoke($middleware)
    {
        $middleware = remove_accents($middleware);

        $file = explode('.', $middleware);
        $file = path('system/middleware', ...$file);
        $file = File::setEx($file, 'php');

        if (File::check($file))
            throw new Exception("Middleware [$middleware] already exists in project");

        $template = Path::seekForFile('library/template/terminal/middleware.txt');
        $template = Import::content($template, ['middleware' => $middleware]);

        File::create($file, $template);

        Terminal::echol("File [#c:p,#] created successfully", $file);
    }
};
