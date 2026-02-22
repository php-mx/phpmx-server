<?php

namespace PhpMx;

use Closure;
use Exception;
use ReflectionMethod;

/**
 * Classe responsável pelo registro, organização e resolução de rotas HTTP.
 * Suporta agrupamento por caminho e middlewares, além de parâmetros dinâmicos na URL.
 */
abstract class Router
{
    protected static array $ROUTE = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];

    protected static array $CURRENT_MIDDLEWARE = [[]];
    protected static array $CURRENT_PATH = [];
    protected static bool $SCANNED = false;

    /**
     * Adiciona uma rota para responder por requisições GET e POST.
     * @param string $route Template da rota (ex: 'users/[#id]').
     * @param string|array|int $response Classe, array [classe, método] ou status HTTP de resposta.
     * @param array $middlewares Lista de middlewares da rota.
     */
    static function add(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::get($route, $response, $middlewares);
        self::post($route, $response, $middlewares);
    }

    /**
     * Adiciona uma rota para responder por requisições GET, POST, PUT e DELETE.
     * @param string $route Template da rota.
     * @param string|array|int $response Classe, array [classe, método] ou status HTTP de resposta.
     * @param array $middlewares Lista de middlewares da rota.
     */
    static function full(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::get($route, $response, $middlewares);
        self::post($route, $response, $middlewares);
        self::put($route, $response, $middlewares);
        self::delete($route, $response, $middlewares);
    }

    /**
     * Adiciona uma rota para responder por requisições GET.
     * @param string $route Template da rota.
     * @param string|array|int $response Classe, array [classe, método] ou status HTTP de resposta.
     * @param array $middlewares Lista de middlewares da rota.
     */
    static function get(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::defineRoute('GET', $route, $response, $middlewares);
    }

    /**
     * Adiciona uma rota para responder por requisições POST.
     * @param string $route Template da rota.
     * @param string|array|int $response Classe, array [classe, método] ou status HTTP de resposta.
     * @param array $middlewares Lista de middlewares da rota.
     */
    static function post(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::defineRoute('POST', $route, $response, $middlewares);
    }

    /**
     * Adiciona uma rota para responder por requisições PUT.
     * @param string $route Template da rota.
     * @param string|array|int $response Classe, array [classe, método] ou status HTTP de resposta.
     * @param array $middlewares Lista de middlewares da rota.
     */
    static function put(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::defineRoute('PUT', $route, $response, $middlewares);
    }

    /**
     * Adiciona uma rota para responder por requisições DELETE.
     * @param string $route Template da rota.
     * @param string|array|int $response Classe, array [classe, método] ou status HTTP de resposta.
     * @param array $middlewares Lista de middlewares da rota.
     */
    static function delete(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::defineRoute('DELETE', $route, $response, $middlewares);
    }

    /**
     * Define um prefixo de caminho para um conjunto de rotas declaradas no Closure.
     * @param string $path Prefixo de caminho a ser aplicado.
     * @param Closure $wrapper Função contendo as declarações de rotas.
     */
    static function path(string $path, Closure $wrapper): void
    {
        list($template) = self::parseRouteTemplate("$path...");
        $template = implode("/", [...self::$CURRENT_PATH, $template]);
        self::$CURRENT_PATH[] = $path;
        $wrapper();
        array_pop(self::$CURRENT_PATH);
    }

    /**
     * Define middlewares padrão para um conjunto de rotas declaradas no Closure.
     * @param array $middlewares Lista de middlewares a aplicar.
     * @param Closure $wrapper Função contendo as declarações de rotas.
     */
    static function middleware(array $middlewares, Closure $wrapper): void
    {
        self::$CURRENT_MIDDLEWARE[] = [...end(self::$CURRENT_MIDDLEWARE), ...$middlewares];
        $wrapper();
        array_pop(self::$CURRENT_MIDDLEWARE);
    }

    /**
     * Define caminho e middlewares padrão para um conjunto de rotas declaradas no Closure.
     * @param string $path Prefixo de caminho a ser aplicado.
     * @param array $middlewares Lista de middlewares a aplicar.
     * @param Closure $wrapper Função contendo as declarações de rotas.
     */
    static function group(string $path, array $middlewares, Closure $wrapper): void
    {
        $wrapper = fn() => self::middleware($middlewares, $wrapper);
        $wrapper = fn() => self::path($path, $wrapper);
        $wrapper();
    }

    /**
     * Resolve a requisição atual, executa os middlewares e envia a resposta ao cliente.
     * @param array $GLOBAL_MIDDLEWARES Middlewares globais executados antes dos middlewares de rota.
     */
    static function solve(array $GLOBAL_MIDDLEWARES = [])
    {
        list($middlewares, $wrapper) = Log::add('mx', 'router solve', function () {
            $routes = self::scan(Request::type());

            $routeMatch = self::getRouteMatch(Request::path(), $routes);

            if ($routeMatch) {
                list($template, $response, $params, $middlewares) = $routeMatch;
                self::setRequestRouteParams($template, $params);
                $wrapper = fn() => self::executeActionResponse($response);
            } else {
                $wrapper = fn() => throw new Exception('Route not found', STS_NOT_FOUND);
                $middlewares = [];
            }

            return [$middlewares, $wrapper];
        });

        Log::add('mx', 'route dispatch', function () use ($middlewares, $wrapper, $GLOBAL_MIDDLEWARES) {
            $wrapper = fn() => Log::add('mx', 'route action', $wrapper);

            $response = Middleware::run([...$GLOBAL_MIDDLEWARES, ...$middlewares], $wrapper);

            Response::content($response);
            Response::send();
        });
    }

    protected static function getRouteMatch(array $path, $routes): ?array
    {
        foreach ($routes as $template => $route)
            if (self::checkRouteMatch($path, $template))
                return $route;
        Log::add('mx', 'route matching not found');
        return null;
    }

    protected static function checkRouteMatch(array $path, string $template): bool
    {
        $template = trim($template, '/');
        $template = explode('/', $template);

        while (count($template)) {
            $expected = array_shift($template);
            $received = array_shift($path) ?? '';
            if ($expected === '...') return true;
            if (is_blank($received) && !is_blank($expected)) return false;
            if ($expected !== '#' && $received !== $expected) return false;
        }

        return count($path) === 0;
    }

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

    protected static function executeActionResponse(string|array|int $response)
    {
        $response = is_array($response) ? $response : [$response];
        $action = array_shift($response) ?? STS_NOT_FOUND;
        $data = Request::data();

        if (is_httpStatus($action)) {
            $status = $action;
            $message = array_shift($response) ?? env("STM_$status") ?? 'unknown';
            $message = prepare($message, $data);
            throw new Exception($message, $status);
        }

        if (is_int($action)) {
            throw new Exception('response route error', STS_INTERNAL_SERVER_ERROR);
        }

        if (is_stringable($action)) {
            $class = $action;
            $method = array_shift($response) ?? '__invoke';

            if (!class_exists($class))
                throw new Exception('route not implemented', STS_NOT_IMPLEMENTED);

            $__constructParams = [];

            if (method_exists($class, '__construct'))
                $__constructParams = self::getMethodParams($class, '__construct', $data);

            $controller = new $class(...$__constructParams);

            if (!method_exists($controller, $method))
                throw new Exception("Method [$method] does not exist in response class", STS_NOT_IMPLEMENTED);

            return $controller->{$method}(...self::getMethodParams($class, $method, $data));
        }

        throw new Exception('response route error', STS_INTERNAL_SERVER_ERROR);
    }

    protected static function getMethodParams(string|Object $class, string $method, array $data): array
    {
        $params = [];

        $reflection = new ReflectionMethod($class, $method);
        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            if (isset($data[$name]))
                $params[] = $data[$name];
            else if ($param->isDefaultValueAvailable())
                $params[] = $param->getDefaultValue();
            else
                throw new Exception("Parameter [$name] is required", STS_INTERNAL_SERVER_ERROR);
        }

        return $params;
    }

    protected static function scan(string $method): array
    {
        $method = strtoupper($method);

        $routes = self::$SCANNED ? self::$ROUTE : cache("routes", function () {
            self::$ROUTE = [];

            foreach (array_reverse(Path::seekForDirs('system/router')) as $path)
                foreach (Dir::seekForFile($path, true) as $file)
                    Import::only("$path/$file", false);

            return self::organize(self::$ROUTE);
        });

        self::$SCANNED = true;

        return $routes[$method] ?? [];
    }

    protected static function defineRoute(string $method, string $route, string|array|int $response, array $middlewares = []): void
    {
        $route = implode('/', [...self::$CURRENT_PATH, $route]);

        list($template, $params) = self::parseRouteTemplate($route);

        $middlewares = [...end(self::$CURRENT_MIDDLEWARE), ...$middlewares];

        self::$ROUTE[$method][$template] = [
            $template,
            $response,
            $params,
            $middlewares
        ];
    }

    protected static function organize(array $array): array
    {
        uksort($array, function ($a, $b) {
            $countA = substr_count($a, '/');
            $countB = substr_count($b, '/');

            if ($countA !== $countB) return $countB <=> $countA;

            $posA = strpos($a, '/');
            $posB = strpos($b, '/');

            if ($posA !== $posB) return $posB <=> $posA;

            $aParts = explode('/', $a);
            $bParts = explode('/', $b);

            $max = max(count($aParts), count($bParts));

            $peso = fn($part) => match ($part) {
                '...' => 2,
                '#' => 1,
                default => 0,
            };

            for ($i = 0; $i < $max; $i++) {
                $partA = $aParts[$i] ?? '';
                $partB = $bParts[$i] ?? '';

                $pesoA = $peso($partA);
                $pesoB = $peso($partB);

                if ($pesoA !== $pesoB) return $pesoA <=> $pesoB;
            }

            return 0;
        });

        return $array;
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
