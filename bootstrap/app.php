<?php

use App\Services\JWTService;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = null;
if (getenv('APP_ENV') === 'testing') {
    if (!file_exists(__DIR__ . '/../.env.testing')) {
        throw new Exception('File .env.testing not found');
    }

    $dotenv = '.env.testing';
}

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__),
    $dotenv
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);


/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\RequestServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);

// Provider para ambiente de desenvolvimento
if (getenv('APP_ENV') == 'development') {
    $app->register(\NunoMaduro\Collision\Adapters\Laravel\CollisionServiceProvider::class);
    $app->register(Appzcoder\LumenRoutesList\RoutesCommandServiceProvider::class);
}

$app->register(Sentry\Laravel\ServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->routeMiddleware([
    'jwt.auth' => App\Http\Middleware\JwtMiddleware::class,
    'giver.request' => App\Http\Middleware\GiverRequestMiddleware::class,
    'database.selector' => App\Http\Middleware\DatabaseSelectorMiddleware::class,
    'session' => \Illuminate\Session\Middleware\StartSession::class
]);

$app->singleton(Illuminate\Session\SessionManager::class, function () use ($app) {
    return $app->loadComponent('session', Illuminate\Session\SessionServiceProvider::class, 'session');
});

$app->singleton('session.store', function () use ($app) {
    return $app->loadComponent('session', Illuminate\Session\SessionServiceProvider::class, 'session.store');
});

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/api.php';
});

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

return $app;
