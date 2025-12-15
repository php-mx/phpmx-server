<?php

namespace PhpMx;

use Exception;

/** Classe utilitária para envio e download de arquivos via resposta HTTP. */
abstract class Assets
{
    /** Envia um arquivo do projeto como resposta da requisição */
    static function send(Response $response, ...$assetArgs): never
    {
        self::load(...func_get_args());
        $response->send();
    }

    /** Realiza o download de um arquivo do projeto como resposta da requisição */
    static function download(Response $response, ...$assetArgs): never
    {
        self::load(...func_get_args());
        $response->download(true);
        $response->send();
    }

    /** Carrega um arquivo do projeto na resposta da requisição */
    static function load(Response $response, ...$assetArgs): void
    {
        $file = path(...$assetArgs);
        self::loadResponse($response, $file);
        $response->download(false);
    }

    /** Retorna o ResponseFile do arquivo */
    protected static function loadResponse(Response $response, string $file): void
    {
        if (!File::check($file))
            throw new Exception("File not found", STS_NOT_FOUND);

        $response->content(Import::content($file));

        $response->type(File::getEx($file));
        $response->download(File::getOnly($file));
        $response->status(STS_OK);
    }
}
