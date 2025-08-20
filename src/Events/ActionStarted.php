<?php

namespace Litepie\Actions\Events;

use Litepie\Actions\Contracts\ActionContract;

class ActionStarted
{
    public function __construct(
        public ActionContract $action,
        public array $data
    ) {}
}
