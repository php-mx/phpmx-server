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

/**
 * Classe para gerenciamento de campos e validação de inputs da requisição.
 * Encapsula os dados recebidos e fornece campos tipados com sanitização e verificação automática.
 */
class Input
{
    protected array $dataValue = [];

    /** @var InputField[] */
    protected array $dataField = [];

    /**
     * @param array|null $dataValue Dados de entrada (opcional, usa Request::data() por padrão).
     */
    function __construct(?array $dataValue = null)
    {
        $dataValue = $dataValue ?? Request::data();

        $this->dataValue = array_map(fn($var) => str_get_var($var), $dataValue);
    }

    /**
     * Retorna o objeto de um campo de input genérico.
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $default Valor padrão caso o campo não seja recebido.
     * @return InputField
     */
    function &field($name, $alias = null, mixed $default = null): InputField
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputField($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /**
     * Retorna o objeto de um campo de input preparado para receber um valor booleano.
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $default Valor padrão caso o campo não seja recebido.
     * @return InputFieldBool
     */
    function &fieldBool($name, $alias = null, mixed $default = null): InputFieldBool
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldBool($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /**
     * Retorna o objeto de um campo de input preparado para receber um valor de lista.
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $default Valor padrão caso o campo não seja recebido.
     * @return InputFieldList
     */
    function &fieldList($name, $alias = null, mixed $default = null): InputFieldList
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldList($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /**
     * Retorna o objeto de um campo de input preparado para receber um arquivo de upload.
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $default Valor padrão caso o campo não seja recebido.
     * @return InputFieldUpload
     */
    function &fieldUpload($name, $alias = null, mixed $default = null): InputFieldUpload
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldUpload($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /**
     * Retorna o objeto de um campo de input preparado para receber uma imagem em base64.
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $default Valor padrão caso o campo não seja recebido.
     * @return InputFieldUploadImage
     */
    function &fieldUploadImage($name, $alias = null, mixed $default = null): InputFieldUploadImage
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldUploadImage($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /**
     * Retorna o objeto de um campo de input preparado para receber um código Captcha.
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $default Valor padrão caso o campo não seja recebido.
     * @return InputFieldCaptcha
     */
    function &fieldCaptcha($name, $alias = null, mixed $default = null): InputFieldCaptcha
    {
        phpex('gd');
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldCaptcha($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /**
     * Retorna o objeto de um campo de input preparado para receber um array scheme.
     * @param string $name Nome do campo.
     * @param string|null $alias Rótulo amigável para mensagens de erro.
     * @param mixed $default Valor padrão caso o campo não seja recebido.
     * @return InputFieldScheme
     */
    function &fieldScheme($name, $alias = null, mixed $default = null): InputFieldScheme
    {
        $this->dataField[$name] = $this->dataField[$name] ?? new InputFieldScheme($name, $alias, $this->dataValue[$name] ?? $default);
        return $this->dataField[$name];
    }

    /**
     * Retorna o valor verificado e sanitizado de um campo do input.
     * @param string $fieldName Nome do campo.
     * @return mixed
     */
    function get($fieldName): mixed
    {
        return $this->field($fieldName)->get();
    }

    /**
     * Verifica se todos os campos do input passam na validação, lançando Exception em caso de falha.
     * @return bool
     */
    function check(): bool
    {
        $this->data();
        return true;
    }

    /**
     * Retorna os valores validados dos campos do input em forma de array.
     * @param array|null $nameFields Lista de campos a retornar (opcional, retorna todos por padrão).
     * @return array
     */
    function data(?array $nameFields = null): array
    {
        $nameFields = $nameFields ?? array_keys($this->dataField);

        $return = [];

        foreach ($nameFields as $name)
            $return[$name] = $this->field($name)->get();

        return $return;
    }

    /**
     * Retorna apenas os valores dos campos efetivamente recebidos na requisição.
     * @param array|null $nameFields Lista de campos a considerar (opcional, considera todos por padrão).
     * @return array
     */
    function dataRecived(?array $nameFields = null): array
    {
        $nameFields = $nameFields ?? array_keys($this->dataField);

        $return = [];

        foreach ($nameFields as $name)
            if ($this->field($name)->recived())
                $return[$name] = $this->field($name)->get();

        return $return;
    }

    /**
     * Lança uma Exception em nome do input com mensagem e status HTTP definidos.
     * @param string $message Mensagem de erro.
     * @param bool|int $status Status HTTP (false usa STS_BAD_REQUEST, true usa STS_OK).
     */
    function send($message, bool|int $status = false): void
    {
        if ($status === true) $status = STS_OK;
        if ($status === false || !is_httpStatus($status)) $status = STS_BAD_REQUEST;

        $send = ['message' => $message,];

        throw new Exception(json_encode($send), $status);
    }
}
