<?php

declare(strict_types=1);

namespace App\Models;

class Company extends BaseModel
{
    protected $table = 'company';
    public $timestamps = false;

    public static function getList(array $filters): array
    {
        $query = Company::query();
        $query->select('company.name as company_name', 'company.profit', 'company.rank');

        if (!empty($filters)) {
            $query->filters($filters);
        }

        $query->orderBy('company.rank', 'ASC');
        $data = $query->makeCache("Company")->get();

        return $data->isEmpty() ? [] : $data->toArray();
    }

    public static function truncateCompaniesTable(): void
    {
        BaseModel::getConnectionResolver()->statement("truncate table company");
    }

    public static function insertCompanyData(array $data): void
    {
        BaseModel::getConnectionResolver()->table('company')->insert($data);
    }

    public function scopeFilters($query, $filters): void
    {
        if (!empty($filters['rule']) && in_array($filters['rule'], ['greater', 'smaller', 'between'])) {
            if (in_array($filters['rule'], ['greater', 'smaller']) && isset($filters['billions']) && is_numeric($filters['billions'])) {
                if (is_numeric($filters['billions'])) {
                    $condition = $filters['rule'] === 'greater' ? '>' : '<';
                    $query->where('company.profit', $condition, $filters['billions']);
                } else {
                    throw new \InvalidArgumentException('Value billions must be numeric.');
                }
            } elseif ($filters['rule'] === 'between' && isset($filters['range']) && is_array($filters['range']) && count($filters['range']) === 2) {
                if (is_numeric($filters['range'][0]) && is_numeric($filters['range'][1])) {
                    $query->whereBetween('company.profit', $filters['range']);
                } else {
                    throw new \InvalidArgumentException('Both values in range must be numeric.');
                }
            }
        }
    }
}
