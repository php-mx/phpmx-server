<?php

use PhpMx\Request;

if (!function_exists('url')) {

    /** Retorna uma string de URL */
    function url(string|array ...$params)
    {
        $url = [];
        $queryString = [];

        foreach ($params as $param) {
            if (is_string($param) && str_starts_with($param, '?'))
                parse_str(substr($param, 1), $param);

            if (is_array($param))
                $queryString = [...$queryString, ...$param];

            else
                $url[] = $param;
        }

        $url = implode('/', $url);

        if (!str_contains($url, '://')) {
            if (isset(parse_url($url)['scheme'])) {
                $url = "https://$url";
            } else {
                if (str_starts_with($url, '.'))
                    $url = path(implode('/', Request::path()), substr($url, 1));

                $url = path(Request::host(), $url);
                $url = (Request::ssl() ? 'https' : 'http') . '://' . $url;
            }
            $url = trim($url, '/');
        }

        $url = str_replace_all("//", "/", $url);
        $url = str_replace_first(":/", "://", $url);

        if (!is_blank($queryString))
            $url = "$url?" . urldecode(http_build_query($queryString));

        return $url;
    }
}
