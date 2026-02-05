<?php

use PhpMx\Request;

/** Se a requisição é do tipo GET */
define('IS_GET', !IS_TERMINAL && Request::type('GET'));

/** Se a requisição é do tipo POST */
define('IS_POST', !IS_TERMINAL && Request::type('POST'));

/** Se a requisição é do tipo PUT */
define('IS_PUT', !IS_TERMINAL && Request::type('PUT'));

/** Se a requisição é do tipo PATCH */
define('IS_PATCH', !IS_TERMINAL && Request::type('PATCH'));

/** Se a requisição é do tipo DELETE */
define('IS_DELETE', !IS_TERMINAL && Request::type('DELETE'));
