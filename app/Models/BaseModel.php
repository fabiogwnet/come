<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use Cacheable;
    public $timestamps = true;

    public function save(array $options = array())
    {
        $saved = parent::save($options);

        //====== CLEAR CACHE ======//
        $cache_key = str_replace('App\Models\\', '', get_class($this));
        Helpers::clearCache(trim($cache_key));

        if (!empty($this->cache_dependents)) {
            foreach ($this->cache_dependents as $dependent_model) {
                $cache_key = str_replace('App\Models\\', '', $dependent_model);
                Helpers::clearCache($cache_key);
            }
        }

        return $saved;
    }

    public function delete()
    {
        $deleted = parent::delete();

        //====== CLEAR CACHE ======//
        $cache_key = str_replace('App\Models\\', '', get_class($this));
        Helpers::clearCache($cache_key);

        if (!empty($this->cache_dependents)) {
            foreach ($this->cache_dependents as $dependent_model) {
                $cache_key = str_replace('App\Models\\', '', $dependent_model);
                Helpers::clearCache($cache_key);
            }
        }

        return $deleted;
    }

    public function scopeDebugSql($model): string
    {
        $query = str_replace(['%', '?'], ['%%', '\'%s\''], $model->toSql());
        $query = vsprintf($query, $model->getBindings());

        return $query;
    }
}
