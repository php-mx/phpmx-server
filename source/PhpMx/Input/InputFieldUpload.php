<?php

namespace PhpMx\Input;

/**
 * Campo de input para validação de arquivos enviados via upload.
 * Verifica automaticamente erros de envio, tamanho e integridade do arquivo recebido.
 */
class InputFieldUpload extends InputField
{
    /**
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $value Valor inicial (array de arquivo do $_FILES).
     */
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
