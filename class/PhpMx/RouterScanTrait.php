<?php

namespace PhpMx;

use Closure;

trait RouterScanTrait
{
    protected static array $ROUTE = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];

    protected static array $CURRENT_MIDDLEWARE = [[]];
    protected static array $CURRENT_PATH = [];
    protected static bool $SCANNED = false;

    /** Adiciona uma rota para responder por requisições GET e POST */
    static function add(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::get($route, $response, $middlewares);
        self::post($route, $response, $middlewares);
    }

    /** Adiciona uma rota para responder por requisições GET, POST, PUT e DELETE */
    static function full(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::get($route, $response, $middlewares);
        self::post($route, $response, $middlewares);
        self::put($route, $response, $middlewares);
        self::delete($route, $response, $middlewares);
    }

    /** Adiciona uma rota para responder por requisições GET */
    static function get(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::defineRoute('GET', $route, $response, $middlewares);
    }

    /** Adiciona uma rota para responder por requisições POST */
    static function post(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::defineRoute('POST', $route, $response, $middlewares);
    }

    /** Adiciona uma rota para responder por requisições PUT */
    static function put(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::defineRoute('PUT', $route, $response, $middlewares);
    }

    /** Adiciona uma rota para responder por requisições DELETE */
    static function delete(string $route, string|array|int $response, array $middlewares = []): void
    {
        self::defineRoute('DELETE', $route, $response, $middlewares);
    }

    /** Adiciona um caminho padrão para um conjunto de rotas */
    static function path(string $path, Closure $wrapper): void
    {
        list($template) = self::parseRouteTemplate("$path...");
        $template = implode("/", [...self::$CURRENT_PATH, $template]);
        self::$CURRENT_PATH[] = $path;
        $wrapper();
        array_pop(self::$CURRENT_PATH);
    }

    /** Adiciona um middlewares padrão para um conjunto de rotas */
    static function middleware(array $middlewares, Closure $wrapper): void
    {
        self::$CURRENT_MIDDLEWARE[] = [...end(self::$CURRENT_MIDDLEWARE), ...$middlewares];
        $wrapper();
        array_pop(self::$CURRENT_MIDDLEWARE);
    }

    /** Adiciona caminho e middlewares para um conjunto de rotas */
    static function group(string $path, array $middlewares, Closure $wrapper): void
    {
        $wrapper = fn() => self::middleware($middlewares, $wrapper);
        $wrapper = fn() => self::path($path, $wrapper);
        $wrapper();
    }

    /** Retorna todas as rotas compativeis com um metodo especifico */
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

    /** Adiciona uma rota para interpretação */
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

    /** Organiza um array de rotas preparando para a interpretação */
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
}
