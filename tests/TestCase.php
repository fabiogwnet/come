<?php

declare(strict_types=1);

namespace Tests;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use App\Models\BaseModel;

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
            'test.company',
        ];

        foreach ($tables as $full_table_name) {
            $this->db()->statement("
                TRUNCATE TABLE {$full_table_name}
            ");
        }
    }

    private function db()
    {
        return BaseModel::getConnectionResolver();
    }
}
