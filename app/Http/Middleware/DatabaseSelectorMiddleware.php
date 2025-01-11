<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseSelectorMiddleware
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $this->connectionDb(getenv("DB_DEFAULT_CONNECTION"));

        return $next($request);
    }

    private function connectionDb($dbName)
    {
        DB::setDefaultConnection($dbName);
        DB::connection()->reconnect();
    }
}
