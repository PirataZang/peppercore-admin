<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Only the public lead-capture endpoint is opened cross-origin, since it's
    | meant to be called directly from the marketing site on another domain.
    | The authenticated /api/* routes stay same-origin (served behind the
    | same reverse proxy as the SPA) and are not covered here.
    |
    */

    'paths' => ['api/public/*'],

    'allowed_methods' => ['POST'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
