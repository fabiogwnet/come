<?php

namespace App\Models;

use App\Query\CacheBuilder;

trait Cacheable
{
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        $builder = new CacheBuilder($conn, $grammar, $conn->getPostProcessor());
        $builder->setModel($this);

        if (isset($this->rememberFor)) {
            $builder->remember($this->rememberFor);
        }

        if (isset($this->rememberCacheTag)) {
            $builder->cacheTags($this->rememberCacheTag);
        }

        if (isset($this->rememberCachePrefix)) {
            $builder->prefix($this->rememberCachePrefix);
        }

        if (isset($this->rememberCacheDriver)) {
            $builder->cacheDriver($this->rememberCacheDriver);
        }

        return $builder;
    }
}
