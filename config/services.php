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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'), // using a faster/cheaper model to save limits
    ],

    'ollama' => [
        // Ollama server utama untuk workload lokal / private inference.
        'base_url' => env('OLLAMA_BASE_URL', 'https://ollama.rsbaladhikahusada.com'),
        'model' => env('OLLAMA_MODEL', 'qwen2.5-coder:7b'),
        // Optional: Cloudflare Access service token headers
        'access_client_id' => env('OLLAMA_ACCESS_CLIENT_ID'),
        'access_client_secret' => env('OLLAMA_ACCESS_CLIENT_SECRET'),
    ],

];
