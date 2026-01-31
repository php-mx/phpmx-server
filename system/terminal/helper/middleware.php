<?php

use PhpMx\Dir;
use PhpMx\Path;
use PhpMx\Terminal;

return new class {

    protected $used = [];

    function __invoke()
    {
        foreach (Path::seekForDirs('system/middleware') as $n => $path) {
            $origin = $this->getOrigim($path);

            if ($n > 0) Terminal::echo();

            Terminal::echo('[#greenB:#]', $origin);

            foreach ($this->getFilesIn($path, $origin) as $file)
                Terminal::echo(' - [#cyan:#ref] [#blueD:#file][#yellowD:#status]', $file);
        }
    }

    protected function getOrigim($path)
    {
        if ($path === 'system/middleware') return 'current-project';

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

            if ($this->used[$ref] == $origin) {
                $files[$ref] = [
                    'ref' => $ref,
                    'file' => $file,
                    'status' => ''
                ];
            } else {
                $files[$ref] = [
                    'ref' => $ref,
                    'file' => '',
                    'status' => 'replaced in ' . $this->used[$ref]
                ];
            }
        }
        ksort($files);
        return $files;
    }
};
