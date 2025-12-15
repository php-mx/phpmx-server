<?php

namespace PhpMx;

use Closure;
use Exception;

/** Classe responsável pela execução encadeada de middlewares. */
class MiddlewareQueue
{
    protected array $QUEUE = [];

    function __construct(protected Request $request, protected Response $response) {}

    /** Executa uma fila de middlewares retornando a action */
    function __invoke(array $queue, Closure $action)
    {
        $queue[] = $action;
        return self::execute($queue);
    }

    protected function execute(mixed &$queue): mixed
    {
        if (count($queue)) {
            $middleware = array_shift($queue);
            $middleware = self::getCallable($middleware);
            $next = fn() => self::execute($queue);
            return $middleware($next);
        }

        return null;
    }

    protected function getCallable(mixed $middleware)
    {
        if (is_array($middleware))
            return fn($next) => $this([...$middleware], $next);

        if (is_string($middleware)) {

            $action = remove_accents($middleware);
            $action = explode('.', $action);

            $actionFile = path(...$action);
            $actionFile = File::setEx($actionFile, 'php');
            $actionFile = path('system/middleware', $actionFile);
            $actionFile = Path::seekForFile($actionFile);

            if (!$actionFile)
                throw new Exception("Middleware [$middleware] not found");

            $action = Import::return($actionFile);

            if (!is_object($action))
                throw new Exception("Middleware [$middleware] not is object");

            if (is_extend($action, Context::class)) {
                $action->request = $this->request;
                $action->response = $this->response;
            }

            if (!is_callable($action))
                throw new Exception("Middleware [$middleware] cannot be called");

            return fn($next) => Log::add('middleware', $middleware, fn() => $action($next));
        }

        if (is_closure($middleware))
            return $middleware;

        if (is_null($middleware))
            return fn($next) => $next();

        throw new Exception('Impossible middleware resolve');
    }
}
