<?php

namespace PhpMx\Reflection;

use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Router;
use ReflectionClass;
use ReflectionMethod;

abstract class ReflectionRouterFile extends BaseReflectionFile
{
    static function scheme(string $file): array
    {
        $schemes = [];

        /** @var Router|mixed $interceptor */
        $interceptor = new class extends Router {
            function intercept(string $file): array
            {
                $ROUTE = self::$ROUTE;
                $CURRENT_MIDDLEWARE = self::$CURRENT_MIDDLEWARE;
                $CURRENT_PATH = self::$CURRENT_PATH;
                $SCANNED = self::$SCANNED;

                Import::only($file);

                $intercepted = self::$ROUTE;

                self::$ROUTE = $ROUTE;
                self::$CURRENT_MIDDLEWARE = $CURRENT_MIDDLEWARE;
                self::$CURRENT_PATH = $CURRENT_PATH;
                self::$SCANNED = $SCANNED;

                return $intercepted;
            }
        };

        foreach ($interceptor->intercept($file) as $method => $routes) {
            foreach ($routes as $route) {
                $responseInfo = self::extractRouteResponse($route[1]);

                $schemes[] = array_filter([
                    '_key' => md5("route:" . strtolower($method) . ":" . $route[0]),
                    '_type' => 'route',
                    '_file' => path($file),
                    '_origin' => Path::origin($file),

                    'path' => $route[0],
                    'method' => strtoupper($method),
                    'middlewares' => $route[3] ?? [],
                    'response' => $responseInfo,
                ]);
            }
        }

        return array_filter($schemes);
    }

    protected static function extractRouteResponse($response): array
    {
        if (is_int($response)) {
            return ['type' => 'status', 'code' => $response, 'description' => ''];
        }

        $parts = is_array($response) ? $response : [$response];
        $controller = array_shift($parts);
        $method = array_shift($parts) ?? '__invoke';

        $info = [
            '_key' => md5("class:$controller"),
            '_type' => 'class',
            '_file' => null,
            '_line' => null,
            '_origin' => null,

            'type' => 'class',
            'class' => $controller,
            'method' => $method,
            'callable' => false,
        ];

        if (class_exists($controller)) {
            $reflection = new ReflectionClass($controller);
            $info['_file'] = path($reflection->getFileName());
            $info['_origin'] = Path::origin(path($reflection->getFileName()));

            if (method_exists($controller, $method)) {
                $refMethod = new ReflectionMethod($controller, $method);
                $info['_line'] = $refMethod->getStartLine();
                $info['callable'] = true;

                $doc = self::parseDocBlock($refMethod->getDocComment());
                $info['description'] = $doc['description'] ?? null;
            } else {
                $info['_line'] = $reflection->getStartLine();
            }
        }

        return $info;
    }
}
