<?php

namespace PhpMx\Input;

use PhpMx\Mime;

/** Campo de input para validação de imagens base64. */
class InputFieldUploadImage extends InputField
{
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        parent::__construct($name, $alias, $value);

        $this->validate(function ($v) {
            $mime = explode(':', $v)[1];
            $mime = explode(';', $mime)[0];
            return Mime::checkMimeMime($mime, 'png', 'jpg', 'jpeg', 'webp');
        }, 'Arquivo não é de nenhum tipo permitio');
    }

    /** Verifica se o input foi recebido */
    function recived(): bool
    {
        $recived = is_image_base64($this->value);

        if (!$recived && $this->required)
            $this->send($this->requiredErrorMessage, $this->requiredErrorStatus);

        return $recived;
    }
}
