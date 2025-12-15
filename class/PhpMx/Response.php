<?php

namespace PhpMx;

/** Classe para construção e envio de respostas HTTP. */
class Response
{
    protected array $HEADER = [];
    protected ?int $STATUS = null;
    protected ?string $TYPE = null;
    protected mixed $CONTENT = null;
    protected ?string $CACHE = null;
    protected bool $DOWNLOAD = false;
    protected ?string $DOWNLOAD_NAME = null;

    /** Define o status HTTP da resposta */
    function status(?int $status, bool $replace = true)
    {
        $this->STATUS = $replace ? $status : ($this->STATUS ?? $status);
    }

    /** Define um cabeçalho para a resposta */
    function header(string|array $name, ?string $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $n => $v)
                $this->header($n, $v);
        } else {
            $this->HEADER[$name] = $value;
        }
    }

    /** Define o contentType da resposta */
    function type(?string $type, bool $replace = true)
    {
        if ($type) {
            $type = trim($type, '.');
            $type = strtolower($type);
            $type = Mime::getMimeEx($type) ?? $type;
        }

        if ($replace || is_null($this->TYPE))
            $this->TYPE = $type;
    }

    /** Define o conteúdo da resposta */
    function content(mixed $content, bool $replace = true)
    {
        if ($replace || is_null($this->CONTENT))
            $this->CONTENT = $content;
    }

    /** Define se o arquivo deve ser armazenado em cache */
    function cache(null|bool|string $strToTime): void
    {
        if (is_bool($strToTime)) $strToTime = $strToTime ? null : '';
        $this->CACHE = $strToTime;
    }

    /** Define se o navegador deve fazer download da resposta */
    function download(null|bool|string $download): void
    {
        if (is_string($download)) {
            $this->DOWNLOAD_NAME = $download;
            $download = true;
        }
        $this->DOWNLOAD = boolval($download);
    }

    /** Envia a resposta ao navegador do cliente */
    function send(): void
    {
        $content = $this->getMontedContent();
        $headers = $this->getMontedHeders();

        http_response_code($this->STATUS ?? STS_OK);

        foreach ($headers as $name => $value)
            header(remove_accents("$name: $value"));

        die($content);
    }

    /** Retorna o status atual da resposta */
    function getStatus(): ?int
    {
        return is_httpStatus($this->STATUS) ? $this->STATUS : null;
    }

    /** Retorna o conteúdo atual da resposta */
    function getContent(): mixed
    {
        return $this->CONTENT;
    }

    /** Verifica se o tipo da resposta é um dos tipos informados */
    function checkType(): bool
    {
        foreach (func_get_args() as $type)
            if (Mime::checkMimeMime($type, $this->TYPE))
                return true;
        return false;
    }

    /** Retorna conteúdo da resposta */
    protected function getMontedContent(): string
    {
        return is_array($this->CONTENT) ? json_encode($this->CONTENT) : strval($this->CONTENT);
    }

    /** Retorna cabeçalhos de resposta */
    protected function getMontedHeders(): array
    {
        return [
            ...$this->HEADER,
            ...$this->getMontedHeaderCache(),
            ...$this->getMontedHeaderType(),
            ...$this->getMontedHeaderDownload(),
            ...$this->getMontedHeader(),
        ];
    }

    /** Retorna os cabeçalhos */
    protected function getMontedHeader(): array
    {
        return [
            'MX' => 'true',
            'MX-Status' => $this->STATUS ?? STS_OK,
            'MX-Type' => Mime::getExMime($this->TYPE),
        ];
    }

    /** Retorna cabeçalhos de cache */
    protected function getMontedHeaderCache(): array
    {
        if (!$this->TYPE) $this->type(is_json($this->CONTENT) ? 'json' : 'html');

        $headerCache = [];

        $cacheType = Mime::getExMime($this->TYPE);

        $cacheTime = $this->CACHE;

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
    protected function getMontedHeaderType(): array
    {
        if (is_array($this->CONTENT) || is_json($this->CONTENT))
            $this->type('json');

        $type = $this->TYPE ?? Mime::getMimeEx('json');

        return ['Content-Type' => "$type; charset=utf-8"];
    }

    /** Retorna cabeçalhos de download */
    protected function getMontedHeaderDownload(): array
    {
        $headerDownload = [];
        if ($this->DOWNLOAD) {
            $ex = Mime::getExMime($this->TYPE) ?? 'download';
            $fileName = $this->DOWNLOAD_NAME ?? 'download';
            $fileName = File::setEx($fileName, $ex);
            $headerDownload['Content-Disposition'] = "attachment; filename=$fileName";
        }
        return $headerDownload;
    }
}
