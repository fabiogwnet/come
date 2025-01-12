<?php

declare(strict_types=1);

namespace App\Query;

use Watson\Rememberable\Query\Builder;

class CacheBuilder extends Builder
{
    protected $model;

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * Alias to remember function
     *
     * @param int $seconds
     * @param string|null $key
     * @return $this
     */
    public function cache(int $seconds = 86400, string $key = null): self
    {
        return $this->remember($seconds, $key);
    }

    public function makeCache($key = null, $seconds = 86400): self
    {
        $key = "-" . $key . "-";
        return $this->remember($seconds, $key);
    }


    /**
     * Get a unique cache key for the complete query.
     *
     * @param  mixed  $appends
     * @return string
     */
    public function getCacheKey($appends = null)
    {
        $query_string = $this->toSql();
        $query_params = $this->getBindings();

        $cacheSufix = md5($query_string . serialize($query_params));

        return $this->cachePrefix . '_' . ($this->cacheKey ?: $this->generateCacheKey()) . '_' . $cacheSufix;
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @param  mixed  $appends
     * @return string
     */
    public function generateCacheKey($appends = null)
    {
        $conn = ucfirst($this->connection->getName());
        $modelName = get_class($this->getModel());
        $key = join('_', [$conn, $modelName]);
        return $key;
    }
}
