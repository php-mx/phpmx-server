<?php

namespace PhpMx;

use Exception;
use PhpMx\Input\InputField;
use PhpMx\Input\InputFieldBool;
use PhpMx\Input\InputFieldCaptcha;
use PhpMx\Input\InputFieldList;
use PhpMx\Input\InputFieldScheme;
use PhpMx\Input\InputFieldUpload;
use PhpMx\Input\InputFieldUploadImage;

/** Classe para gerenciamento de campos e validação de inputs da requisição. */
class Input
{
    protected array $dataValue = [];

    /** @var InputField[] */
    protected array $dataField = [];

    function __construct(array $dataValue = [])
    {
        $this->dataValue = array_map(fn($var) => str_get_var($var), $dataValue);
    }

    /** Retorna o objeto de um campo de input */
    function &field($name, $alias = null, mixed $default = null): InputField
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputField($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /** Retorna o objeto de um campo de input preparado pra receber um valor boleano */
    function &fieldBool($name, $alias = null, mixed $default = null): InputFieldBool
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldBool($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /** Retorna o objeto de um campo de input preparado pra receber um valor de lista */
    function &fieldList($name, $alias = null, mixed $default = null): InputFieldList
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldList($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /** Retorna o objeto de um campo de input preparado para receber um arquivo upload */
    function &fieldUpload($name, $alias = null, mixed $default = null): InputFieldUpload
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldUpload($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /** Retorna o objeto de um campo de input preparado para receber uma imagem b64 */
    function &fieldUploadImage($name, $alias = null, mixed $default = null): InputFieldUploadImage
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldUploadImage($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /** Retorna o objeto de um campo de input preparado para receber um codigo Captcha */
    function &fieldCaptcha($name, $alias = null, mixed $default = null): InputFieldCaptcha
    {
        if (!extension_loaded('gd'))
            throw new Exception("Extension [gd] is required to use Captcha.", STS_INTERNAL_SERVER_ERROR);

        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldCaptcha($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /** Retorna o objeto de um campo de input preparado para receber um array scheme */
    function &fieldScheme($name, $alias = null, mixed $default = null): InputFieldScheme
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldScheme($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /** Retorna o valor verificado e sanitizado de um campo do input */
    function get($fieldName): mixed
    {
        return $this->field($fieldName)->get();
    }

    /** Verifica se todos os campo do input passam no teste de verificação */
    function check(): bool
    {
        $this->data();
        return true;
    }

    /** Retorna os valores dos campos do input em forma de array */
    function data(?array $nameFields = null): array
    {
        $nameFields = $nameFields ?? array_keys($this->dataField);

        $return = [];

        foreach ($nameFields as $name)
            $return[$name] = $this->field($name)->get();

        return $return;
    }

    /** Retorna os valores recebidos dos campos do input em forma de array */
    function dataRecived(?array $nameFields = null): array
    {
        $nameFields = $nameFields ?? array_keys($this->dataField);

        $return = [];

        foreach ($nameFields as $name)
            if ($this->field($name)->recived())
                $return[$name] = $this->field($name)->get();

        return $return;
    }

    /** Lança uma Exception em nome do input */
    function send($message, bool|int $status = false): void
    {
        if ($status === true) $status = STS_OK;
        if ($status === false || !is_httpStatus($status)) $status = STS_BAD_REQUEST;

        $send = ['message' => $message,];

        throw new Exception(json_encode($send), $status);
    }
}
