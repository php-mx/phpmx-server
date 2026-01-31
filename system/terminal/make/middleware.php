<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;

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

        Terminal::echo('middleware [#greenB:#] created successfully [#whiteD:#]', [
            $middleware,
            $file
        ]);
    }
};
