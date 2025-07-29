<?php

namespace PhpMx\Input;

/** Classe utilitária para gerenciamento de mensagens de erro em inputs. */
abstract class InputMessage
{
    protected static array $TYPE = [
        FILTER_VALIDATE_IP => 'O campo [#name] precisa ser um endereço IP',
        FILTER_VALIDATE_INT => 'O campo [#name] precisa ser um numero inteiro',
        FILTER_VALIDATE_MAC => 'O campo [#name] precisa ser um endereço MAC',
        FILTER_VALIDATE_URL => 'O campo [#name] precisa ser uma URL',
        FILTER_VALIDATE_EMAIL => 'O campo [#name] precisa ser um email',
        FILTER_VALIDATE_FLOAT => 'O campo [#name] precisa ser um numero',
        FILTER_VALIDATE_DOMAIN => 'O campo [#name] precisa ser um dominio',
        FILTER_VALIDATE_REGEXP => 'O campo [#name] precisa ser um a expressão regular',
        FILTER_VALIDATE_BOOLEAN => 'O campo [#name] precisa ser um valor booleano',
        'required' => 'O campo [#name] é obrigatório',
        'preventTag' => 'O campo [#name] contem um valor inválido',
        'default' => 'O campo [#name] contem um erro',
        'equal' => 'O campo [#name] deve ser igual o campo [#equal]',
        'fileSize' => 'o arquivo [#name] é um arquivo grande demais',
        'fileError' => 'arquivo [#name] não foi recebido corretamente',
    ];

    /** Define ou altera uma mensagem padrão para um tipo de resposta */
    static function set(int|string $type, ?string $message)
    {
        self::$TYPE[$type] = $message;
    }

    /** Retorna a mensagem padrão para um tipo de resposta */
    static function get(string $type)
    {
        return self::$TYPE[$type] ?? null;
    }
}
