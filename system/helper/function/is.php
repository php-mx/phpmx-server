<?php

if (!function_exists('is_httpStatus')) {

    /**
     * Verifica se uma variável corresponde a um status HTTP válido (100~599).
     * @param mixed $var Variável a verificar.
     * @return bool
     */
    function is_httpStatus($var): bool
    {
        return is_numeric($var) && $var >= 100 && $var <= 599;
    }
}

if (!function_exists('is_httpStatusError')) {

    /**
     * Verifica se uma variável corresponde a um status de erro HTTP (400~599).
     * @param mixed $var Variável a verificar.
     * @return bool
     */
    function is_httpStatusError($var): bool
    {
        return is_numeric($var) && $var >= 400 && $var < 600;
    }
}
