<?php

namespace PhpMx\Input;

/** Campo de input para validação de arquivos enviados (upload). */
class InputFieldUpload extends InputField
{
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        parent::__construct($name, $alias, $value);

        $this->preventTag(false);
        $this->scapePrepare(false);

        $this->validate(fn($v) => is_array($v), '[#name] não foi enviado');
        $this->validate(fn($v) => $v['error'] != 1 && $v['error'] != 2, 'fileSize');
        $this->validate(fn($v) => $v['error'] == 0, 'fileError');
    }
}
