<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    /*
     * Paths for which CORS should be enabled.
     */
    'paths' => ['api/*', 'docs/api', 'docs/api/*'],

    /*
     * Allowed methods for CORS requests.
     */
    'allowed_methods' => ['*'],

    /*
     * Allowed origins for CORS requests.
     * In local development, allow all origins for Scramble Try It feature.
     */
    'allowed_origins' => env('APP_ENV', 'production') === 'local'
        ? ['*']
        : ['https://stockmaster.diegochacondev.es'],

    /*
     * Patterns for allowed origins.
     */
    'allowed_origins_patterns' => [],

    /*
     * Headers that can be sent in CORS requests.
     */
    'allowed_headers' => ['*'],

    /*
     * Headers that can be exposed in CORS responses.
     */
    'exposed_headers' => [],

    /*
     * Maximum age in seconds that preflight requests can be cached.
     */
    'max_age' => 0,

    /*
     * Whether credentials (cookies, authorization headers) can be sent.
     */
    'supports_credentials' => true,

];
