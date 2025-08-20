<?php

namespace Litepie\Actions\Traits;

use Illuminate\Support\Facades\Cache;
use Litepie\Actions\ActionResult;

trait HasCaching
{
    protected function getCacheKey(): string
    {
        if ($this->cacheKey) {
            return config('actions.cache.prefix', 'action') . ':' . $this->cacheKey;
        }
        return config('actions.cache.prefix', 'action') . ':' . $this->getName() . ':' . md5(serialize($this->data));
    }

    protected function hasCachedResult(): bool
    {
        return config('actions.cache.enabled', true) && Cache::has($this->getCacheKey());
    }

    protected function getCachedResult(): ActionResult
    {
        return Cache::get($this->getCacheKey());
    }

    protected function cacheResult(ActionResult $result): void
    {
        if (!config('actions.cache.enabled', true)) {
            return;
        }
        Cache::put($this->getCacheKey(), $result, $this->cacheTtl);
    }

    public function clearCache(): static
    {
        Cache::forget($this->getCacheKey());
        return $this;
    }
}
