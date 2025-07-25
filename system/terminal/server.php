<?php

use PhpMx\File;
use PhpMx\Terminal;

return new class extends Terminal {

    function __invoke()
    {
        if (!File::check('index.php'))
            throw new Exception('[index.php] not found');

        $port = parse_url(env('TERMINAL_URL'))['port'];
        $port = $port ? ":$port" : '';

        self::echoLine();
        self::echo('| Starting PHP server');
        self::echo('| Visit [[#]]', env('TERMINAL_URL'));
        self::echo('| Use [[#]] to terminate the server', "CTRL + C");
        self::echoLine();
        self::echo('');

        echo shell_exec("php -S 0.0.0.0$port index.php");
    }
};
