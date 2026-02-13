<?php

use PhpMx\File;
use PhpMx\Import;
use PhpMx\Terminal;

/** Cria uma rota no final do arquivo system/router/autorouter.php */
return new class {

    function __invoke(string $method, string $template, string $response, ?string $responseMethod = null)
    {
        $method = strtolower($method);

        if (!in_array($method, ['get', 'post', 'put', 'delete', 'full', 'add']))
            throw new Exception('Routing method not allowed');

        if (!is_httpStatus($response)) {
            $controller = str_replace('.', '/', $response);
            $controller = str_replace('\\', '/', $controller);

            $controller = explode('/', $controller);
            $controller = array_map(fn($v) => ucfirst($v), $controller);
            $controller = implode('\\', [...$controller]);

            $response = !is_blank($responseMethod) ? prepare('[[#]::class, "[#]"]', [$controller, $responseMethod]) : "$controller::class";
        }

        $routeDefinition = prepare('Router::[#]("[#]", [#]);', [$method, $template, $response]);

        $routerFile = 'system/router/autorouter.php';
        $content = Import::content($routerFile);

        if (!is_blank($content)) {
            $prefx = prepare('Router::[#]("[#]",', [$method, $template]);
            $prefx2 = prepare("Router::[#]('[#]',", [$method, $template]);
            $lines = preg_split('/\r\n|\r|\n/', $content);
            foreach ($lines as $index => $lineContent) {
                $trimmed = trim($lineContent);
                if (str_starts_with($trimmed, $prefx) || str_starts_with($trimmed, $prefx2))
                    throw new Exception("Route [$template] already exists in $routerFile:" . ($index + 1));
            }
        }

        if (is_blank($content))
            $content = "<?php\n\nuse PhpMx\Router;\n";

        $content .= "\n$routeDefinition";

        $line = substr_count($content, "\n") - 1;

        File::create($routerFile, $content, true);

        Terminal::echol("Router [#c:s,#] created in [#c:p,#][#c:p,:][#c:p,#]", [$template, $routerFile, $line]);

        if ($controller && !str_starts_with($controller, '\\'))
            if (!class_exists("\\Controller\\$controller") && Terminal::confirm('Do you want to create the controller?', default: true))
                Terminal::run('make.controller', $controller, $responseMethod);
    }
};
