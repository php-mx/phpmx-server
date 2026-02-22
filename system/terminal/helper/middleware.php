<?php

use PhpMx\Dir;
use PhpMx\Reflection\ReflectionMiddlewareFile;
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

        foreach (Dir::seekForFile($path, true) as $item)
            $items[] = ReflectionMiddlewareFile::scheme(path($path, $item));

        return array_filter($items);
    }
};
