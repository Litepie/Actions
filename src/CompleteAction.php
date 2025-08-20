<?php

namespace Litepie\Actions;

use Litepie\Actions\Traits\AuthorizesActions;
use Litepie\Actions\Traits\ExecutesSubActions;
use Litepie\Actions\Traits\HandlesNotificationsAndEvents;
use Litepie\Actions\Traits\LogsActions;
use Litepie\Actions\Traits\ManagesForms;

/**
 * Complete Action Class - Full-featured action with all capabilities
 *
 * This class includes all available traits for complex actions that need:
 * - Authorization checking
 * - Data validation (inherited from BaseAction)
 * - Form management
 * - Sub-action execution
 * - Notifications and events
 * - Action logging
 *
 * Use this as a base for complex actions that need advanced functionality.
 */
abstract class CompleteAction extends BaseAction
{
    use AuthorizesActions, 
        ManagesForms, 
        ExecutesSubActions, 
        HandlesNotificationsAndEvents, 
        LogsActions;
}
