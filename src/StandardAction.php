<?php

namespace Litepie\Actions;

use Litepie\Actions\Traits\AuthorizesActions;
use Litepie\Actions\Traits\LogsActions;

/**
 * Standard Action Class - Common action with essential traits
 *
 * This class includes the most commonly used traits for typical actions:
 * - Authorization checking
 * - Data validation (inherited from BaseAction)
 * - Action logging
 *
 * Use this as a base for most of your actions that need basic functionality.
 */
abstract class StandardAction extends BaseAction
{
    use AuthorizesActions, LogsActions;
}
