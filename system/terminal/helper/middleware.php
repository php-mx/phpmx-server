<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/**
 * Lista as middlewares registradas no projeto.
 * @param string $filter Nome ou parte do nome de uma middleware para filtrar a busca.
 */
return new class {

    use TerminalHelperTrait;

    function __invoke($fitler = null)
    {
        $this->handle(
            'system/middleware',
            $fitler,
            function ($item) {
                Terminal::echol('   [#c:p,#name] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
            }
        );
    }

    protected function scan($path)
    {
        $items = [];

        foreach (Dir::seekForFile($path, true) as $item) {
            $scheme = $this->reflectionFile(path($path, $item));
            if (!empty($scheme))
                $items[] = $scheme;
        }

        return $items;
    }

    protected function reflectionFile(string $file): array
    {
        $content = Import::content($file);

        preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);

        if (!$match) return [];

        $pos = $match[0][1];

        $docBlock = self::docBlockBefore($content, $pos);
        $docScheme = self::parseDocBlock($docBlock, ['description', 'see', 'internal', 'context']);

        $docScheme['context'] = $docScheme['context'] ?? 'http';

        $middleware = explode('system/middleware/', $file);
        $middleware = array_pop($middleware);
        $middleware = substr($middleware, 0, -4);
        $middleware = str_replace(['/', '\\'], '.', $middleware);

        return [
            'key' => "middleware:$middleware",
            'typeKey' => 'middleware',

            'name' => $middleware,

            'origin' => Path::origin($file),
            'file' => $file,
            'line' => substr_count(substr($content, 0, $pos), "\n") + 1,

            ...$docScheme,
        ];
    }
};
