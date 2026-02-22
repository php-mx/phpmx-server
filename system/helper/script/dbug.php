<?php

if (!function_exists('dbugpre')) {

    /**
     * Realiza o var_dump de variáveis dentro de uma tag HTML pre.
     * @param mixed ...$params Variáveis para depuração.
     * @return void
     */
    function dpre(mixed ...$params): void
    {
        echo '<pre>';
        d(...$params);
        echo '</pre>';
    }
}

if (!function_exists('ddpre')) {

    /**
     * Realiza o var_dump de variáveis dentro de uma tag HTML pre encerrando a execução do sistema.
     * @param mixed ...$params Variáveis para depuração.
     * @return void
     */
    function ddpre(mixed ...$params): void
    {
        dpre(...$params);
        die;
    }
}
