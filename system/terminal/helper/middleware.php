<?php

use PhpMx\Dir;
use PhpMx\Path;
use PhpMx\Terminal;
use PhpMx\Import;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista as middlewares registradas no projeto */
return new class {

    use TerminalHelperTrait;

    function __invoke($fitler = null)
    {
        $this->handle('system/middleware', $fitler);
    }

    protected function scan($path)
    {
        $files = [];
        foreach (Dir::seekForFile($path, true) as $item) {

            $ref = substr($item, 0, -4);
            $ref = str_replace(['/', '\\'], '.', $ref);

            $file = path($path, $item);
            $content = Import::content($file);

            preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);
            $description = $match ? $this->getDocBefore($content, $match[0][1]) : '';

            $files[] = [
                'ref' => $ref,
                'description' => $description
            ];
        }
        return $files;
    }

    protected function getDocBefore(string $code, int $pos): string
    {
        $before = substr($code, 0, $pos);
        if (preg_match_all('/\/\*\*\s*(.*?)\s*\*\//s', $before, $docs)) {
            $lastDoc = end($docs[0]);
            $lastDesc = end($docs[1]);
            $lastPos = strrpos($before, $lastDoc) + strlen($lastDoc);

            $between = substr($before, $lastPos);

            if (preg_match('/^[\s\w\$\=]*$/', $between))
                return trim($lastDesc);
        }
        return '';
    }
};
