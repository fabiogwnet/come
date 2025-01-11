<?php
declare(strict_types=1);

namespace App\Models;

class Company extends BaseModel
{
    protected $table = 'company';
    public $timestamps = false;

    public static function getList() {
        $data = [];

        $data[] = [
            "company_name" => "Petrobras",
            "profit" => "36.47",
            "rank" => 1
        ];

        $data[] = [
            "company_name" => "Vale",
            "profit" => "15.98",
            "rank" => 3
        ];

        return $data;
    }

    public static function truncateCompaniesTable(): void
    {
        BaseModel::getConnectionResolver()->statement("truncate table company");
    }

    public static function insertCompanyData(array $data): void
    {
        BaseModel::getConnectionResolver()->table('company')->insert($data);
    }
}
