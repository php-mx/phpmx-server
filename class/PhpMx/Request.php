<?php

namespace PhpMx;

/** Classe para acesso aos dados da requisição HTTP atual. */
class Request
{
    protected ?array $SERVER = null;
    protected ?string $TYPE = null;
    protected ?array $HEADER = null;
    protected ?bool $SSL = null;
    protected ?string $HOST = null;
    protected ?array $PATH = null;
    protected ?array $QUERY = null;
    protected ?array $BODY = null;
    protected array $ROUTE = [];
    protected ?array $FILE = null;

    /** Retorna um ou todos os parametros server da requisição atual */
    function server(): mixed
    {
        $this->SERVER = $this->SERVER ?? $this->current_server();

        if (func_num_args()) return $this->SERVER[func_get_arg(0)] ?? null;

        return $this->SERVER;
    }

    /** Retorna/Compara o tipo da requisição atual (GET, POST, PUT, DELETE, OPTIONS,) */
    function type(): string|bool
    {
        $this->TYPE = $this->TYPE ?? $this->current_type();

        if (func_num_args()) return $this->TYPE == strtoupper(func_get_arg(0));

        return $this->TYPE;
    }

    /** Retorna um ou todos os parametros header da requisição atual */
    function header(): mixed
    {
        $this->HEADER = $this->HEADER ?? $this->current_header();

        if (func_num_args()) return $this->HEADER[func_get_arg(0)] ?? null;

        return $this->HEADER;
    }

    /** Retorna/Compara o status de utilização SSL da requisição atual */
    function ssl(): bool
    {
        $this->SSL = $this->SSL ?? $this->current_ssl();

        if (func_num_args()) return $this->SSL == func_get_arg(0);

        return $this->SSL;
    }

    /** Retorna o host da requisição atual */
    function host(): string
    {
        $this->HOST = $this->HOST ?? $this->current_host();
        return $this->HOST;
    }

    /** Retorna ou o todos os caminhos da URI da requisição atual */
    function path(): array|string
    {
        $this->PATH = $this->PATH ?? $this->current_path();

        if (func_num_args()) return $this->PATH[func_get_arg(0)] ?? null;

        return $this->PATH;
    }

    /** Retorna ou o todos os parametros passados via query na requisição atual */
    function query(): mixed
    {
        $this->QUERY = $this->QUERY ?? $this->current_query();

        if (func_num_args() == 1) return $this->QUERY[func_get_arg(0)] ?? null;

        return $this->QUERY;
    }

    /** Retorna um ou todos os dados enviados no corpo da requisição atual */
    function body(): mixed
    {
        $this->BODY = $this->BODY ?? $this->current_body();

        if (func_num_args()) return $this->BODY[func_get_arg(0)] ?? null;

        return $this->BODY;
    }

    /** Retorna um ou todos os dados enviados via rota para a requisição atual */
    function route(): mixed
    {
        if (func_num_args()) return $this->ROUTE[func_get_arg(0)] ?? null;

        return $this->ROUTE;
    }

    /** Retorna um ou todos os capturados pela requisição atual via route, query, body ou file */
    function data(): mixed
    {
        $data = [...$this->route(), ...$this->query(), ...$this->body(), ...$this->file()];

        if (func_num_args()) return $data[func_get_arg(0)] ?? null;

        return $data;
    }

    /** Retorna um o todos os arquivos enviados na requisição atual */
    function file(): array
    {
        $this->FILE = $this->FILE ?? $this->current_file();

        $return = $this->FILE;

        if (func_num_args()) $return = $return[func_get_arg(0)] ?? [];

        return $return;
    }

    #==| SET |==#

    /** Define o valor de um parametro header da requisição atual */
    function set_header(string|int $name, mixed $value): void
    {
        $this->HEADER = $this->HEADER ?? $this->current_header();
        $this->HEADER[$name] = $value;
    }

    /** Define o valor de um parametro query da requisição atual */
    function set_query(string|int $name, mixed $value): void
    {
        $this->QUERY = $this->QUERY ?? $this->current_query();
        $this->QUERY[$name] = $value;
    }

    /** Define o valor de um parametro do corpo da requisição atual */
    function set_body(string|int $name, mixed $value): void
    {
        $this->BODY = $this->BODY ?? $this->current_query();
        $this->BODY[$name] = $value;
    }

    /** Define o valor de um parametro de rota da requisição atual */
    function set_route(string|int $name, mixed $value): void
    {
        $this->ROUTE[$name] = $value;
    }

    #==| LOAD |==#

    protected function current_server(): array
    {
        return $_SERVER;
    }

    protected function current_header(): array
    {
        return IS_TERMINAL ? [] : getallheaders();
    }

    protected function current_type(): string
    {
        if (IS_TERMINAL) return 'TERMINAL';

        return $this->server('REQUEST_METHOD') ?? 'UNDEFINED';
    }

    protected function current_ssl(): bool
    {
        if (IS_TERMINAL)
            return env('FORCE_SSL');

        return env('FORCE_SSL') || strtolower($this->server('HTTPS') ?? '') == 'on';
    }

    protected function current_host(): string
    {
        if ($this->server('HTTP_HOST')) return $this->server('HTTP_HOST');

        $parse = parse_url(env('TERMINAL_URL') ?? 'http://localhost:8888');

        $host = $parse['host'];

        if (isset($parse['port']))
            $host = "$host:" . $parse['port'];

        return $host;
    }

    protected function current_path(): array
    {
        $path = urldecode($this->server('REQUEST_URI') ?? '');
        $path = explode('?', $path);
        $path = array_shift($path);
        $path = trim($path, '/');
        $path = explode('/', $path);
        $path = array_filter($path, fn($path) => !is_blank($path));

        return $path ?? [];
    }

    protected function current_query(): array
    {
        $query = $this->server('REQUEST_URI') ?? '';

        $query = parse_url($query)['query'] ?? '';

        parse_str($query, $query);

        $query = array_map(fn($v) => urldecode($v), (array) $query);

        return array_map(fn($var) => str_get_var($var), $query);
    }

    protected function current_body(): array
    {
        $data = [];

        $inputData = file_get_contents('php://input');

        if (is_json($inputData)) {
            $data = json_decode($inputData, true);
        } elseif ($this->type('POST')) {
            $data = $_POST;
        } else if ($this->type('GET') || $this->type('PUT') || $this->type('DELETE')) {
            parse_str($inputData, $data);
        }

        array_walk_recursive($data, fn(&$v) => $v = str_get_var($v));

        return $data;
    }

    protected function current_file(): array
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
