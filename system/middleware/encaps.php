<?php

use PhpMx\Context;
use PhpMx\Log;

return new class extends Context {

    function __invoke(Closure $next)
    {
        try {
            $this->encapsResponse($next());
        } catch (Throwable $e) {
            $this->encapsException($e);
        }

        $this->response->send();
    }

    function encapsResponse($response): void
    {
        if (is_httpStatus($response))
            throw new Exception('', $response);

        if (is_json($response))
            $response = json_decode($response, true);

        $status = $this->response->getStatus() ?? STS_OK;

        $response = [
            'info' => [
                'mx' => true,
                'status' => $status,
                'error' => is_httpStatusError($status),
                'message' => env("STM_$status"),
            ],
            'data' => $response
        ];

        $this->response->status($status);

        if (env('DEV')) $response['log'] = Log::getArray();

        $this->response->content($response);
    }

    function encapsException(Throwable $e): void
    {
        $status = $e->getCode();
        $message = $e->getMessage();

        if (!is_httpStatus($status))
            $status = !is_class($e, Error::class) ? STS_BAD_REQUEST : STS_INTERNAL_SERVER_ERROR;

        if ($status == STS_REDIRECT) {
            $this->response->header('location', $message);
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
                    'error' => is_httpStatusError($status),
                    ...$message
                ],
                'data' => null
            ];

            $this->response->header('Mx-Error-Message', $response['info']['message']);
            $this->response->header('Mx-Error-Status', $response['info']['status']);

            if (env('DEV') && is_httpStatusError($status)) {
                $response['info']['file'] = $e->getFile();
                $response['info']['line'] = $e->getLine();
                $this->response->header('Mx-Error-File', $response['info']['file']);
                $this->response->header('Mx-Error-Line', $response['info']['line']);
            }

            $this->response->cache(false);

            if (env('DEV')) $response['log'] = Log::getArray();

            $this->response->content($response);
        }

        $this->response->status($status);
    }
};
