<?php

namespace PhpMx\Input;

/**
 * Campo de input para validação e decodificação de esquemas JSON.
 * Aceita arrays ou strings JSON, decodificando automaticamente para array após validação.
 */
class InputFieldScheme extends InputField
{
    /**
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $value Valor inicial (array ou string JSON).
     */
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        parent::__construct($name, $alias, $value);

        $this->scapePrepare(false);
        $this->preventTag(false);

        $this->validate(fn($v) => is_array($v) || is_json($v), 'Erro no campo [#name]');

        $this->sanitize(fn($value) => is_json($value) ? json_decode($value, true) : $value);
    }
}
