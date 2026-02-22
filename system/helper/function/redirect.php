<?php

if (!function_exists('redirect')) {

    /**
     * Lança uma Exception de redirecionamento para a URL composta pelos argumentos fornecidos.
     * @param string ...$params Partes da URL de destino.
     * @return void
     */
    function redirect(): void
    {
        throw new Exception(url(...func_get_args()), STS_REDIRECT);
    }
}
