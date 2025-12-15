<?php

use PhpMx\File;
use PhpMx\Terminal;

return new class {

    function __invoke()
    {
        if (!File::check('index.php'))
            throw new Exception('[index.php] not found');

        $port = parse_url(env('TERMINAL_URL'))['port'];
        $port = $port ? ":$port" : '';

        Terminal::echoLine();
        Terminal::echo('| Starting PHP server');
        Terminal::echo('| Visit [[#]]', env('TERMINAL_URL'));
        Terminal::echo('| Use [[#]] to terminate the server', "CTRL + C");
        Terminal::echoLine();
        Terminal::echo('');

        echo shell_exec("php -S 0.0.0.0$port index.php");
    }
};
