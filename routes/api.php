<?php
$router->group(['prefix' => '/', 'namespace' => ''], function () use ($router) {
    $router->group(['middleware' => ['session', 'jwt.auth', 'giver.request', 'database.selector']], function () use ($router) {
        //CLI Group
        $router->group([
            'prefix' => 'api',
            'namespace' => 'Api\\'
        ], function () use ($router) {
            $router->get('company/list', 'CompanyController@list');
        });
    });
});
