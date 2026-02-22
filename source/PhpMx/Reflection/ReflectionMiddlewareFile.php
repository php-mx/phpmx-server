<?php

namespace PhpMx\Reflection;

use PhpMx\Import;
use PhpMx\Path;

abstract class ReflectionMiddlewareFile extends BaseReflectionFile
{
    static function scheme(string $file): array
    {
        $content = Import::content($file);

        preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);

        if (!$match) return [];

        $pos = $match[0][1];

        $docBlock = self::docBlockBefore($content, $pos);
        $docScheme = self::parseDocBlock($docBlock);

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
            'file' => path($file),
            'line' => substr_count(substr($content, 0, $pos), "\n") + 1,

            ...$docScheme,
        ];
    }
}
