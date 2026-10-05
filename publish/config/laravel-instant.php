<?php

return [
    'app' => [
        'name' => env('APP_URL'),
        'secret_key' => env('LI_SECRET_KEY'),
    ],
    'response' => [
        'read_class' => [
            'App\\',
            'Diatria\\',
        ],
    ],
    'query' => [
        'reject_unknown_fields' => true,
    ],
    'database' => [
        'primary_key' => env('LI_PRIMARY_KEY', 'int'),
        'migrations' => [
            'enabled' => false,
        ],
    ],
    'models' => [
        'user' => Diatria\LaravelInstant\Models\User::class,
        'role' => Diatria\LaravelInstant\Models\Role::class,
        'permission' => Diatria\LaravelInstant\Models\Permission::class,
        'role_permission' => Diatria\LaravelInstant\Models\RolePermission::class,
    ],
    'route' => [
        'enabled' => false,
        // Set to 'api', 'api/v1', or another prefix when routes are enabled.
        'prefix' => 'api',
        'middleware' => ['api'],
    ],
    'auth' => [
        'driver' => env('LI_AUTH_DRIVER', 'jwt'),
        'secret_key' => env('LI_SECRET_KEY'),
        'algorithm' => env('LI_JWT_ALGORITHM', 'HS256'),
        'access_token_name' => env('LI_ACCESS_TOKEN_NAME', 'laravel_instant'),
        'refresh_token_name' => env('LI_REFRESH_TOKEN_NAME', 'laravel_instant_refresh'),
        'access_token_expires' => 3600,
        'refresh_token_expires' => 21600,
    ],
    'class_permission' => Diatria\LaravelInstant\Utils\Permission::class,
    'disable_permissions' => false,
    'cookies' => [
        'name' => env('LI_COOKIE_NAME'),
        'expires' => 86400, // 1 day
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'none',
        'domain' => env('COOKIES_DOMAIN'),
    ],
];
