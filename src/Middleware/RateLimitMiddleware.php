<?php

namespace Litepie\Actions\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Litepie\Actions\Contracts\ActionContract;
use Litepie\Actions\Exceptions\ActionException;

class RateLimitMiddleware extends ActionMiddleware
{
    public function __construct(
        protected int $maxAttempts = 60,
        protected int $decayMinutes = 1
    ) {}

    public function handle(ActionContract $action, Closure $next): mixed
    {
        $key = $this->resolveRequestSignature($action);
        $maxAttempts = $this->maxAttempts;
        if (Cache::has($key) && Cache::get($key) >= $maxAttempts) {
            throw new ActionException('Rate limit exceeded for action: ' . $action->getName());
        }
        Cache::put($key, Cache::get($key, 0) + 1, now()->addMinutes($this->decayMinutes));
        return $next($action);
    }

    protected function resolveRequestSignature(ActionContract $action): string
    {
        return 'action_rate_limit:' . $action->getName() . ':' . md5(serialize($action->getData()));
    }
}
