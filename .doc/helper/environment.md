# Variaveis de ambiente

Este documento lista as variáveis de ambiente padrão do server. Todas podem ser sobrescritas no seu próprio arquivo `.env` ou via configuração manual.

- **FORCE_SSL** (padrão: `false`): Força o uso de HTTPS em todas as requisições.
- **TERMINAL_URL** (padrão: `http://localhost:8888`): URL base para acesso ao terminal web.
- **JWT** (padrão: `jwt-key`): Chave padrão para geração e validação de tokens JWT.
- **CACHE** (padrão: `null`): Configuração global de cache. Se não definido, utiliza os valores individuais abaixo.
- **CACHE_JS** (padrão: `+30 days`): Tempo de cache para arquivos JavaScript.
- **CACHE_CSS** (padrão: `+30 days`): Tempo de cache para arquivos CSS.
- **CACHE_ICO** (padrão: `+30 days`): Tempo de cache para arquivos ICO.
- **CACHE_PNG** (padrão: `+30 days`): Tempo de cache para arquivos PNG.
- **CACHE_JPG** (padrão: `+30 days`): Tempo de cache para arquivos JPG.
- **CACHE_BMP** (padrão: `+30 days`): Tempo de cache para arquivos BMP.
- **CACHE_GIF** (padrão: `+30 days`): Tempo de cache para arquivos GIF.
- **CACHE_WEBP** (padrão: `+30 days`): Tempo de cache para arquivos WEBP.
- **CACHE_MP3** (padrão: `+30 days`): Tempo de cache para arquivos MP3.
- **CACHE_MP4** (padrão: `+30 days`): Tempo de cache para arquivos MP4.
- **STM_200** (padrão: `ok`): Mensagem padrão para resposta HTTP 200.
- **STM_201** (padrão: `created`): Mensagem padrão para resposta HTTP 201.
- **STM_204** (padrão: `not content`): Mensagem padrão para resposta HTTP 204.
- **STM_303** (padrão: `redirect`): Mensagem padrão para resposta HTTP 303.
- **STM_400** (padrão: `bad request`): Mensagem padrão para resposta HTTP 400.
- **STM_401** (padrão: `unauthorized`): Mensagem padrão para resposta HTTP 401.
- **STM_403** (padrão: `forbidden`): Mensagem padrão para resposta HTTP 403.
- **STM_404** (padrão: `not found`): Mensagem padrão para resposta HTTP 404.
- **STM_405** (padrão: `method not allowed`): Mensagem padrão para resposta HTTP 405.
- **STM_500** (padrão: `internal server error`): Mensagem padrão para resposta HTTP 500.
- **STM_501** (padrão: `not implemented`): Mensagem padrão para resposta HTTP 501.
- **STM_503** (padrão: `service unavailable`): Mensagem padrão para resposta HTTP 503.
