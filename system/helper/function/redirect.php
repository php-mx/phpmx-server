<?php

if (!function_exists('redirect')) {

    /** Lança uma exception de redirecionamento */
    function redirect(): never
    {
        throw new Exception(url(...func_get_args()), STS_REDIRECT);
    }
}
