<?php

namespace PhpMx\Input;

/**
 * Campo de input para listas representadas como string separada por vírgulas.
 * Converte automaticamente arrays recebidos para string antes de aplicar as regras herdadas.
 */
class InputFieldList extends InputField
{
    /**
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $value Valor inicial (array é convertido automaticamente para string CSV).
     */
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        $value = is_array($value) ? implode(',', $value) : $value;
        parent::__construct($name, $alias, $value);
    }
}
