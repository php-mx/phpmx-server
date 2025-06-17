<?php

use PhpMx\Env;

Env::default('FORCE_SSL', false);

Env::default('TERMINAL_URL', 'http://localhost:8888');

Env::default('JWT', 'jwt-key');

Env::default('CACHE', null);
Env::default('CACHE_JS', '+30 days');
Env::default('CACHE_CSS', '+30 days');
Env::default('CACHE_ICO', '+30 days');
Env::default('CACHE_PNG', '+30 days');
Env::default('CACHE_JPG', '+30 days');
Env::default('CACHE_BMP', '+30 days');
Env::default('CACHE_GIF', '+30 days');
Env::default('CACHE_WEBP', '+30 days');
Env::default('CACHE_MP3', '+30 days');
Env::default('CACHE_MP4', '+30 days');

Env::default('STM_200', 'ok');
Env::default('STM_201', 'created');
Env::default('STM_204', 'not content');
Env::default('STM_303', 'redirect');
Env::default('STM_400', 'bad request');
Env::default('STM_401', 'unauthorized');
Env::default('STM_403', 'forbidden');
Env::default('STM_404', 'not found');
Env::default('STM_405', 'method not allowed');
Env::default('STM_500', 'internal server error');
Env::default('STM_501', 'not implemented');
Env::default('STM_503', 'service unavailable');
