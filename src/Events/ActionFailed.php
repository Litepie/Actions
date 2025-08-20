<?php

namespace Litepie\Actions\Events;

use Litepie\Actions\Contracts\ActionContract;
use Throwable;

class ActionFailed
{
    public function __construct(
        public ActionContract $action,
        public Throwable $exception
    ) {}
}
