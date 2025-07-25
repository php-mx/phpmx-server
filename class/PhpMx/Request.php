<?php

namespace PhpMx;

abstract class Request
{
    protected static ?string $TYPE = null;
    protected static ?array $HEADER = null;
    protected static ?bool $SSL = null;
    protected static ?string $HOST = null;
    protected static ?array $PATH = null;
    protected static ?array $QUERY = null;
    protected static ?array $BODY = null;
    protected static array $ROUTE = [];
    protected static ?array $FILE = null;

    /** Retorna/Compara o tipo da requisição atual (GET, POST, PUT, DELETE, OPTIONS,) */
    static function type(): string|bool
    {
        self::$TYPE = self::$TYPE ?? self::current_type();

        if (func_num_args())
            return self::$TYPE == strtoupper(func_get_arg(0));

        return self::$TYPE;
    }

    /** Retorna um ou todos os parametros header da requisição atual */
    static function header(): mixed
    {
        self::$HEADER = self::$HEADER ?? self::current_header();

        if (func_num_args())
            return self::$HEADER[func_get_arg(0)] ?? null;

        return self::$HEADER;
    }

    /** Retorna/Compara o status de utilização SSL da requisição atual */
    static function ssl(): bool
    {
        self::$SSL = self::$SSL ?? self::current_ssl();

        if (func_num_args())
            return self::$SSL == func_get_arg(0);

        return self::$SSL;
    }

    /** Retorna o host da requisição atual */
    static function host(): string
    {
        self::$HOST = self::$HOST ?? self::current_host();
        return self::$HOST;
    }

    /** Retorna ou o todos os caminhos da URI da requisição atual */
    static function path(): array|string
    {
        self::$PATH = self::$PATH ?? self::current_path();

        if (func_num_args())
            return self::$PATH[func_get_arg(0)] ?? null;

        return self::$PATH;
    }

    /** Retorna ou o todos os parametros passados via query na requisição atual */
    static function query(): mixed
    {
        self::$QUERY = self::$QUERY ?? self::current_query();

        if (func_num_args() == 1)
            return self::$QUERY[func_get_arg(0)] ?? null;

        return self::$QUERY;
    }

    /** Retorna um ou todos os dados enviados no corpo da requisição atual */
    static function body(): mixed
    {
        self::$BODY = self::$BODY ?? self::current_body();

        if (func_num_args())
            return self::$BODY[func_get_arg(0)] ?? null;

        return self::$BODY;
    }

    /** Retorna um ou todos os dados enviados via rota para a requisição atual */
    static function route(): mixed
    {
        if (func_num_args())
            return self::$ROUTE[func_get_arg(0)] ?? null;

        return self::$ROUTE;
    }

    /** Retorna um ou todos os capturados pela requisição atual via route, query, body ou file */
    static function data(): mixed
    {
        $data = [...self::route(), ...self::query(), ...self::body(), ...self::file()];

        if (func_num_args())
            return $data[func_get_arg(0)] ?? null;

        return $data;
    }

    /** Retorna um o todos os arquivos enviados na requisição atual */
    static function file(): array
    {
        self::$FILE = self::$FILE ?? self::current_file();

        $return = self::$FILE;

        if (func_num_args())
            $return = $return[func_get_arg(0)] ?? [];

        return $return;
    }

    #==| SET |==#

    /** Define o valor de um parametro header da requisição atual */
    static function set_header(string|int $name, mixed $value): void
    {
        self::$HEADER = self::$HEADER ?? self::current_header();
        self::$HEADER[$name] = $value;
    }

    /** Define o valor de um parametro query da requisição atual */
    static function set_query(string|int $name, mixed $value): void
    {
        self::$QUERY = self::$QUERY ?? self::current_query();
        self::$QUERY[$name] = $value;
    }

    /** Define o valor de um parametro do corpo da requisição atual */
    static function set_body(string|int $name, mixed $value): void
    {
        self::$BODY = self::$BODY ?? self::current_query();
        self::$BODY[$name] = $value;
    }

    /** Define o valor de um parametro de rota da requisição atual */
    static function set_route(string|int $name, mixed $value): void
    {
        self::$ROUTE[$name] = $value;
    }

    #==| LOAD |==#

    protected static function current_header(): array
    {
        return IS_TERMINAL ? [] : getallheaders();
    }

    protected static function current_type(): string
    {
        return match (true) {
            IS_TERMINAL => 'TERMINAL',
            IS_GET => 'GET',
            IS_POST => 'POST',
            IS_PUT => 'PUT',
            IS_DELETE => 'DELETE',
            IS_OPTIONS => 'OPTIONS',
            default => 'UNDEFINED',
        };
    }

    protected static function current_ssl(): bool
    {
        if (IS_TERMINAL)
            return env('FORCE_SSL');

        return env('FORCE_SSL') || strtolower($_SERVER['HTTPS'] ?? '') == 'on';
    }

    protected static function current_host(): string
    {
        if ($_SERVER['HTTP_HOST']) return $_SERVER['HTTP_HOST'];

        $parse = parse_url(env('TERMINAL_URL'));

        $host = $parse['host'];

        if (isset($parse['port']))
            $host = "$host:" . $parse['port'];

        return $host;
    }

    protected static function current_path(): array
    {
        $path = urldecode($_SERVER['REQUEST_URI'] ?? '');
        $path = explode('?', $path);
        $path = array_shift($path);
        $path = trim($path, '/');
        $path = explode('/', $path);
        $path = array_filter($path, fn($path) => !is_blank($path));

        return $path ?? [];
    }

    protected static function current_query(): array
    {
        $query = $_SERVER['REQUEST_URI'] ?? '';

        $query = parse_url($query)['query'] ?? '';

        parse_str($query, $query);

        $query = array_map(fn($v) => urldecode($v), $query);

        return array_map(fn($var) => str_get_var($var), $query);
    }

    protected static function current_body(): array
    {
        $data = [];

        $inputData = file_get_contents('php://input');

        if (is_json($inputData)) {
            $data = json_decode($inputData, true);
        } elseif (IS_POST) {
            $data = $_POST;
        } elseif (IS_GET || IS_PUT || IS_DELETE) {
            parse_str($inputData, $data);
        }

        array_walk_recursive($data, fn(&$v) => $v = str_get_var($v));

        return $data;
    }

    protected static function current_file(): array
    {
        if (IS_TERMINAL) return [];

        $files = [];

        foreach ($_FILES as $name => $file) {
            if (is_array($file['error'])) {
                for ($i = 0; $i < count($file['error']); $i++) {
                    $files[$name][] = [
                        'name' => $file['name'][$i],
                        'full_path' => $file['full_path'][$i],
                        'type' => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                        'size' => $file['size'][$i],
                    ];
                }
            } else {
                $files[$name][] = [
                    'name' => $file['name'],
                    'full_path' => $file['full_path'],
                    'type' => $file['type'],
                    'tmp_name' => $file['tmp_name'],
                    'error' => $file['error'],
                    'size' => $file['size'],
                ];
            }
        }

        foreach ($files as &$file)
            $file = array_shift($file);

        return $files;
    }
}
