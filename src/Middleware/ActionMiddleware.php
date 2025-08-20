<?php

namespace Litepie\Actions\Middleware;

use Closure;
use Litepie\Actions\Contracts\ActionContract;

abstract class ActionMiddleware
{
    abstract public function handle(ActionContract $action, Closure $next): mixed;
}
