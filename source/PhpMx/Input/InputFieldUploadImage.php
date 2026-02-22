<?php

namespace PhpMx\Input;

use PhpMx\Mime;

/**
 * Campo de input para validação de imagens enviadas em formato base64.
 * Aceita apenas imagens nos formatos PNG, JPG, JPEG e WEBP.
 */
class InputFieldUploadImage extends InputField
{
    /**
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $value Valor inicial (string de imagem em base64).
     */
    function __construct(string $name, ?string $alias = null, mixed $value = null)
    {
        parent::__construct($name, $alias, $value);

        $this->validate(function ($v) {
            $mime = explode(':', $v)[1];
            $mime = explode(';', $mime)[0];
            return Mime::checkMimeMime($mime, 'png', 'jpg', 'jpeg', 'webp');
        }, 'Arquivo não é de nenhum tipo permitio');
    }

    /**
     * Verifica se o input foi recebido como uma imagem base64 válida.
     * @return bool
     */
    function recived(): bool
    {
        $recived = is_image_base64($this->value);

        if (!$recived && $this->required)
            $this->send($this->requiredErrorMessage, $this->requiredErrorStatus);

        return $recived;
    }
}
