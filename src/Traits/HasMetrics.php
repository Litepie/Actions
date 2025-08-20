<?php

namespace Litepie\Actions\Traits;

trait HasMetrics
{
    protected float $startTime;
    protected array $metrics = [];

    protected function startMetrics(): void
    {
        $this->startTime = microtime(true);
    }

    protected function recordSuccess(): void
    {
        $this->metrics = [
            'execution_time' => microtime(true) - $this->startTime,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'status' => 'success',
        ];
    }

    protected function recordFailure(\Throwable $exception): void
    {
        $this->metrics = [
            'execution_time' => microtime(true) - $this->startTime,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'status' => 'failure',
            'error' => $exception->getMessage(),
        ];
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
