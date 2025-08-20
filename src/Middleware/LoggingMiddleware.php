<?php

namespace Litepie\Actions\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Litepie\Actions\Contracts\ActionContract;

class LoggingMiddleware extends ActionMiddleware
{
    public function handle(ActionContract $action, Closure $next): mixed
    {
        Log::info('Action started', [
            'action' => $action->getName(),
            'data' => $action->getData(),
        ]);
        $result = $next($action);
        Log::info('Action completed', [
            'action' => $action->getName(),
            'success' => $result->isSuccess(),
        ]);
        return $result;
    }
}
