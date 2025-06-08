<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke($middleware)
    {
        $middleware = remove_accents($middleware);

        $middlewareFile = explode('.', $middleware);
        $middlewareFile = path('middleware', ...$middlewareFile);
        $middlewareFile = File::setEx($middlewareFile, 'php');

        if (File::check($middlewareFile))
            throw new Exception("Middleware [$middleware] already exists in project");

        $template = Path::seekFile('storage/template/terminal/middleware.txt');
        $template = Import::content($template, ['middleware' => $middleware]);

        File::create($middlewareFile, $template);

        self::echo('middleware [[#]] created successfully', $middleware);
    }
};
