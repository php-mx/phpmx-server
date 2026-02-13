<?php

use PhpMx\Dir;
use PhpMx\ReflectionFile;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista as middlewares registradas no projeto */
return new class {

    use TerminalHelperTrait;

    function __invoke($fitler = null)
    {
        $this->handle(
            'system/middleware',
            $fitler,
            function ($item) {
                Terminal::echol(' - [#c:p,#name] [#c:sd,#file][#c:sd,:][#c:sd,#line]', $item);
                foreach ($item['description'] as $description)
                    Terminal::echol("      $description");
            }
        );
    }

    protected function scan($path)
    {
        $items = [];

        foreach (Dir::seekForFile($path, true) as $item) {
            $scheme = ReflectionFile::middlewareFile(path($path, $item));
            if (!empty($scheme))
                $items[] = $scheme;
        }

        return $items;
    }
};
