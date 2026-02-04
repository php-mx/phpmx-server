<?php

use PhpMx\Env;
use PhpMx\File;
use PhpMx\Terminal;

/** Inicia o servidor embutido do PHP para rodar o projeto localmente */
return new class {

    function __invoke($port = null)
    {
        if (!File::check('index.php'))
            throw new Exception('[index.php] not found');

        $url = parse_url(env('TERMINAL_URL') ?? 'http://localhost:8888');

        $scheme = $url['scheme'] ?? 'http';
        $host = $url['host'] ?? 'localhost';
        $port = $port ?? $url['port'] ?? '8888';
        $port = $port ? ":$port" : '';

        $url = prepare("[#]://[#][#]", [$scheme, $host, $port]);

        Env::default('TERMINAL_URL', $url);

        Terminal::echol('Starting PHP server');
        Terminal::echol('Visit [#c:p,#]', $url);
        Terminal::echol('Use [#c:s,#] to terminate the server', "CTRL+C");
        Terminal::echol();

        echo shell_exec("php -S 0.0.0.0$port index.php");
    }
};
