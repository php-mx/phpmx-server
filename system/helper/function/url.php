<?php

use PhpMx\Request;

if (!function_exists('url')) {

    /**
     * Retorna uma string de URL composta pelos argumentos fornecidos.
     * Argumentos em array ou iniciados com '?' são tratados como query string.
     * URLs relativas são resolvidas automaticamente com base no host e protocolo da requisição atual.
     * @param string|array ...$params Partes da URL ou arrays/strings de query string.
     * @return string
     */
    function url(string|array ...$params): string
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
