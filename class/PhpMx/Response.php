<?php

namespace PhpMx;

/** Classe para construção e envio de respostas HTTP. */
abstract class Response
{
    protected static array $HEADER = [];
    protected static ?int $STATUS = null;
    protected static ?string $TYPE = null;
    protected static mixed $CONTENT = null;
    protected static ?string $CACHE = null;
    protected static bool $DOWNLOAD = false;
    protected static ?string $DOWNLOAD_NAME = null;

    /** Define o status HTTP da resposta */
    static function status(?int $status, bool $replace = true)
    {
        self::$STATUS = $replace ? $status : (self::$STATUS ?? $status);
    }

    /** Define um cabeçalho para a resposta */
    static function header(string|array $name, ?string $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $n => $v)
                self::header($n, $v);
        } else {
            self::$HEADER[$name] = $value;
        }
    }

    /** Define o contentType da resposta */
    static function type(?string $type, bool $replace = true)
    {
        if ($type) {
            $type = trim($type, '.');
            $type = strtolower($type);
            $type = Mime::getMimeEx($type) ?? $type;
        }

        if ($replace || is_null(self::$TYPE))
            self::$TYPE = $type;
    }

    /** Define o conteúdo da resposta */
    static function content(mixed $content, bool $replace = true)
    {
        if ($replace || is_null(self::$CONTENT))
            self::$CONTENT = $content;
    }

    /** Define se o arquivo deve ser armazenado em cache */
    static function cache(null|bool|string $strToTime): void
    {
        if (is_bool($strToTime)) $strToTime = $strToTime ? null : '';
        self::$CACHE = $strToTime;
    }

    /** Define se o navegador deve fazer download da resposta */
    static function download(null|bool|string $download): void
    {
        if (is_string($download)) {
            self::$DOWNLOAD_NAME = $download;
            $download = true;
        }
        self::$DOWNLOAD = boolval($download);
    }

    /** Envia a resposta ao navegador do cliente */
    static function send(): void
    {
        $content = self::getMontedContent();
        $headers = self::getMontedHeders();

        http_response_code(self::$STATUS ?? STS_OK);

        foreach ($headers as $name => $value)
            header(remove_accents("$name: $value"));

        die($content);
    }

    /** Retorna o status atual da resposta */
    static function getStatus(): ?int
    {
        return is_httpStatus(self::$STATUS) ? self::$STATUS : null;
    }

    /** Retorna o conteúdo atual da resposta */
    static function getContent(): mixed
    {
        return self::$CONTENT;
    }

    /** Verifica se o tipo da resposta é um dos tipos informados */
    static function checkType(): bool
    {
        foreach (func_get_args() as $type)
            if (Mime::checkMimeMime($type, self::$TYPE))
                return true;
        return false;
    }

    /** Retorna conteúdo da resposta */
    protected static function getMontedContent(): string
    {
        return is_array(self::$CONTENT) ? json_encode(self::$CONTENT) : strval(self::$CONTENT);
    }

    /** Retorna cabeçalhos de resposta */
    protected static function getMontedHeders(): array
    {
        return [
            ...self::$HEADER,
            ...self::getMontedHeaderCache(),
            ...self::getMontedHeaderType(),
            ...self::getMontedHeaderDownload(),
            ...self::getMontedHeader(),
        ];
    }

    /** Retorna os cabeçalhos */
    protected static function getMontedHeader(): array
    {
        return [
            'MX' => 'true',
            'MX-Status' => self::$STATUS ?? STS_OK,
            'MX-Type' => Mime::getExMime(self::$TYPE),
        ];
    }

    /** Retorna cabeçalhos de cache */
    protected static function getMontedHeaderCache(): array
    {
        if (!self::$TYPE) self::type(is_json(self::$CONTENT) ? 'json' : 'html');

        $headerCache = [];

        $cacheType = Mime::getExMime(self::$TYPE);

        $cacheTime = self::$CACHE;

        if (is_bool($cacheTime)) $cacheTime = $cacheTime ? null : '';

        $cacheTime = $cacheTime ?? env(strtoupper("CACHE_$cacheType"));

        if (is_bool($cacheTime)) $cacheTime = $cacheTime ? null : '';

        $cacheTime = $cacheTime ?? env("CACHE");

        if (is_bool($cacheTime)) $cacheTime = $cacheTime ? null : '';

        $headerCache['Mx-Cache'] = "($cacheTime)";

        if ($cacheTime && strtotime($cacheTime)) {
            $cacheTime = strtotime($cacheTime);
            $maxAge = $cacheTime - time();
            $headerCache['Pragma'] = 'public';
            $headerCache['Cache-Control'] = "max-age=$maxAge";
            $headerCache['Expires'] = gmdate('D, d M Y H:i:s', $cacheTime) . ' GMT';
        } else {
            $headerCache['Pragma'] = 'no-cache';
            $headerCache['Cache-Control'] = 'no-cache, no-store, must-revalidate';
            $headerCache['Expires'] = '0';
        }

        return $headerCache ?? [];
    }

    /** Retorna cabeçalhos de tipo de conteúdo */
    protected static function getMontedHeaderType(): array
    {
        if (is_array(self::$CONTENT) || is_json(self::$CONTENT))
            self::type('json');

        $type = self::$TYPE ?? Mime::getMimeEx('json');

        return ['Content-Type' => "$type; charset=utf-8"];
    }

    /** Retorna cabeçalhos de download */
    protected static function getMontedHeaderDownload(): array
    {
        $headerDownload = [];
        if (self::$DOWNLOAD) {
            $ex = Mime::getExMime(self::$TYPE) ?? 'download';
            $fileName = self::$DOWNLOAD_NAME ?? 'download';
            $fileName = File::setEx($fileName, $ex);
            $headerDownload['Content-Disposition'] = "attachment; filename=$fileName";
        }
        return $headerDownload;
    }
}
