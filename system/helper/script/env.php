<?php


use PhpMx\Env;

/** Obriga o redirecionamento de todas as requisições para HTTPS */
Env::default('FORCE_SSL', true);

/** Chave secreta utilizada para assinatura e validação de tokens JWT */
Env::default('JWT_KEY', 'jwtkey');

/** Tempo de expiração global para o sistema de cache */
Env::default('CACHE', null);

/** Tempo de cache no navegador para arquivos Javascript (.js) */
Env::default('CACHE_JS', '+30 days');

/** Tempo de cache no navegador para arquivos de folha de estilo (.css) */
Env::default('CACHE_CSS', '+30 days');

/** Tempo de cache no navegador para ícones de favoritos (.ico) */
Env::default('CACHE_ICO', '+30 days');

/** Tempo de cache no navegador para imagens PNG */
Env::default('CACHE_PNG', '+30 days');

/** Tempo de cache no navegador para imagens JPG/JPEG */
Env::default('CACHE_JPG', '+30 days');

/** Tempo de cache no navegador para imagens BMP */
Env::default('CACHE_BMP', '+30 days');

/** Tempo de cache no navegador para imagens GIF */
Env::default('CACHE_GIF', '+30 days');

/** Tempo de cache no navegador para imagens de formato WEBP */
Env::default('CACHE_WEBP', '+30 days');

/** Tempo de cache no navegador para arquivos de áudio MP3 */
Env::default('CACHE_MP3', '+30 days');

/** Tempo de cache no navegador para arquivos de vídeo MP4 */
Env::default('CACHE_MP4', '+30 days');

/** Mensagem padrão para status HTTP 200 (Success) */
Env::default('STM_200', 'ok');

/** Mensagem padrão para status HTTP 201 (Created) */
Env::default('STM_201', 'created');

/** Mensagem padrão para status HTTP 204 (No Content) */
Env::default('STM_204', 'not content');

/** Mensagem padrão para status HTTP 303 (See Other/Redirect) */
Env::default('STM_303', 'redirect');

/** Mensagem padrão para status HTTP 400 (Bad Request) */
Env::default('STM_400', 'bad request');

/** Mensagem padrão para status HTTP 401 (Unauthorized) */
Env::default('STM_401', 'unauthorized');

/** Mensagem padrão para status HTTP 403 (Forbidden) */
Env::default('STM_403', 'forbidden');

/** Mensagem padrão para status HTTP 404 (Not Found) */
Env::default('STM_404', 'not found');

/** Mensagem padrão para status HTTP 405 (Method Not Allowed) */
Env::default('STM_405', 'method not allowed');

/** Mensagem padrão para status HTTP 500 (Internal Server Error) */
Env::default('STM_500', 'internal server error');

/** Mensagem padrão para status HTTP 501 (Not Implemented) */
Env::default('STM_501', 'not implemented');

/** Mensagem padrão para status HTTP 503 (Service Unavailable) */
Env::default('STM_503', 'service unavailable');
