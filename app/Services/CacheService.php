<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class CacheService
{
    public static function getAllKeys()
    {
        $allKeys = Redis::keys('*');
        return $allKeys;
    }

    public static function flush()
    {
        return Redis::del('*');
    }

    public static function deleteByKey(string $key): bool
    {
        $key =  "-{$key}-";

        $allKeys = self::getAllKeys();

        $exist = false;

        if (is_array($allKeys) && count($allKeys)) {
            foreach ($allKeys as $item) {

                if (strpos($item, $key)) {
                    if (strpos($item, $key)) {
                        Redis::del($item);
                        $exist = true;
                    }
                }
            }
        }

        return $exist;
    }
}
