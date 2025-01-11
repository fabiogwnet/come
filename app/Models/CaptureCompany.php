<?php
declare(strict_types=1);

namespace App\Models;

class CaptureCompany extends BaseModel
{
    protected $table = 'capture_company';
    public $timestamps = false;

    public static function createCaptureCompany(string $html): CaptureCompany
    {
        $capture_company = new CaptureCompany();
        $capture_company->body = $html;
        $capture_company->created_at = date("Y-m-d H:i:s");
        $capture_company->save();

        return $capture_company;
    }
}
