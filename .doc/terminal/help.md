# phpmx help

---

Grupo de comandos para criação de artefatos no projeto PHPMX. Os subcomandos disponíveis são:

- Os comandos criam arquivos prontos para uso, evitando sobrescrever arquivos já existentes.
- Útil para automação e padronização de novos recursos no projeto.

### help.middleware

Lista todos os middlewares disponíveis no projeto e suas origens (projeto ou pacotes).

**Chamada:**

```sh
php mx help.middleware
```

- Exibe todos os middlewares customizados e herdados de dependências.
- Indica a origem de cada middleware.

---

### help.router

Lista todas as rotas disponíveis no projeto, agrupando por origem (projeto ou pacotes).

```sh
php mx help.router <método> <uri>
```

- `<método>` (opcional): Filtra por método HTTP (get, post, put, delete)
- `<uri>` (opcional): Filtra por URI específica

- Exibe todas as rotas customizadas e herdadas de dependências.
- Mostra o arquivo de origem de cada rota.
- Permite filtrar por método HTTP e/ou URI.

---

### help.router.order

Exibe a ordem de resolução dos middlewares e rotas, mostrando a prioridade de execução.

```sh
php mx help.router.order <método> <uri>
```

- `<método>` (opcional): Filtra por método HTTP
- `<uri>` (opcional): Filtra por URI específica

- Mostra a ordem de execução dos middlewares.
- Exibe a ordem de resolução das rotas, considerando grupos e prioridades.
- Permite filtrar por método HTTP e/ou URI.
