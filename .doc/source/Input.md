# Input

Classe utilitária para manipulação, validação e sanitização de dados de entrada (input) no ecossistema PHPMX.

```php
use PhpMx\Input;

$input = new Input();
```

## Visão geral

A classe `Input` centraliza o tratamento de dados recebidos em requisições, permitindo definir campos, aplicar filtros, validações e obter valores já sanitizados.

Por padrão, um novo input utiliza os dados de **Request::data()**, mas você pode fornecer outro array de dados para validação:

```php
$input = new Input([...]);
```

---

## Métodos principais de Input

### field

Cria ou recupera um campo de input genérico.

```php
$input->field($name, $alias = null, $default = null): InputField
```

### fieldBool

Campo preparado para valores booleanos.

```php
$input->fieldBool($name, $alias = null, $default = null): InputFieldBool
```

### fieldList

Campo preparado para listas (arrays).

```php
$input->fieldList($name, $alias = null, $default = null): InputFieldList
```

### fieldUpload

Campo preparado para upload de arquivos.

```php
$input->fieldUpload($name, $alias = null, $default = null): InputFieldUpload
```

### fieldUploadImage

Campo preparado para upload de imagens em base64.

```php
$input->fieldUploadImage($name, $alias = null, $default = null): InputFieldUploadImage
```

### fieldCaptcha

Campo preparado para validação de captcha.

```php
$input->fieldCaptcha($name, $alias = null, $default = null): InputFieldCaptcha
```

### fieldScheme

Campo preparado para receber um array scheme.

```php
$input->fieldScheme($name, $alias = null, $default = null): InputFieldScheme
```

### get

Obtém o valor sanitizado de um campo.

```php
$input->get($fieldName): mixed
```

### check

Verifica se todos os campos do input passam na validação.

```php
$input->check(): bool
```

### data

Retorna os valores dos campos do input em array.

```php
$input->data(?array $nameFields = null): array
```

### dataRecived

Retorna apenas os campos realmente recebidos na requisição.

```php
$input->dataRecived(?array $nameFields = null): array
```

### send

Lança uma Exception em nome do input.

```php
$input->send($message, $status = false): never
```

---

## Métodos principais de InputField

### required

Define se o campo é obrigatório e a mensagem de erro.

```php
$field->required(bool $required, ?string $errorMessage = null, ?int $errorStatus = STS_BAD_REQUEST): static
```

### get

Aplica validações e sanitizações e retorna o valor do campo.

```php
$field->get(): mixed
```

### validate

Adiciona regras de validação customizadas ou padrões.

```php
$field->validate(int|Closure|InputField $rule, ?string $errorMessage = null, ?int $errorStatus = STS_BAD_REQUEST): static
```

### sanitize

Adiciona regras de sanitização customizadas ou padrões.

```php
$field->sanitize(int|Closure $rule): static
```

### recived

Verifica se o campo foi recebido na requisição.

```php
$field->recived(): bool
```

### preventTag

Define se o valor do campo deve ser tratado para evitar tags HTML.

```php
$field->preventTag(bool $preventTag, ?string $errorMessage = null, ?int $errorStatus = STS_BAD_REQUEST): static
```

### scapePrepare

Define quais tags devem ser escapadas pelo prepare.

```php
$field->scapePrepare(bool|array $scapePrepare = true): static
```

---

# Tipos de InputField

- **InputField**: Campo base para qualquer tipo de dado. Permite definir regras de required, validação, sanitização e tratamento de tags.
- **InputFieldBool**: Campo para valores booleanos. Converte e valida automaticamente o valor recebido.
- **InputFieldList**: Campo para listas. Aceita arrays ou strings separadas por vírgula.
- **InputFieldUpload**: Campo para upload de arquivos. Valida se o arquivo foi enviado corretamente e se não houve erro de tamanho ou upload.
- **InputFieldUploadImage**: Campo para upload de imagens em base64. Valida o tipo da imagem (png, jpg, jpeg, webp) e se o valor é uma imagem válida.
- **InputFieldCaptcha**: Campo para validação de captcha. Exige código válido e faz a verificação usando as classes de cifra e código do PHPMX.
- **InputFieldScheme**: Campo para arrays complexos (scheme). Aceita arrays ou JSON, deserializando automaticamente.

---

## Exemplo de uso

```php
$input = new Input();
$nome = $input->field('nome');
$ativo = $input->fieldBool('ativo');
$itens = $input->fieldList('itens');
$arquivo = $input->fieldUpload('arquivo');
$imagem = $input->fieldUploadImage('imagem');
$captcha = $input->fieldCaptcha('captcha');
if($input->check()){
    $data = $input->dataRecived();
}
```

---

> **Nota:**
>
> - Todos os campos permitem definir validações e sanitizações customizadas.
> - Campos obrigatórios lançam exceção automaticamente se não forem recebidos.
> - O uso dos filtros garante maior segurança e padronização no tratamento dos dados de entrada.
