<?php

use PhpMx\Dir;
use PhpMx\Path;
use PhpMx\Terminal;
use PhpMx\Import;

/** Lista as middlewares registradas no projeto */
return new class {

    protected $used = [];

    function __invoke()
    {
        foreach (Path::seekForDirs('system/middleware') as $n => $path) {
            $origin = $this->getOrigim($path);

            if ($n > 0) Terminal::echo();

            Terminal::echo('[#greenB:#]', $origin);

            foreach ($this->getFilesIn($path, $origin) as $file) {
                Terminal::echo();
                Terminal::echo(' [#cyan:#ref] [#description]', $file);
                Terminal::echo('  [#blue:#file] [#yellow:#replaced]', $file);
            }
        }
    }

    protected function getOrigim($path)
    {
        if ($path === 'system/middleware') return 'current-project';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return ($parts[1] ?? 'unknown') . '-' . ($parts[2] ?? 'unknown');
        }

        return 'unknown';
    }

    protected function getFilesIn($path, $origin)
    {
        $files = [];
        foreach (Dir::seekForFile($path, true) as $item) {

            $ref = substr($item, 0, -4);
            $ref = str_replace(['/', '\\'], '.', $ref);

            $file = path($path, $item);
            $content = Import::content($file); // Lê para processar o DocBlock

            // Regex flexível para capturar o offset da classe
            preg_match('/(?:return|.*?=)\s*new\s+class/i', $content, $match, PREG_OFFSET_CAPTURE);
            $description = $match ? $this->getDocBefore($content, $match[0][1]) : '';

            $this->used[$ref] = $this->used[$ref] ?? $file;

            $files[$ref] = [
                'ref' => $ref,
                'description' => $description,
                'file' => $file,
                'replaced' => $this->used[$ref] == $file ? '' : $this->used[$ref]
            ];
        }
        ksort($files);
        return $files;
    }

    /** Extrai o DocBlock permitindo caracteres de atribuição no meio */
    protected function getDocBefore(string $code, int $pos): string
    {
        $before = substr($code, 0, $pos);
        if (preg_match_all('/\/\*\*\s*(.*?)\s*\*\//s', $before, $docs)) {
            $lastDoc = end($docs[0]);
            $lastDesc = end($docs[1]);
            $lastPos = strrpos($before, $lastDoc) + strlen($lastDoc);

            $between = substr($before, $lastPos);

            // Aceita espaços, quebras e a sintaxe do interceptor ($var =)
            if (preg_match('/^[\s\w\$\=]*$/', $between)) {
                return trim($lastDesc);
            }
        }
        return '';
    }
};
