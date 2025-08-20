<?php

namespace Litepie\Actions\Traits;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Litepie\Actions\Exceptions\ActionException;

/**
 * Handles authorization logic for actions
 */
trait AuthorizesActions
{
    /**
     * Check if the user is authorized to perform this action
     *
     * @throws ActionException
     */
    protected function authorize(): void
    {
        // Default: require authenticated user
        if (!$this->user) {
            throw new ActionException('User must be authenticated to perform this action');
        }

        // Check if specific gate exists for this action
        $gateName = $this->getGateName();
        if (Gate::has($gateName)) {
            if (!Gate::forUser($this->user)->allows($gateName, [$this->model, $this->data])) {
                throw new ActionException('User is not authorized to perform this action');
            }
        }
    }

    /**
     * Get the gate name for authorization
     */
    protected function getGateName(): string
    {
        return Str::kebab(class_basename(static::class));
    }
}
