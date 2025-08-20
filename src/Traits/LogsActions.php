<?php

namespace Litepie\Actions\Traits;

use Illuminate\Support\Facades\Log;
use Litepie\Actions\ActionLogger;

/**
 * Handles action logging functionality
 */
trait LogsActions
{
    /**
     * Log the action execution using ActionLogger
     *
     * @param string $status The execution status ('success' or 'failed')
     * @param string|null $message Optional message to log
     */
    protected function logAction(string $status, ?string $message = null): void
    {
        try {
            $targetModel = $this->model ?? $this->getTargetModel();
            $actionDescription = $this->getDescription($status);

            $actionLogger = app(ActionLogger::class)
                ->action($this->getName())
                ->description($actionDescription)
                ->property($this->getLogProperties())
                ->by($this->user);

            if ($targetModel) {
                $actionLogger->on($targetModel);
            }

            $actionLogger->save();

        } catch (\Exception $e) {
            Log::error("Failed to log action to database: " . $e->getMessage(), [
                'action' => static::class,
                'status' => $status
            ]);
        }
    }

    /**
     * Get the target model for this action (override in child classes)
     */
    protected function getTargetModel(): ?\Illuminate\Database\Eloquent\Model
    {
        return null;
    }

    /**
     * Get action description for logging (override in child classes)
     */
    protected function getDescription(string $status): string
    {
        $actionName = $this->getName();
        $actionName = ucfirst(preg_replace('/([A-Z])/', ' $1', $actionName));

        return trim($actionName) . ($status === 'success' ? ' completed successfully' : ' failed');
    }

    /**
     * Get properties for logging (general action data)
     */
    protected function getLogProperties(): array
    {
        // Safely get sub-actions count if ExecutesSubActions trait is used
        $subActionsCount = method_exists($this, 'getSubActionsCount') 
            ? $this->getSubActionsCount() 
            : 0;

        // Safely get notifications count if HandlesNotificationsAndEvents trait is used
        $notificationsCount = method_exists($this, 'getNotifications') 
            ? count($this->getNotifications()) 
            : 0;

        // Safely get events count if HandlesNotificationsAndEvents trait is used
        $eventsCount = method_exists($this, 'getEvents') 
            ? count($this->getEvents()) 
            : 0;

        return [
            'action_data' => $this->data,
            'status' => $this->status,
            'result' => $this->result,
            'user_agent' => request()?->userAgent(),
            'ip_address' => request()?->ip(),
            'timestamp' => now()->toISOString(),
            'sub_actions_executed' => $subActionsCount,
            'notifications_sent' => $notificationsCount,
            'events_fired' => $eventsCount,
        ];
    }
}
