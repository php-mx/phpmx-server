<?php

if (!function_exists('dbugpre')) {

    /** Realiza o var_dump de variaveis dentro de uma tag HTML pre */
    function dbugpre(mixed ...$params): void
    {
        echo '<pre>';
        dbug(...$params);
        echo '</pre>';
    }
}

if (!function_exists('ddpre')) {

    /** Realiza o var_dump de variaveis dentro de uma tag HTML pre finalizando o sistema */
    function ddpre(mixed ...$params): void
    {
        dbugpre(...$params);
        die;
    }
}
