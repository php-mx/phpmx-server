<?php

namespace PhpMx\Input;

/**
 * Campo de input especializado para valores booleanos.
 * Converte automaticamente o valor recebido para bool antes de aplicar as regras herdadas.
 */
class InputFieldBool extends InputField
{
    /**
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $value Valor inicial convertido automaticamente para bool.
     */
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        $value = boolval(str_get_var($value));
        parent::__construct($name, $alias, $value);
    }
}
