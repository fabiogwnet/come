<?php
declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        Hash::setRounds(5);

        return $app;
    }
}
