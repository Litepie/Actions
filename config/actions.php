<?php

return [
    'cache' => [
        'enabled' => env('ACTIONS_CACHE_ENABLED', true),
        'ttl' => env('ACTIONS_CACHE_TTL', 3600),
        'prefix' => env('ACTIONS_CACHE_PREFIX', 'action'),
    ],
    'events' => [
        'enabled' => env('ACTIONS_EVENTS_ENABLED', true),
        'prefix' => 'action',
    ],
    'validation' => [
        'stop_on_first_failure' => true,
        'bail' => true,
    ],
    'defaults' => [
        'async' => false,
        'retries' => 3,
        'timeout' => 300,
    ],
    'logging' => [
        'enabled' => env('ACTIONS_LOGGING_ENABLED', true),
        'default_log_name' => env('ACTIONS_DEFAULT_LOG_NAME', 'actions'),
        'auth_driver' => env('ACTIONS_AUTH_DRIVER', null),
        'delete_records_older_than_days' => 365,
    ],
    'authorization' => [
        'enabled' => env('ACTIONS_AUTHORIZATION_ENABLED', true),
        'require_authenticated_user' => true,
    ],
    'forms' => [
        'enabled' => env('ACTIONS_FORMS_ENABLED', true),
        'default_theme' => 'bootstrap',
    ],
    'sub_actions' => [
        'enabled' => env('ACTIONS_SUB_ACTIONS_ENABLED', true),
        'max_depth' => 5,
        'continue_on_failure' => false,
    ],
    'notifications' => [
        'enabled' => env('ACTIONS_NOTIFICATIONS_ENABLED', true),
        'queue' => env('ACTIONS_NOTIFICATIONS_QUEUE', 'default'),
    ],
];
