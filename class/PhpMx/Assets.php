<?php

namespace PhpMx;

use Exception;

/** Classe utilitária para envio e download de arquivos via resposta HTTP. */
abstract class Assets
{
    /** Envia um arquivo do projeto como resposta da requisição */
    static function send(): void
    {
        self::load(...func_get_args());
        Response::send();
    }

    /** Realiza o download de um arquivo do projeto como resposta da requisição */
    static function download(): void
    {
        self::load(...func_get_args());
        Response::download(true);
        Response::send();
    }

    /** Carrega um arquivo do projeto na resposta da requisição */
    static function load(): void
    {
        $file = path(...func_get_args());
        self::loadResponse($file);
        Response::download(false);
    }

    /** Retorna o ResponseFile do arquivo */
    protected static function loadResponse(string $file): void
    {
        if (!File::check($file))
            throw new Exception("File not found", STS_NOT_FOUND);

        Response::content(Import::content($file));
        Response::type(File::getEx($file));
        Response::download(File::getOnly($file));
        Response::status(STS_OK);
    }
}
