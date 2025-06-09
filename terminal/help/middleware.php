<?php

use PhpMx\Dir;
use PhpMx\Path;
use PhpMx\Terminal;

return new class extends Terminal {

    protected $used = [];

    function __invoke()
    {
        self::echo();
        foreach (Path::seekDirs('middleware') as $path) {
            $origin = $this->getOrigim($path);

            self::echo('[[#]]', $origin);
            self::echoLine();

            foreach ($this->getFilesIn($path, $origin) as $file) {
                self::echo(' - [#ref] ([#file])[#status]', $file);
            };

            self::echo();
        }
    }

    protected function getOrigim($path)
    {
        if ($path === 'middleware') return 'CURRENT-PROJECT';

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
