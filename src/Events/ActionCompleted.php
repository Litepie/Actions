<?php

namespace Litepie\Actions\Events;

use Litepie\Actions\ActionResult;
use Litepie\Actions\Contracts\ActionContract;

class ActionCompleted
{
    public function __construct(
        public ActionContract $action,
        public ActionResult $result
    ) {}
}
