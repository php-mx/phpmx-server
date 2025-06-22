# Routes

Este documento lista as rotas globais padrão disponíveis no PHPMX Server, definidas em `/routes/mx.php`.

---

## Rotas padrão

- **GET /**: Status da aplicação (`base.status`)
- **GET /favicon.ico**: Favicon (`base.favicon`)
- **GET /robots.txt**: Arquivo de robôs (`base.robots`)
- **GET /sitemap.xml**: Sitemap (`base.sitemap`)
- **GET /captcha**: Geração de captcha (`base.captcha`)
- **GET /assets/...**: Acesso a arquivos de assets estáticos (`base.assets`)
- **GET /download/...**: Acesso a arquivos para download (`base.download`)

Essas rotas são expostas automaticamente pelo server e podem ser sobrescritas ou expandidas conforme a necessidade do projeto.
