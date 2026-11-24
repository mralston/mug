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

    /*
    |--------------------------------------------------------------------------
    | Token cache
    |--------------------------------------------------------------------------
    |
    | The cache store the access token is kept in until shortly before it
    | expires, so each request does not sign in again. Empty uses the
    | application's default store.
    |
    */

    'token_cache_store' => env('MUG_TOKEN_CACHE_STORE'),
];
