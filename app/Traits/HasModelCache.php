<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasModelCache
{
    /**
     * Default TTL in seconds (1 hour).
     */
    protected static int $cacheTtl = 3600;

    /**
     * Boot trait hooks for Eloquent events.
     * Naming convention: boot{TraitName}() runs automatically.
     */
    public static function bootHasModelCache(): void
    {
        // 1. Immediately store in cache upon creation
        static::created(function ($model) {
            $model->putCache();
        });

        // 2. Refresh cache when updated
        static::updated(function ($model) {
            $model->putCache();
        });

        // 3. Remove from cache when deleted
        static::deleted(function ($model) {
            $model->forgetCache();
        });
    }

    /**
     * Build the unique cache key for the model.
     */
    public static function getCacheKeyFor(
        string $field,
        mixed $value,
    ): string {
        return strtolower(class_basename(static::class)).":{$field}:{$value}";
    }

    /**
     * Write the current instance directly into cache.
     */
    public function putCache(?int $ttl = null): bool
    {
        $ttl ??= static::$cacheTtl;

        return Cache::tags([$this->getTable()])->put($this->getCacheKeyFor($this->getKeyName(), $this->getKey()), $this, $ttl);
    }

    /**
     * Remove the current instance from cache.
     */
    public function forgetCache(): bool
    {
        return Cache::tags([$this->getTable()])->forget($this->getCacheKeyFor($this->getKeyName(), $this->getKey()));
    }

    /**
     * Fetch from cache by ID, falling back to DB if missing.
     */
    public static function findCached(int|string $id, ?int $ttl = null): ?static
    {
        $instance = new static;
        $cacheKey = static::getCacheKeyFor(
            $instance->getKeyName(),
            $id
        );
        $ttl ??= static::$cacheTtl;

        return Cache::tags([$instance->getTable()])->remember($cacheKey, $ttl, function () use ($id) {
            return static::find($id);
        });
    }
}
