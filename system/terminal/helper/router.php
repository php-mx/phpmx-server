<?php

use PhpMx\Dir;
use PhpMx\Import;
use PhpMx\Path;
use PhpMx\Router;
use PhpMx\Terminal;
use PhpMx\Trait\TerminalHelperTrait;

/** Lista as rotas registradas no projeto */
return new class extends Router {

    use TerminalHelperTrait;

    protected array $keys = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];

    function __invoke($match = null, $method = null)
    {
        $registredRoutes = [];
        $method = is_null($method) == '*' ? null : strtoupper($method);

        foreach (Path::seekForDirs('system/router') as $path)
            foreach (array_reverse(Dir::seekForFile($path, true)) as $file) {
                Import::only(path($path, $file), false);
                $origim = $this->origin($path, 'system/router');
                $registredRoutes[$origim] = $registredRoutes[$origim] ?? ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];

                foreach (self::$ROUTE as $currentMethod => $routes)
                    if (is_null($method) || $method == $currentMethod)
                        foreach ($routes as $route) {
                            list($template, $response, $parms, $middlewares) = $route;
                            list($response, $description) = $this->getResponseInfo($response);
                            $curentRoute = [
                                'order' => $template,
                                'template' => '/' . trim($template, '/'),
                                'response' => $response,
                                'middlewares' => empty($middlewares) ? '' : ' [' . implode(', ', $middlewares) . ']',
                                'description' => $description,
                                'origim' => $origim,
                                'file' => path($path, $file),
                                'replaced' => $this->key[$currentMethod][$template] ?? false,
                                'method' => $currentMethod,
                            ];
                            $this->key[$currentMethod][$template] = true;

                            if (!is_null($match)) {
                                $searchMatch = strtolower('/' . trim($match, '/'));
                                $searchTemplate = strtolower('/' . trim($template, '/'));
                                if (!str_starts_with($searchTemplate, $searchMatch) && !$this->checkRouteMatch([$match], $template))
                                    continue;
                            }
                            $registredRoutes[$origim][$currentMethod][] = $curentRoute;
                        }

                self::$ROUTE = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];
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

                Terminal::echol();

                foreach ($routes as $route) {
                    if (!$route['replaced']) {
                        $response = $route['response'];

                        Terminal::echo(" - [#c:s,$curentMethod][#c:s,:][#c:p,#template]", $route);
                        Terminal::echol("[#middlewares] $response ", $route);
                    } else {
                        Terminal::echol(" - [#c:sd,$curentMethod][#c:sd,:][#c:pd,#template] [#c:wd,replaced]", $route);
                    }
                }
            }
        }
    }

    protected function origin($path, $base)
    {
        if ($path === $base) return 'current-project';

        if (str_starts_with($path, 'vendor/')) {
            $parts = explode('/', $path);
            return $parts[1] . '-' . $parts[2];
        }

        return 'unknown';
    }

    protected static function organize(array $array): array
    {
        uasort($array, function ($a, $b) {
            $pathA = $a['order'] ?? '';
            $pathB = $b['order'] ?? '';

            $countA = substr_count($pathA, '/');
            $countB = substr_count($pathB, '/');

            if ($countA !== $countB) {
                return $countB <=> $countA;
            }

            $aParts = explode('/', $pathA);
            $bParts = explode('/', $pathB);
            $max = max(count($aParts), count($bParts));

            $peso = fn($part) => match ($part) {
                '...'   => 2,
                '#'     => 1,
                default => 0,
            };

            for ($i = 0; $i < $max; $i++) {
                $partA = $aParts[$i] ?? '';
                $partB = $bParts[$i] ?? '';

                $pesoA = $peso($partA);
                $pesoB = $peso($partB);

                if ($pesoA !== $pesoB) {
                    return $pesoA <=> $pesoB;
                }
            }

            return $pathA <=> $pathB;
        });

        return $array;
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

    protected function getResponseInfo($response): array
    {
        if (is_int($response))
            return ["[#c:w,$response]", ''];

        $parts = is_array($response) ? $response : [$response];
        $controller = array_shift($parts);
        $method = array_shift($parts) ?? '__invoke';

        if (class_exists($controller)) {
            if (method_exists($controller, $method)) {
            } else {

                $reflection = new \ReflectionClass($controller);
                $filePath = path($reflection->getFileName());

                return ["[#c:sd,$filePath] [#c:e,$method][#c:e,()]", ''];
            }
        } else {
            return ["[#c:e,$controller]", ''];
        }

        if (class_exists($controller) && method_exists($controller, $method)) {
            $reflection = new \ReflectionMethod($controller, $method);
            $filePath = path($reflection->getFileName());
            $startLine = $reflection->getStartLine();
            $doc = $reflection->getDocComment();
            $doc = $doc ? trim(str_replace(['/**', '*/', '*', "\r"], '', $doc)) : '';

            return ["[#c:sd,{$filePath}:{$startLine} {$method}()]", $doc];
        }

        return ["[#c:e,$controller ({$method})]", ''];
    }
};
