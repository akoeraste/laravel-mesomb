<?php

return [

    /*
     * Api Version
     */
    'version' => env('MESOMB_API_VERSION', 'v1.1'),

    /*
     * MeSomb Service Host
     */
    'host' => env('MESOMB_API_HOST', 'https://mesomb.hachther.com'),

    /*
     * MeSomb Application Key
     * Copy from https://mesomb.hachther.com/en/applications/{id}
     */
    'app_key' => env('MESOMB_APP_KEY'),

    /*
     * MeSomb Access Key
     * Copy from https://mesomb.hachther.com/en/applications/{id}
     */
    'access_key' => env('MESOMB_ACCESS_KEY'),

    /*
     * MeSomb Secret Key
     * Copy from https://mesomb.hachther.com/en/applications/{id}
     */
    'secret_key' => env('MESOMB_SECRET_KEY'),

    /*
     * Set MESOMB_SSL_VERIFY to false if you want to run your http request without verification
     */
    'ssl_verify' => env('MESOMB_SSL_VERIFY', true),

    /*
     * Number of second to wait before timeout a transaction
     */
    'timeout' => env('MESOMB_TIMEOUT', 60),

    /*
     * MeSomb Secret Key
     * Copy from https://mesomb.hachther.com/en/applications/{id}
     */
    'algorithm' => 'HMAC-SHA1',

    /*
     * PIN used for MeSomb Pin
     * Configure @ https://mesomb.hachther.com/en/applications/{id}/settings/setpin/
     */
    'pin' => env('MESOMB_PIN', null),

    /*
     * Supported Collect Methods
     */
    'currencies' => ['XAF', 'XOF'],

    /*
     * Support Collect Methods
     * Array in order of preference
     */
    'services' => ['MTN', 'ORANGE', 'AIRTEL'],

    /*
     * Supported countries
     */
    'countries' => ['CM', 'NE'],

    /*
     * Set to True if your application uses uuid instead auto-incrmenting ids
     */
    'uses_uuid' => false,

    /*
     * Used to store the application Status
     */
    'application_cache_key' => 'mesomb_application_status',

    /*
     * You can choose to wait till the application to wait till the payment is approved
     * or queue the payment request check later
     * enum: asynchronous, synchronous

     */
    'mode' => 'synchronous',

    'throw_exceptions' => true,
];
