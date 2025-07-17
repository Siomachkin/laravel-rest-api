<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | This value determines the current API version. This is used in various
    | places throughout the application for version-specific behavior.
    |
    */
    'version' => env('API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for different API endpoints.
    |
    */
    'rate_limits' => [
        'default' => env('API_RATE_LIMIT_DEFAULT', 60),
        'auth' => env('API_RATE_LIMIT_AUTH', 30),
        'strict' => env('API_RATE_LIMIT_STRICT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Pagination
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for API responses.
    |
    */
    'pagination' => [
        'default_per_page' => env('API_PAGINATION_DEFAULT', 15),
        'max_per_page' => env('API_PAGINATION_MAX', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Response Format
    |--------------------------------------------------------------------------
    |
    | Configure the standard response format for API endpoints.
    |
    */
    'response' => [
        'include_timestamp' => env('API_INCLUDE_TIMESTAMP', true),
        'include_request_id' => env('API_INCLUDE_REQUEST_ID', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    |
    | Security-related configuration for the API.
    |
    */
    'security' => [
        'sanitize_input' => env('API_SANITIZE_INPUT', true),
        'log_requests' => env('API_LOG_REQUESTS', true),
        'log_responses' => env('API_LOG_RESPONSES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Documentation
    |--------------------------------------------------------------------------
    |
    | Configuration for API documentation generation.
    |
    */
    'docs' => [
        'enabled' => env('API_DOCS_ENABLED', true),
        'url' => env('API_DOCS_URL', '/docs'),
        'title' => env('API_DOCS_TITLE', 'User CRUD API Documentation'),
        'description' => env('API_DOCS_DESCRIPTION', 'REST API for managing users with multiple email addresses.'),
    ],
];