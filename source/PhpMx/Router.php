<?php

namespace PhpMx;

use Closure;
use Exception;
use ReflectionMethod;


abstract class Router
{
    protected static array $ROUTE = [];
    protected static array $MIDDLEWARES = [[]];
    protected static array $PATH = [];

    /** Adiciona uma rota para responder por requisições GET */
    static function get(string $route, string $response, array $middlewares = []): void
    {
        if (IS_GET) self::defineRoute($route, $response, $middlewares);
    }

    /** Adiciona uma rota para responder por requisições POST */
    static function post(string $route, string $response, array $middlewares = []): void
    {
        if (IS_POST) self::defineRoute($route, $response, $middlewares);
    }

    /** Adiciona uma rota para responder por requisições PUT */
    static function put(string $route, string $response, array $middlewares = []): void
    {
        if (IS_PUT) self::defineRoute($route, $response, $middlewares);
    }

    /** Adiciona uma rota para responder por requisições DELETE */
    static function delete(string $route, string $response, array $middlewares = []): void
    {
        if (IS_DELETE) self::defineRoute($route, $response, $middlewares);
    }

    /** Adiciona um caminho padrão para um conjunto de rotas */
    static function path(string $path, Closure $wrapper): void
    {
        list($template) = self::parseRouteTemplate("$path...");
        $template = implode("/", [...self::$PATH, $template]);
        if (self::checkRouteMatch($template)) {
            self::$PATH[] = $path;
            $wrapper();
            array_pop(self::$PATH);
        }
    }

    /** Adiciona um middlewares padrão para um conjunto de rotas */
    static function middleware(array $middlewares, Closure $wrapper): void
    {
        self::$MIDDLEWARES[] = [...end(self::$MIDDLEWARES), ...$middlewares];
        $wrapper();
        array_pop(self::$MIDDLEWARES);
    }

    /** Adiciona caminho e middlewares para um conjunto de rotas */
    static function group(string $path, array $middlewares, Closure $wrapper): void
    {
        $wrapper = fn() => self::middleware($middlewares, $wrapper);
        $wrapper = fn() => self::path($path, $wrapper);
        $wrapper();
    }

    /** Resolve a requisição atual enviando a reposta ao cliente */
    static function solve(array $globalMiddlewares = [])
    {
        list($middlewares, $wrapper) = log_add('mx', 'router solve', [], function () use ($globalMiddlewares) {
            $paths = Path::seekDirs('routes');
            $paths = array_reverse($paths);

            foreach ($paths as $path)
                foreach (Dir::seekForFile($path, true) as $file)
                    Import::only("$path/$file", true);

            $routeMatch = self::getRouteMatch();

            if ($routeMatch) {
                list($template, $response, $params, $middlewares) = $routeMatch;
                self::setRequestRouteParams($template, $params);
                $wrapper = fn() => self::executeActionResponse($response, Request::data());
                $middlewares = [...$globalMiddlewares, ...$middlewares];
            } else {
                $wrapper = fn() => throw new Exception('Route not found', STS_NOT_FOUND);
                $middlewares = $globalMiddlewares;
            }
            return [$middlewares, $wrapper];
        });

        log_add('mx', 'route dispatch', [], function () use ($middlewares, $wrapper) {
            $wrapper = fn() => log_add('mx', 'route action', [], $wrapper);

            $response = Middleware::run($middlewares, $wrapper);

            Response::content($response);
            Response::send();
        });
    }

    /** Adiciona uma rota para interpretação */
    protected static function defineRoute(string $route, string $response, array $middlewares = []): void
    {
        $route = implode('/', [...self::$PATH, $route]);

        list($template, $params) = self::parseRouteTemplate($route);

        $middlewares = [...end(self::$MIDDLEWARES), ...$middlewares];

        self::$ROUTE[$template] = [
            $template,
            $response,
            $params,
            $middlewares
        ];
    }

    /** Explode uma rota em [template,params] */
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

    /** Limpa uma string para ser utilziada como rota */
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

    /** Retorna o template da rota que corresponde a URL atual */
    protected static function getRouteMatch(): ?array
    {
        $routes = self::organize(self::$ROUTE);
        foreach ($routes as $template => $route)
            if (self::checkRouteMatch($template)) {
                log_add('mx', 'route matching [[#]]', [$template]);
                return $route;
            }
        log_add('mx', 'route matching not found');
        return null;
    }
    /** Verifica se um template combina com a URL atual */
    protected static function checkRouteMatch(string $template): bool
    {
        $uri = Request::path();

        $template = trim($template, '/');
        $template = explode('/', $template);

        while (count($template)) {
            $expected = array_shift($template);
            $received = array_shift($uri) ?? '';
            if ($expected === '...') return true;
            if (is_blank($received) && !is_blank($expected)) return false;
            if ($expected !== '#' && $received !== $expected) return false;
        }

        return count($uri) === 0;
    }

    /** Organiza um array de rotas preparando para a interpretação */
    protected static function organize(array $array): array
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

    /** Define os parametros da rota dentro do objeto de requisição */
    protected static function setRequestRouteParams(?string $template, ?array $params): void
    {
        if (is_null($template)) return;

        $uri = Request::path();
        $dataParams = [];

        foreach ($params ?? [] as $pos => $name) {
            $value = $uri[$pos];
            $dataParams[$name ?? count($dataParams)] = $value;
        }

        if (str_ends_with($template, '...')) {
            $template = explode('/', $template);
            array_pop($template);
            $dataParams = [...$dataParams, ...array_slice($uri, count($template))];
        }

        foreach ($dataParams as $var => $value)
            Request::set_route($var, $value);
    }

    /** Executa uma resposta de rota */
    protected static function executeActionResponse(string $response, array $data = [])
    {
        if (is_httpStatus($response))
            throw new Exception('', $response);

        if (is_int($response))
            throw new Exception('response route error', STS_INTERNAL_SERVER_ERROR);


        list($class, $method) = explode(':', "$response:default");

        $class = str_replace('.', '/', $class);
        $class = explode('/', $class);
        $class = array_map(fn($v) => ucfirst($v), $class);
        $class = path('Controller', ...$class);
        $class = str_replace('/', '\\', $class);

        if (!class_exists($class))
            throw new Exception('route not implemented', STS_NOT_IMPLEMENTED);

        $params = [];
        if (method_exists($class, '__construct')) {
            $reflection = new ReflectionMethod($class, '__construct');
            foreach ($reflection->getParameters() as $param) {
                $name = $param->getName();
                if (isset($data[$name])) {
                    $params[] = $data[$name];
                } else if ($param->isDefaultValueAvailable()) {
                    $params[] = $param->getDefaultValue();
                } else {
                    throw new Exception("Parameter [$name] is required", STS_INTERNAL_SERVER_ERROR);
                }
            }
        }

        $response = new $class(...$params);

        if (!method_exists($response, $method))
            throw new Exception("Method [$method] does not exist in response class", STS_NOT_IMPLEMENTED);

        $params = [];
        $reflection = new ReflectionMethod($response, $method);
        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            if (isset($data[$name])) {
                $params[] = $data[$name];
            } else if ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                throw new Exception("Parameter [$name] is required", STS_INTERNAL_SERVER_ERROR);
            }
        }

        return $response->{$method}(...$params) ?? null;
    }
}
