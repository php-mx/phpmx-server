<?php

if (!function_exists('is_httpStatus')) {

    /** Verifica se uma variavel corresponde a um status HTTP (100~599) */
    function is_httpStatus($var): bool
    {
        return is_numeric($var) && $var >= 100 && $var <= 599;
    }
}

if (!function_exists('is_httpStatusError')) {

    /** Verifica se uma variavel corresponde a um status de erro HTTP (300~599) */
    function is_httpStatusError($var): bool
    {
        return is_numeric($var) && $var >= 300 && $var <= 599;
    }
}
