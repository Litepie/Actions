<?php

namespace Litepie\Actions\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Handles notifications and events for actions
 */
trait HandlesNotifications
{
    /**
     * Send notifications based on action configuration
     */
    protected function sendNotifications(): void
    {
        $notifications = $this->getNotifications();

        foreach ($notifications as $notification) {
            if (!isset($notification['recipients'], $notification['class'])) {
                continue;
            }

            $recipients = $notification['recipients'];
            $notificationClass = $notification['class'];

            if (!class_exists($notificationClass)) {
                Log::warning("Notification class not found: {$notificationClass}");
                continue;
            }

            try {
                Notification::send($recipients, new $notificationClass($this->result, $this->data));
            } catch (\Throwable $e) {
                Log::error("Failed to send notification: {$notificationClass}", [
                    'error' => $e->getMessage(),
                    'action' => static::class
                ]);
            }
        }
    }


    /**
     * Get notifications configuration
     */
    protected function getNotifications(): array
    {
        return [];
    }

}
