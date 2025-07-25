<?php

namespace PhpMx;

use Closure;
use Exception;

$interceptor = new class extends Terminal {

    public bool $intercepting = false;

    protected ?string $METHOD = null;
    protected ?string $URI = null;

    function __invoke($uri = null)
    {
        if (!$this->intercepting)
            throw new Exception('Class [phpMx\Router] has already been declared and not be intercepted');

        Import::only('index.php');

        $routes = Router::scan();

        $routes = $this->organize($routes);

        if (!is_null($uri))
            $routes = array_filter($routes, fn($v) => $this->checkRouteMatch($v['template'], $uri));

        self::echo();

        foreach ($routes as $route) {
            self::echo(' [[#method]]: [#call]', $route);
            self::echo('   response: [#]', $route['response']);
            self::echo('   middlewares: [#]', $route['middlewares']);
            self::echo('   registred: [#]', $route['registred_in']);
            if ($route['replaced_in'])
                self::echo('   replaced: [#]', $route['replaced_in']);
            self::echo();
        }
    }

    protected function  checkRouteMatch(string $template, string $uri): bool
    {
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);

        $template = trim($template, '/');
        $template = explode('/', $template);

        while (count($template)) {
            $expected = array_shift($template);
            $received = array_shift($uri) ?? '';

            if ($expected === '...') return true;

            if (is_blank($received) && !is_blank($expected)) return false;

            if (count($uri)) {
                if ($expected !== '#' && $received !== $expected) return false;
            } else {
                if ($expected !== '#' && !str_starts_with($expected, $received)) return false;
            }
        }

        return count($uri) === 0;
    }

    protected function organize(array $routes): array
    {
        $scheme = [];
        $used = [];

        foreach (array_reverse($routes) as $route) {

            $method = $route['method'];
            $template = $route['template'];
            $params = $route['params'];
            $file = $route['file'];
            $line = $route['line'];
            $response = $this->getResponse($route['response']);
            $middlewares = $route['middlewares'];

            $key = md5("$method:$template");

            $callParams = array_map(fn($v) => is_null($v) ? $v : "[#$v]", $params);
            $call = str_replace('#', '[#]', $template);
            $call = prepare($call, $callParams);
            $call = trim($call, '/');

            $routeScheme = [
                'template' => $template,
                'method' => strtoupper($method),
                'call' => "/$call",
                'response' => $response,
                'middlewares' => $middlewares,
                'registred_in' => "$file ($line)",
                'replaced_in' => $used[$key] ?? null,
            ];

            if (!$routeScheme['replaced_in'])
                $used[$key] = $routeScheme['registred_in'];

            $scheme[] = $routeScheme;
        }

        $scheme = array_reverse($scheme);

        return $scheme;
    }

    protected function getOrigim($path)
    {
        if ($path === 'system/routes') return 'CURRENT-PROJECT';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
    }

    protected function getResponse($response)
    {
        if (is_numeric($response)) return "[$response]";

        $response = is_array($response) ? $response : [$response, '__invoke'];

        $class = array_shift($response);
        $method = array_shift($response);

        return prepare("[$class][$method]");
    }
};

$interceptor->intercepting = !class_exists('\PhpMx\Router', false);

if ($interceptor->intercepting) {

    Log::add('mx', 'interceptor [PhpMx.Router]');
    abstract class Router
    {
        protected static array $ROUTE = [];
        protected static array $MIDDLEWARES = [[]];
        protected static array $PATH = [];

        protected static function registerRoute(string $method, string $route, string|array|int $response, array $middlewares = []): void
        {
            $route = implode('/', [...self::$PATH, $route]);
            list($template, $params) = self::parseRouteTemplate($route);

            $middlewares = [...end(self::$MIDDLEWARES), ...$middlewares];
            $middlewares = implode(',', $middlewares);

            $line = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['line'];
            $file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['file'];

            self::$ROUTE[] = [
                'method' => $method,
                'template' => $template,
                'params' => $params,
                'middlewares' => $middlewares,
                'response' => $response,
                'file' => path($file),
                'line' => $line,
            ];
        }

        static function __callStatic($name, $arguments)
        {
            return match ($name) {
                'get',
                'post',
                'put',
                'delete', => self::registerRoute($name, ...$arguments),
                'add', => [
                    self::get(...$arguments),
                    self::post(...$arguments),
                ],
                default => null,
            };
        }

        static function path(string $path, Closure $wrapper): void
        {
            list($template) = self::parseRouteTemplate("$path...");
            $template = implode("/", [...self::$PATH, $template]);
            self::$PATH[] = $path;
            $wrapper();
            array_pop(self::$PATH);
        }

        static function middleware(array $middlewares, Closure $wrapper): void
        {
            self::$MIDDLEWARES[] = [...end(self::$MIDDLEWARES), ...$middlewares];
            $wrapper();
            array_pop(self::$MIDDLEWARES);
        }

        static function group(string $path, array $middlewares, Closure $wrapper): void
        {
            $wrapper = fn() => self::middleware($middlewares, $wrapper);
            $wrapper = fn() => self::path($path, $wrapper);
            $wrapper();
        }

        static function solve(array $globalMiddlewares = [])
        {
            self::$MIDDLEWARES = [$globalMiddlewares];
        }

        static function scan(): array
        {
            self::$ROUTE = [];

            foreach (array_reverse(Path::seekForDirs('system/routes')) as $path)
                foreach (Dir::seekForFile($path, true) as $file)
                    Import::only("$path/$file", false);

            return self::$ROUTE;
        }

        protected static function parseRouteTemplate(string $route): array
        {
            $params = [];
            $route = self::normalizeRoute($route);
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

        protected static function normalizeRoute(string $route): string
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
    }
}

return $interceptor;
