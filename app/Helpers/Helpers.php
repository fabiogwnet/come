<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Services\CacheService;
use Ramsey\Uuid\Uuid;

abstract class Helpers
{
    public static function clearCache($key)
    {
        CacheService::deleteByKey($key);
    }
    
    public static function getUuid()
    {
        $uuid4 = Uuid::uuid4(Uuid::NAMESPACE_DNS, 'comexio.com.br');
        return trim($uuid4->toString());
    }
}
