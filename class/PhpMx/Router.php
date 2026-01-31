<?php

namespace PhpMx;

use Exception;
use ReflectionMethod;

/** Classe responsável pelo registro, organização e resolução de rotas HTTP. */
class Router
{
    use RouterScanTrait;

    function __construct(protected array $GLOBAL_MIDDLEWARES = []) {}

    /** Resolve a requisição atual enviando a reposta ao cliente */
    function solve(?Request $contextRequest = null, ?Response $contextResponse = null)
    {
        $contextRequest = $contextRequest ?? new Request;
        $contextResponse = $contextResponse ?? new Response;

        list($middlewares, $wrapper) = Log::add('mx', 'router solve', function () use ($contextRequest, $contextResponse) {
            $routes = self::scan($contextRequest->type());

            $routeMatch = $this->getRouteMatch($contextRequest->path(), $routes);

            if ($routeMatch) {
                list($template, $response, $params, $middlewares) = $routeMatch;
                $this->setRequestRouteParams($contextRequest, $template, $params);
                $wrapper = fn() => $this->executeActionResponse($response, $contextRequest, $contextResponse);
            } else {
                $wrapper = fn() => throw new Exception('Route not found', STS_NOT_FOUND);
                $middlewares = $this->GLOBAL_MIDDLEWARES;
            }

            return [$middlewares, $wrapper];
        });

        Log::add('mx', 'route dispatch', function () use ($middlewares, $wrapper, $contextRequest, $contextResponse) {
            $wrapper = fn() => Log::add('mx', 'route action', $wrapper);

            $middlewareQueue = new MiddlewareQueue($contextRequest, $contextResponse);
            $response = $middlewareQueue([...$this->GLOBAL_MIDDLEWARES, ...$middlewares], $wrapper);

            $contextResponse->content($response);
            $contextResponse->send();
        });
    }

    /** Retorna o template da rota que corresponde a URL atual */
    protected function getRouteMatch(array $path, $routes): ?array
    {
        foreach ($routes as $template => $route)
            if ($this->checkRouteMatch($path, $template))
                return $route;
        Log::add('mx', 'route matching not found');
        return null;
    }

    /** Verifica se um template combina com a URL atual */
    protected function checkRouteMatch(array $path, string $template): bool
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

    /** Define os parametros da rota dentro do objeto de requisição */
    protected function setRequestRouteParams(Request $request, ?string $template, ?array $params): void
    {
        if (is_null($template)) return;

        $uri = $request->path();
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
            $request->set_route($var, $value);
    }

    /** Executa uma resposta de rota */
    protected function executeActionResponse(string|array|int $response, Request $contextRequest, Response $contextResponse)
    {
        $response = is_array($response) ? $response : [$response];
        $action = array_shift($response) ?? STS_NOT_FOUND;
        $data = $contextRequest->data();

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
                $__constructParams = $this->getMethodParams($class, '__construct', $data);

            $controller = new $class(...$__constructParams);

            if (is_extend($controller, Context::class)) {
                $controller->request = $contextRequest;
                $controller->response = $contextResponse;
            }

            if (!method_exists($controller, $method))
                throw new Exception("Method [$method] does not exist in response class", STS_NOT_IMPLEMENTED);

            return $controller->{$method}(...$this->getMethodParams($class, $method, $data));
        }

        throw new Exception('response route error', STS_INTERNAL_SERVER_ERROR);
    }

    /** Retorna os parametros que deve ser utilizados para chamar um metodo de um objeto de resposa */
    protected function getMethodParams(string|Object $class, string $method, array $data): array
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
}
