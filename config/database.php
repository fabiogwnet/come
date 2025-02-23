<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_DEFAULT_CONNECTION', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        /*'testing' => [
            'driver' => 'mysql',
            'host' => 'comexio_database_test',
            'database' => env('DB_DEFAULT_DATABASE'),
            'username' => env('DB_DEFAULT_USERNAME'),
            'password' => env('DB_DEFAULT_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', true),
            'engine' => env('DB_ENGINE', null),
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true,
            ],
            'modes' => [
                'NO_ENGINE_SUBSTITUTION',
            ]
        ],*/

        'testing' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_DEFAULT_HOSTNAME') ? env('DB_DEFAULT_HOSTNAME') : 'comexio_postgres',
            'port'     => env('DB_DEFAULT_PORT') ? env('DB_DEFAULT_PORT') : '5432',
            'database' => 'api',
            'username' => env('DB_DEFAULT_USERNAME'),
            'password' => env('DB_DEFAULT_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'test'
        ],

        'api' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_DEFAULT_HOSTNAME') ? env('DB_DEFAULT_HOSTNAME') : 'comexio_postgres',
            'port'     => env('DB_DEFAULT_PORT') ? env('DB_DEFAULT_PORT') : '5432',
            'database' => 'api',
            'username' => env('DB_DEFAULT_USERNAME'),
            'password' => env('DB_DEFAULT_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public'
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host'     => env('DB_DEFAULT_HOSTNAME') ? env('DB_DEFAULT_HOSTNAME') : 'comexio_sqlsrv',
            'port'     => env('DB_DEFAULT_PORT') ? env('DB_DEFAULT_PORT') : '1433',
            'database' => 'dibri',
            'username' => env('DB_DEFAULT_USERNAME'),
            'password' => env('DB_DEFAULT_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'encrypt' => 'yes',
            'trust_server_certificate' => true
        ],

        'mysql' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_DEFAULT_HOSTNAME') ? env('DB_DEFAULT_HOSTNAME') : 'comexio_postgres',
            'port'     => env('DB_DEFAULT_PORT') ? env('DB_DEFAULT_PORT') : '5432',
            'database' => 'api',
            'username' => env('DB_DEFAULT_USERNAME'),
            'password' => env('DB_DEFAULT_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public'
        ],

        'comexio_mysql' => [
            'driver' => 'mysql',
            'host' => 'comexio_mysql',
            'port' => 3306,
            'database' => 'api',
            'username' => env('DB_DEFAULT_USERNAME'),
            'password' => env('DB_DEFAULT_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', true),
            'engine' => env('DB_ENGINE', null),
            'options' => [
                \PDO::ATTR_EMULATE_PREPARES => true,
            ],
            'modes' => [
                'NO_ENGINE_SUBSTITUTION',
            ]
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'cluster' => env('REDIS_CLUSTER', false),

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],

    ]
];
