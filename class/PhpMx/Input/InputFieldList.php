<?php

namespace PhpMx\Input;

/** Campo de input para listas representadas como string. */
class InputFieldList extends InputField
{
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        $value = is_array($value) ? implode(',', $value) : $value;
        parent::__construct($name, $alias, $value);
    }
}
