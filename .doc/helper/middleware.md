# Middleware

Este documento lista os middlewares globais disponíveis por padrão no PHPMX Server, definidos na pasta `/middleware`.

---

## cors

Middleware responsável por habilitar CORS (Cross-Origin Resource Sharing) na aplicação. Permite requisições de qualquer origem, define métodos e headers permitidos e trata requisições OPTIONS automaticamente.

```
'cors'
```

## encaps

Middleware responsável por encapsular todas as respostas da API em um formato padrão, incluindo informações de status, mensagem, erro e dados. Também captura exceções e erros, retornando respostas padronizadas.

```
'encaps'
```

Saída padrão de uma resposta encapsulada:

```json
{
  "info": {
    "mx": true,
    "status": 200,
    "error": false,
    "message": "ok"
  },
  "data": null
}
```

Em caso de erro, a saída pode ser:

```json
{
  "info": {
    "mx": true,
    "status": 404,
    "error": true,
    "message": "not found"
  },
  "data": null
}
```

> **Nota:** Se o ambiente estiver em desenvolvimento (`DEV`), a resposta também incluirá um campo `log` com informações detalhadas da execução.
