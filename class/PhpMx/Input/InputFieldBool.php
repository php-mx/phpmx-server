<?php

namespace PhpMx\Input;

class InputFieldBool extends InputField
{
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        $value = boolval(str_get_var($value));
        parent::__construct($name, $alias, $value);
    }
}
