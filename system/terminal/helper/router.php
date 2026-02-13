<?php

use PhpMx\DocScheme;
use PhpMx\Dir;
use PhpMx\Path;
use PhpMx\ReflectionFile;
use PhpMx\Terminal;

/** Lista as rotas registradas no projeto */
return new class {

    function __invoke($match = null, $method = null)
    {
        $defaultScheme = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];
        $key = $defaultScheme;
        $registredRoutes = [];
        $method = is_null($method) ? null : strtoupper($method);
        $useFilter = !is_blank($match) && $match != '*';

        foreach (Path::seekForDirs('system/router') as $path) {
            foreach (array_reverse(Dir::seekForFile($path, true)) as $file) {
                $origim = Path::origin($path);
                $registredRoutes[$origim] = $registredRoutes[$origim] ?? $defaultScheme;
                foreach (ReflectionFile::routerFile(path($path, $file)) as $scheme) {
                    $routeTemplate = $scheme['path'];
                    $routeMethod = $scheme['method'];

                    $scheme['order'] = $routeTemplate;
                    $scheme['template'] = '/' . trim($routeTemplate, '/');
                    $scheme['replaced'] = $key[$routeMethod][$routeTemplate] ?? false;

                    $key[$routeMethod][$routeTemplate] = true;

                    if ($useFilter)
                        if ($match == '/' && $routeTemplate != '/')
                            continue;
                        else if (!str_starts_with(trim($routeTemplate, '/'), trim($match, '/')) && !$this->checkRouteMatch([$match], $routeTemplate))
                            continue;

                    $registredRoutes[$origim][$routeMethod][] = $scheme;
                }
            }
        }

        $originsLn = -1;

        foreach (array_reverse($registredRoutes) as $origin => $methods) {
            $count = 0;
            foreach ($methods as $routes) $count += count($routes);
            if (!$count) continue;

            if (++$originsLn) Terminal::echol();
            Terminal::echol('[#c:sb,#]', $origin);

            foreach (array_reverse($methods) as $curentMethod => $routes) {
                if (!is_null($method) && $curentMethod != $method) continue;
                if (empty($routes)) continue;

                $routes = $this->organize($routes);

                foreach (array_reverse($routes) as $item) {
                    Terminal::echol();
                    if (!$item['replaced']) {
                        Terminal::echol(' - [#c:d,#method][#c:d,:][#c:p,#template] [#c:sd,#file]', $item);
                        $response = $item['response'];

                        if ($response['type'] == 'status')
                            Terminal::echol("      [#c:s,status] [#c:s,#code]", $response);

                        if ($response['type'] == 'class') {
                            if ($response['callable']) {
                                Terminal::echol("      [#c:s,#class][#c:s,::][#c:s,#method][#c:s,()] [#c:sd,#file][#c:sd,:][#c:sd,#line]", $response);
                                foreach ($response['description'] as $description)
                                    Terminal::echol("         $description");
                            } elseif ($response['file']) {
                                Terminal::echol("      [#c:dd,#class][#c:dd,::][#c:e,#method][#c:e,()] [#c:sd,#file]", $response);
                            } else {
                                Terminal::echol("      [#c:e,#class][#c:dd,::][#c:dd,#method][#c:dd,()]", $response);
                            }
                        }
                    } else {
                        Terminal::echol(' - [#c:dd,#method][#c:sd,:][#c:pd,#template] [#c:sd,#file] [#c:wd,replaced]', $item);
                    }
                }
            }
        }

        if ($originsLn == -1)
            Terminal::echol('[#c:dd,- empty -]');
    }

    protected static function checkRouteMatch(array $path, string $template): bool
    {
        $path = array_shift($path);

        if (is_null($path)) return true;

        $path = explode('/', $path);
        $path = array_filter($path);

        $template = trim($template, '/');
        $template = explode('/', $template);

        while (count($template)) {
            $expected = array_shift($template);
            $received = array_shift($path) ?? '';
            if ($expected === '...') return true;
            if (is_blank($received) && !is_blank($expected)) return false;
            if ($expected !== '#' && $received !== $expected) return false;
        }

        return count($path) === 0;
    }

    protected static function organize(array $array): array
    {
        uasort($array, function ($itemA, $itemB) {
            $a = $itemA['order'];
            $b = $itemB['order'];

            $countA = substr_count($a, '/');
            $countB = substr_count($b, '/');

            if ($countA !== $countB) return $countB <=> $countA;

            $posA = strpos($a, '/');
            $posB = strpos($b, '/');

            if ($posA !== $posB) return $posB <=> $posA;

            $aParts = explode('/', $a);
            $bParts = explode('/', $b);

            $max = max(count($aParts), count($bParts));

            $peso = fn($part) => match ($part) {
                '...' => 2,
                '#' => 1,
                default => 0,
            };

            for ($i = 0; $i < $max; $i++) {
                $partA = $aParts[$i] ?? '';
                $partB = $bParts[$i] ?? '';

                $pesoA = $peso($partA);
                $pesoB = $peso($partB);

                if ($pesoA !== $pesoB) return $pesoA <=> $pesoB;
            }

            return 0;
        });

        return $array;
    }
};
