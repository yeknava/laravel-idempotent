<?php

return [
    'expires_in' => 86400, //24h
    'force' => true, //force idempotent key exists (it wont force on GET method anyway)
    'skip_http_methods' => [
        'HEAD',
        'OPTIONS',
    ],
    'header_key' => 'Idempotency-Key'
];
