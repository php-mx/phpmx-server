<?php

/*
 * Input gerencia a validação e sanitização de dados recebidos na requisição.
 * Por padrão lê Request::data() (merge de route, query, body e file).
 * Cada campo lança Exception com STS_BAD_REQUEST automaticamente em caso de falha.
 */

use PhpMx\Input;

// Instancia com os dados da requisição atual
$input = new Input();

// Campo simples — obrigatório por padrão
$name = $input->field('name', 'Nome')->get();

// Campo opcional com valor padrão
$page = $input->field('page')->required(false)->get() ?? 1;

// Campo booleano — converte automaticamente strings 'true'/'false'/'1'/'0'
$active = $input->fieldBool('active', 'Ativo')->get();

// Campo com validação nativa do PHP
$email = $input->field('email', 'E-mail')->validate(FILTER_VALIDATE_EMAIL)->get();

// Campo com validação customizada via Closure
$age = $input->field('age', 'Idade')->validate(
    fn($v) => is_numeric($v) && $v >= 18 && $v <= 120,
    'A idade deve estar entre 18 e 120 anos'
)->get();

// Campo com sanitização
$bio = $input->field('bio')->sanitize(FILTER_SANITIZE_SPECIAL_CHARS)->get();

// Campo de lista — aceita array ou string separada por vírgulas
$tags = $input->fieldList('tags', 'Tags')->get();

// Campo de upload de arquivo
$file = $input->fieldUpload('avatar', 'Avatar')->get();

// Campo de imagem em base64
$photo = $input->fieldUploadImage('photo', 'Foto')->get();

// Campo de captcha
$captcha = $input->fieldCaptcha('captcha', 'Captcha')->get();

// Campo de esquema JSON — aceita array ou string JSON, retorna array
$settings = $input->fieldScheme('settings', 'Configurações')->required(false)->get();

// Valida todos os campos de uma vez — lança Exception no primeiro erro
$input->check();

// Retorna todos os campos validados como array
$data = $input->data();

// Retorna apenas os campos que foram efetivamente enviados na requisição
$partial = $input->dataRecived();

// Lança erro em nome do input (útil para regras de negócio)
if ($data['email'] === 'blocked@example.com')
    $input->send('Este e-mail está bloqueado', STS_FORBIDDEN);
