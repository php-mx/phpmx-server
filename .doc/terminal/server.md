# phpmx server

---

Inicia um servidor PHP embutido para desenvolvimento local, utilizando o arquivo `index.php` como ponto de entrada.

- Útil para testar e desenvolver a aplicação localmente, sem necessidade de configuração adicional de Apache ou Nginx.
- O endereço e a porta utilizados são definidos pela variável de ambiente `TERMINAL_URL`.

## Uso

```sh
php mx server
```

- Inicia o servidor na porta configurada.
- Exibe a URL de acesso e instruções para encerrar o servidor (`CTRL + C`).
- O servidor permanece ativo até ser interrompido manualmente.
