<?php

namespace Litepie\Actions\Traits;

use Illuminate\Support\Facades\Event;

trait HasEvents
{
    protected function fireEvent(object $event): void
    {
        if (config('actions.events.enabled', true)) {
            Event::dispatch($event);
        }
    }
}
