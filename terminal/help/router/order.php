<?php

namespace PhpMx;

use Closure;

$interceptor = new class extends Terminal {

    protected static ?string $METHOD = null;
    protected static ?string $URI = null;

    protected static array $MIDDLEWARES = [];
    protected static array $PATH = [];

    protected static array $USED = ['get' => [], 'post' => [], 'put' => [], 'delete' => []];

    protected static array $ROUTE = [];

    function __invoke($method, $uri = null)
    {
        if ($uri) list($uri) = $this->parseRouteTemplate($uri);
        self::$METHOD = $method;
        self::$URI = $uri;

        Import::only('index.php');
    }

    function solve(array $globalMiddlewares)
    {
        self::$MIDDLEWARES[] = $globalMiddlewares;

        $paths = Path::seekDirs('routes');
        $paths = array_reverse($paths);

        foreach ($paths as $path)
            foreach (Dir::seekForFile($path, true) as $file)
                Import::only("$path/$file", true);

        $routes = $this->organize(self::$ROUTE);

        foreach (array_values($routes) as $pos => $route)
            self::echo('[#pos]: [#call] [[#file] ([#line])]', ['pos' => 1 + $pos, ...$route]);
    }

    function route(string $method, string $route, string $response, array $middlewares = [])
    {
        if (is_null(self::$METHOD) || self::$METHOD == $method) {

            $route = implode('/', [...self::$PATH, $route]);
            list($template, $params) = $this->parseRouteTemplate($route);

            if ($this->checkRouteMatch($template)) {
                $dbug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
                $line = $dbug['line'];
                $file = path($dbug['file']);

                $params = array_map(fn($v) => is_null($v) ? $v : "[#$v]", $params);
                $call = str_replace('#', '[#]', $template);
                $call = prepare($call, $params);

                self::$ROUTE[$template] = [
                    'call' => $call,
                    'line' => $line,
                    'file' => $file,
                ];
            }
        }
    }

    function path(string $path, Closure $wrapper)
    {
        self::$PATH[] = $path;
        $wrapper();
        array_pop(self::$PATH);
    }

    function middleware(array $middlewares, Closure $wrapper)
    {
        self::$MIDDLEWARES[] = [...end(self::$MIDDLEWARES), ...$middlewares];
        $wrapper();
        array_pop(self::$MIDDLEWARES);
    }

    function group(string $path, array $middlewares, Closure $wrapper)
    {
        $wrapper = fn() => $this->middleware($middlewares, $wrapper);
        $wrapper = fn() => $this->path($path, $wrapper);
        $wrapper();
    }

    protected function parseRouteTemplate(string $route): array
    {
        $params = [];
        $route = $this->normalizeRoute($route);
        $route = explode('/', $route);

        foreach ($route as $pos => $param) {
            if (str_starts_with($param, '[')) {
                $param = trim($param, '[]');
                if (str_starts_with($param, '#')) $param = substr($param, 1);
                if (empty($param)) $param = null;
                $params[$pos] = $param;
                $route[$pos] = '#';
            }
        }

        $route = implode('/', $route);
        return [$route, $params];
    }

    protected function normalizeRoute(string $route): string
    {
        $route = explode('/', $route);
        $route = array_filter($route, fn($v) => trim($v) != '');
        $route = implode('/', $route);
        $route .= '/';

        if (strpos($route, '...') !== false) {
            $route = explode('...', $route);
            $route = array_shift($route);
            $route = trim($route, '/');
            $route .= '/...';
        }

        return $route;
    }

    protected function checkRouteMatch(string $template): bool
    {
        if (is_null(self::$URI)) return true;

        $uri = self::$URI;
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);

        $template = trim($template, '/');
        $template = explode('/', $template);

        while (count($uri)) {
            $received = array_shift($uri) ?? '';
            if ($received === '...') return true;
            if (!count($template)) return false;
            $expected = array_shift($template);
            if ($expected !== '#' && $received !== $expected) return false;
            if (is_blank($received) && !is_blank($expected)) return false;
        }

        return true;
    }

    protected function organize(array $array): array
    {
        uksort($array, function ($a, $b) {
            $nBarrA = substr_count($a, '/');
            $nBarrB = substr_count($b, '/');

            if ($nBarrA != $nBarrB) return $nBarrB <=> $nBarrA;

            $arrayA = explode('/', $a);
            $arrayB = explode('/', $b);
            $na = '';
            $nb = '';
            $max = max(count($arrayA), count($arrayB));

            $dynamicParamWeight  =  ['#' => '1',  '...' => '2'];
            for ($i = 0; $i < $max; $i++) {
                $aVal = $arrayA[$i] ?? '';
                $bVal = $arrayB[$i] ?? '';
                $na .= $dynamicParamWeight[$aVal] ?? '0';
                $nb .= $dynamicParamWeight[$bVal] ?? '0';
            }

            $result = intval($na) <=> intval($nb);
            if ($result) return $result;

            $result = count($arrayA) <=> count($arrayB);
            if ($result) return $result * -1;

            $result = strlen($a) <=> strlen($b);
            if ($result) return $result * -1;
        });

        return $array;
    }
};

if (!class_exists('\PhpMx\Router', false)) {
    abstract class Router
    {
        static $interceptor;

        static function __callStatic($name, $arguments)
        {
            return match ($name) {
                'get',
                'post',
                'put',
                'delete', => self::$interceptor->route($name, ...$arguments),
                'solve',
                'path',
                'middleware',
                'group' => self::$interceptor->{$name}(...$arguments),
                default => null,
            };
        }
    };

    Router::$interceptor = $interceptor;
}

return $interceptor;
