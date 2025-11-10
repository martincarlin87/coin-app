<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CoinGecko
    |--------------------------------------------------------------------------
    |
    | Api key for use with the CoinGecko API.
    | The header is set to 'x-cg-pro-api-key' for production and 'x-cg-demo-api-key' for other environments.
    | Similarly, the base url for the production api is 'https://pro-api.coingecko.com/api/v3/ and 'https://api.coingecko.com/api/v3' for non-production environments.
    |
    | https://www.coingecko.com/en/developers/dashboard
    | https://docs.coingecko.com/reference/authentication
    |
    */

    'base_url' => env('APP_ENV') === 'production' ? 'https://pro-api.coingecko.com/api/v3/' : 'https://api.coingecko.com/api/v3',
    'key' => env('COINGECKO_API_KEY'),
    'header' => env('APP_ENV') === 'production' ? 'x-cg-pro-api-key' : 'x-cg-demo-api-key',
];
