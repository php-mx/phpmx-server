<?php

use PhpMx\Context;

return new class extends Context {

    function __invoke(Closure $next)
    {
        $this->response->header('Mx-Cors', 'true');

        $this->response->header('Access-Control-Allow-Origin', '*');
        $this->response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $this->response->header('Access-Control-Allow-Headers', 'X-Requested-With');

        if ($this->request->server('HTTP_ORIGIN')) {
            $this->response->header('Access-Control-Allow-Origin', $this->request->server('HTTP_ORIGIN'));
            $this->response->header('Access-Control-Allow-Credentials', 'true');
            $this->response->header('Access-Control-Max-Age', 86400);
        }

        if ($this->request->type('OPTIONS')) {
            if ($this->request->server('HTTP_ACCESS_CONTROL_REQUEST_METHOD'))
                $this->response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

            if ($this->request->server('HTTP_ACCESS_CONTROL_REQUEST_HEADERS'))
                $this->response->header('Access-Control-Allow-Headers', $this->request->server('HTTP_ACCESS_CONTROL_REQUEST_HEADERS'));

            $this->response->status(STS_OK);
            $this->response->send();
        }

        return $next();
    }
};
