<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | Used to log in to the My Utility Genius API
    |
    */

    'client_id' => env('MUG_CLIENT_ID'),
    'secret' => env('MUG_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    |
    | Determines which endpoint should be used. Possible values:
    |
    | local
    | dev
    | testing
    | production
    |
    */

    'endpoint' => env('MUG_ENDPOINT'),
];
