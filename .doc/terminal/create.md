# phpmx create

---

Grupo de comandos para criação de artefatos no projeto PHPMX. Os subcomandos disponíveis são:

- Os comandos criam arquivos prontos para uso, evitando sobrescrever arquivos já existentes.
- Útil para automação e padronização de novos recursos no projeto.

### create.controller

Cria um novo controller no diretório `source/Controller` do projeto.

```sh
php mx create.controller <nome>
```

- `<nome>`: Nome do controller a ser criado (use ponto ou barra para subpastas).

- Gera o arquivo do controller com namespace e classe prontos.
- Utiliza o template padrão de controller do projeto.
- Não sobrescreve arquivos já existentes.

---

### create.middleware

Cria um novo middleware no diretório `middleware` do projeto.

```sh
php mx create.middleware <nome>
```

- `<nome>`: Nome do middleware a ser criado (use ponto ou barra para subpastas).

- Gera o arquivo do middleware com template padrão.
- Não sobrescreve arquivos já existentes.
