<?php
declare(strict_types=1);

namespace Tests;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->resetDatabase();
    }

    public function resetDatabase(): void
    {
        $tables = [
            'test.capture_company',
            'test.company'
        ];

        foreach ($tables as $full_table_name) {
            DB::statement("
                TRUNCATE TABLE {$full_table_name};
            ");
        }
    }
}
