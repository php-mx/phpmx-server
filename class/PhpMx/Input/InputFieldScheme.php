<?php

namespace PhpMx\Input;

/** Campo de input para validação e decodificação de esquemas JSON. */
class InputFieldScheme extends InputField
{
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        parent::__construct($name, $alias, $value);

        $this->scapePrepare(false);
        $this->preventTag(false);

        $this->validate(fn($v) => is_array($v) || is_json($v), 'Erro no campo [#name]');

        $this->sanitize(fn($value) => is_json($value) ? json_decode($value, true) : $value);
    }
}
