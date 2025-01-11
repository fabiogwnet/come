<?php

namespace App\Providers;

use App\Http\Middleware\DatabaseSelectorMiddleware;
use App\Http\Middleware\GiverRequestMiddleware;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Pearl\RequestValidate\RequestAbstract;

class RequestServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->commands([
            \Pearl\RequestValidate\Console\RequestMakeCommand::class
        ]);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->afterResolving(RequestAbstract::class, function ($resolved) {
            $resolved->validateResolved();
        });

        $this->app->resolving(RequestAbstract::class, function ($request, $app) {
            $request = RequestAbstract::createFrom($app['request'], $request);

            $attributesToCopy = ['jwtAuth', 'scope', 'brand', 'stores', 'storeIds'];
            foreach ($attributesToCopy as $key) {
                $request->$key = $app['request']->$key;
            }

            $request->setContainer($app);
        });
    }
}
