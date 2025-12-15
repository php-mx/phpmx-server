<?php

use PhpMx\Dir;
use PhpMx\Path;
use PhpMx\Terminal;

return new class {

    protected $used = [];

    function __invoke()
    {
        Terminal::echo();

        foreach (Path::seekForDirs('system/middleware') as $path) {
            $origin = $this->getOrigim($path);

            Terminal::echo('[[#]]', $origin);
            Terminal::echoLine();

            foreach ($this->getFilesIn($path, $origin) as $file) {
                Terminal::echo(' - [#ref] ([#file])[#status]', $file);
            };

            Terminal::echo();
        }
    }

    protected function getOrigim($path)
    {
        if ($path === 'system/middleware') return 'CURRENT-PROJECT';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
    }

    protected function getFilesIn($path, $origin)
    {
        $files = [];
        foreach (Dir::seekForFile($path, true) as $item) {

            $ref = path($item);
            $ref = substr($item, 0, -4);
            $ref = str_replace('/', '.', $ref);

            $file = path($path, $item);

            $this->used[$ref] = $this->used[$ref] ?? $origin;

            $files[$ref] = [
                'ref' => $ref,
                'file' => $file,
                'status' => $this->used[$ref] == $origin ? '' : ' [replaced in ' . $this->used[$ref] . ']'
            ];
        }
        ksort($files);
        return $files;
    }
};
