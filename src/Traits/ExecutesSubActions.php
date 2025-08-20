<?php

namespace Litepie\Actions\Traits;

use Illuminate\Support\Facades\Log;
use Litepie\Actions\Exceptions\ActionException;

/**
 * Handles sub-action execution and management
 */
trait ExecutesSubActions
{
    /**
     * Execute sub-actions before or after main action execution
     *
     * @param string $timing Either 'before' or 'after'
     * @return array Results from executed sub-actions
     */
    protected function executeSubActions(string $timing = 'before'): array
    {
        $subActions = $this->getSubActions($timing);
        $results = [];

        foreach ($subActions as $subActionConfig) {
            $results[] = $this->executeSubAction($subActionConfig, $timing);
        }

        return $results;
    }

    /**
     * Execute a single sub-action
     */
    private function executeSubAction(array $subActionConfig, string $timing): array
    {
        try {
            $subActionClass = $subActionConfig['action'];
            $subActionData = $subActionConfig['data'] ?? [];
            $subActionUser = $subActionConfig['user'] ?? $this->user;
            $condition = $subActionConfig['condition'] ?? null;

            // Check condition if provided
            if ($condition && !$this->evaluateCondition($condition)) {
                return [
                    'action' => $subActionClass,
                    'status' => 'skipped',
                    'timing' => $timing,
                ];
            }

            // Merge main action data with sub-action specific data
            $mergedData = array_merge($this->data, $subActionData);

            // Add context from main action if this is an after sub-action
            if ($timing === 'after' && isset($this->result)) {
                $mergedData['main_action_result'] = $this->result;
                $mergedData['main_action_class'] = static::class;
            }

            // Execute the sub-action
            if (!class_exists($subActionClass)) {
                throw new \InvalidArgumentException("Sub-action class not found: {$subActionClass}");
            }

            $subActionResult = $subActionClass::execute($this->model, $mergedData, $subActionUser);

            return [
                'action' => $subActionClass,
                'status' => 'success',
                'result' => $subActionResult,
                'timing' => $timing,
            ];

        } catch (\Throwable $e) {
            $errorResult = [
                'action' => $subActionConfig['action'] ?? 'unknown',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'timing' => $timing,
            ];

            Log::error("Sub-action failed: " . ($subActionConfig['action'] ?? 'unknown'), [
                'main_action' => static::class,
                'error' => $e->getMessage(),
                'timing' => $timing,
            ]);

            // Decide whether to continue or fail the main action
            if (!($subActionConfig['continue_on_failure'] ?? false)) {
                throw new ActionException(
                    "Sub-action failed: {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e
                );
            }

            return $errorResult;
        }
    }

    /**
     * Evaluate a condition for conditional sub-action execution
     */
    protected function evaluateCondition(mixed $condition): bool
    {
        if (is_callable($condition)) {
            return $condition($this->data, $this->result, $this->user);
        }

        if (is_array($condition)) {
            // Support for simple field-value conditions
            if (isset($condition['field'], $condition['value'])) {
                $fieldValue = data_get($this->data, $condition['field']);
                $expectedValue = $condition['value'];

                if (is_array($expectedValue)) {
                    return in_array($fieldValue, $expectedValue);
                }

                return $fieldValue === $expectedValue;
            }
        }

        return true; // Default to true if condition format is not recognized
    }

    /**
     * Get sub-actions configuration
     *
     * @param string $timing Either 'before' or 'after'
     */
    protected function getSubActions(string $timing): array
    {
        return [];
    }

    /**
     * Count executed sub-actions for logging
     */
    protected function getSubActionsCount(): int
    {
        return count($this->getSubActions('before')) + count($this->getSubActions('after'));
    }
}
