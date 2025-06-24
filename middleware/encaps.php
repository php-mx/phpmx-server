<?php

use PhpMx\Log;
use PhpMx\Response;

return new class {

    function __invoke(Closure $next)
    {
        try {
            $this->encapsResponse($next());
        } catch (Exception | Error $e) {
            $this->encapsException($e);
        }
        Response::send();
    }

    function encapsResponse($response): void
    {
        if (is_httpStatus($response))
            throw new Exception('', $response);

        if (is_json($response))
            $response = json_decode($response, true);

        $status = Response::getStatus() ?? STS_OK;

        $response = [
            'info' => [
                'mx' => true,
                'status' => $status,
                'error' => is_httpStatusError($status),
                'message' => env("STM_$status"),
            ],
            'data' => $response
        ];

        Response::status($status);

        if (env('DEV')) $response['log'] = Log::getArray();

        Response::content($response);
    }

    function encapsException(Exception | Error $e): void
    {
        $status = $e->getCode();
        $message = $e->getMessage();

        if (!is_httpStatus($status))
            $status = !is_class($e, Error::class) ? STS_BAD_REQUEST : STS_INTERNAL_SERVER_ERROR;

        if ($status == STS_REDIRECT) {
            Response::header('location', $message);
        } else {
            if (empty($message))
                $message = env("STM_$status");

            if (is_json($message))
                $message = json_decode($message, true);

            if (!is_array($message) || !isset($message['message']))
                $message = ['message' => $message];

            $response = [
                'info' => [
                    'mx' => true,
                    'status' => $status,
                    'error' => $status > 399,
                    ...$message
                ],
                'data' => null
            ];

            Response::header('Mx-Error-Message', $response['info']['message']);
            Response::header('Mx-Error-Status', $response['info']['status']);

            if (env('DEV') && $response['info']['error']) {
                $response['info']['file'] = $e->getFile();
                $response['info']['line'] = $e->getLine();
                Response::header('Mx-Error-File', $response['info']['file']);
                Response::header('Mx-Error-Line', $response['info']['line']);
            }

            Response::cache(false);

            if (env('DEV')) $response['log'] = Log::getArray();

            Response::content($response);
        }

        Response::status($status);
    }
};
