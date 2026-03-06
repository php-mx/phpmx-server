<?php

namespace Example;

use PhpMx\Input;

/**
 * Controllers ficam em source/Controller/ e são referenciados pelo namespace completo da classe.
 * Os parâmetros do __construct e dos métodos são injetados automaticamente pelo nome
 * a partir de Request::data() (merge de route, query, body e file).
 * @see Router::get()
 * @see Request::data()
 */
class ExampleController
{
    protected int $id;

    /**
     * O __construct recebe parâmetros injetados pelo Router pelo nome.
     * @param int $id Parâmetro de rota, query ou body chamado 'id'.
     */
    function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    /** Retorna os dados do recurso identificado pelo parâmetro de rota */
    function show()
    {
        return ['id' => $this->id];
    }

    /** Cria um novo recurso com validação de input */
    function store()
    {
        $input = new Input();

        $name = $input->field('name', 'Nome')->required(true)->get();
        $email = $input->field('email', 'E-mail')->validate(FILTER_VALIDATE_EMAIL)->get();

        $input->check();

        return STS_CREATED;
    }

    /** Atualiza parcialmente o recurso com os campos recebidos */
    function update()
    {
        $input = new Input();

        $name = $input->field('name', 'Nome')->required(false)->get();
        $email = $input->field('email', 'E-mail')->required(false)->validate(FILTER_VALIDATE_EMAIL)->get();

        $data = $input->dataRecived();

        return $data;
    }

    /** Remove o recurso e retorna status sem conteúdo */
    function destroy()
    {
        return STS_NOT_CONTENT;
    }
}
