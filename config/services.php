<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 4000),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],

    'aimlapi' => [
        'api_key' => env('AI_API_KEY'),
        'base_url' => env('AI_BASE_URL', 'https://api.aimlapi.com/v1'),
        'model' => env('AI_DEFAULT_MODEL', 'gpt-4o'),
        'max_tokens' => env('AIMLAPI_MAX_TOKENS', 2048),
        'temperature' => env('AIMLAPI_TEMPERATURE', 0.7),
        'top_p' => env('AIMLAPI_TOP_P', 0.9),
        'frequency_penalty' => env('AIMLAPI_FREQUENCY_PENALTY', 0.0),
        'presence_penalty' => env('AIMLAPI_PRESENCE_PENALTY', 0.0),
    ],

];
