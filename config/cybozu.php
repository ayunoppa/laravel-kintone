<?php

return [
    'subdomain' => env('CYBOZU_SUBDOMAIN'),

    'client_id' => env('CYBOZU_OAUTH_CLIENT_ID'),
    'client_secret' => env('CYBOZU_OAUTH_CLIENT_SECRET'),
    'redirect_uri' => env('CYBOZU_OAUTH_REDIRECT_URI'),

    'scopes' => preg_split('/\s+/', trim(env('CYBOZU_OAUTH_SCOPES', '')), -1, PREG_SPLIT_NO_EMPTY),

    // endpoints
    'authorize_url' => fn ($subdomain) => "https://{$subdomain}.cybozu.com/oauth2/authorization",
    'token_url'     => fn ($subdomain) => "https://{$subdomain}.cybozu.com/oauth2/token",

    // kintone REST base
    'kintone_base_url' => fn ($subdomain) => "https://{$subdomain}.cybozu.com",
];
