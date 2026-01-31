<?php

use PhpMx\Env;
use PhpMx\File;
use PhpMx\Terminal;

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

        Terminal::echo('| Starting PHP server');
        Terminal::echo('| Visit [#greenB:#]', $url);
        Terminal::echo('| Use [#blue:#] to terminate the server', "CTRL + C");
        Terminal::echo();

        echo shell_exec("php -S 0.0.0.0$port index.php");
    }
};
