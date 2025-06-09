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

    function __invoke($method = '*', $uri = null)
    {
        if ($uri) list($uri) = $this->parseRouteTemplate($uri);
        self::$METHOD = $method == '*' ? null : $method;
        self::$URI = $uri;

        Import::only('index.php');
    }

    function solve(array $globalMiddlewares)
    {
        self::$MIDDLEWARES[] = $globalMiddlewares;

        foreach (Path::seekDirs('routes') as $path) {
            $origin = $this->getOrigim($path);
            self::echo('[#]', $origin);
            self::echoLine();

            foreach (Dir::seekForFile($path, true) as $routeFile) {
                $file = path($path, $routeFile);

                self::$ROUTE = [];
                Import::only($file, true);

                if (count(self::$ROUTE)) {
                    self::echo(' - [#]', $file);
                    self::$ROUTE = array_reverse(self::$ROUTE);

                    foreach (self::$ROUTE as &$route) {
                        $method = $route['method'];
                        $template = $route['template'];

                        $replaced = '';
                        if (self::$USED[$method][$template] ?? false) {
                            $replaced = self::$USED[$method][$template];

                            if ($replaced['origin'] == $origin) {
                                $replaced = prepare(' [replaced in [#file] ([#line])]', $replaced);
                            } else {
                                $replaced = prepare(' [replaced in [#origin]: [#file] ([#line])]', $replaced);
                            }
                        }

                        self::$USED[$method][$template] = self::$USED[$method][$template] ?? [
                            'origin' => $origin,
                            'file' => $file,
                            'line' => $route['line'],
                        ];

                        $route['status'] = $replaced;
                    }

                    self::$ROUTE = array_reverse(self::$ROUTE);

                    foreach (self::$ROUTE as $route)
                        self::echo('    - [[#method]]: [#call] {[#middlewares]}[#status]', $route);

                    self::echo();
                }
            }
        }
    }

    function route(string $method, string $route, string $response, array $middlewares = [])
    {
        if (is_null(self::$METHOD) || self::$METHOD == $method) {
            $route = implode('/', [...self::$PATH, $route]);
            list($template, $params) = $this->parseRouteTemplate($route);

            if ($this->checkRouteMatch($template)) {
                $middlewares = [...end(self::$MIDDLEWARES), ...$middlewares];
                $middlewares = implode(',', $middlewares);

                $line = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['line'];

                $params = array_map(fn($v) => is_null($v) ? $v : "[#$v]", $params);
                $call = str_replace('#', '[#]', $template);
                $call = prepare($call, $params);

                self::$ROUTE[] = [
                    'method' => strtoupper($method),
                    'template' => $template,
                    'call' => $call,
                    'line' => $line,
                    'middlewares' => $middlewares,
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

    protected function getOrigim($path)
    {
        if ($path === 'routes') return 'CURRENT-PROJECT';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
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
