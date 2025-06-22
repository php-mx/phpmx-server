# Assets

Classe utilitária para manipulação e envio de arquivos estáticos no ecossistema PHPMX.

```php
use PhpMx\Assets;
```

## Métodos principais

### send

Envia um arquivo do projeto como resposta da requisição.

```php
Assets::send(string $caminho): never
```

- Carrega e envia o arquivo especificado.
- Finaliza a resposta imediatamente.

### download

Realiza o download de um arquivo do projeto como resposta da requisição.

```php
Assets::download(string $caminho): never
```

- Carrega o arquivo e força o download pelo navegador.
- Finaliza a resposta imediatamente.

### load

Carrega um arquivo do projeto na resposta da requisição, sem forçar download.

```php
Assets::load(string $caminho): void
```

- Carrega o arquivo e prepara a resposta, mas não finaliza nem força download.

## Exemplo de uso

```php
Assets::send('storage/assets/logo.png');
Assets::download('storage/assets/manual.pdf');
Assets::load('storage/assets/estilo.css');
```

---

> **Nota:**
>
> - Todos os métodos utilizam verificação de existência do arquivo e retornam erro 404 caso não seja encontrado.
> - O tipo de conteúdo e headers são definidos automaticamente conforme a extensão do arquivo.
> - Ideal para servir imagens, scripts, estilos, documentos e outros recursos estáticos ou para download.
