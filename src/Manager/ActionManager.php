<?php

namespace Litepie\Actions\Manager;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Litepie\Actions\ActionResult;
use Litepie\Actions\Contracts\ActionContract;
use Litepie\Actions\Exceptions\ActionException;

class ActionManager
{
    protected Container $container;
    protected array $actions = [];
    protected Collection $history;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->history = collect();
    }

    public function register(string $name, string $actionClass): void
    {
        if (!is_subclass_of($actionClass, ActionContract::class)) {
            throw new ActionException("Action {$actionClass} must implement ActionContract");
        }
        $this->actions[$name] = $actionClass;
    }

    public function execute(string $name, array $data = []): ActionResult
    {
        if (!isset($this->actions[$name])) {
            throw new ActionException("Action '{$name}' is not registered");
        }
        $action = $this->container->make($this->actions[$name], ['data' => $data]);
        $result = $action->execute();
        $this->history->push([
            'name' => $name,
            'action' => $action,
            'result' => $result,
            'executed_at' => now(),
        ]);
        return $result;
    }

    public function chain(array $actions): Collection
    {
        $results = collect();
        $previousResult = null;
        foreach ($actions as $actionConfig) {
            $name = $actionConfig['name'] ?? null;
            $data = $actionConfig['data'] ?? [];
            if (!$name) {
                throw new ActionException('Action name is required in chain');
            }
            if (isset($actionConfig['use_previous_result']) && $actionConfig['use_previous_result'] && $previousResult) {
                $data = array_merge($data, ['previous_result' => $previousResult->getData()]);
            }
            $result = $this->execute($name, $data);
            $results->push($result);
            $previousResult = $result;
            if (isset($actionConfig['stop_on_failure']) && $actionConfig['stop_on_failure'] && $result->isFailure()) {
                break;
            }
        }
        return $results;
    }

    public function parallel(array $actions): Collection
    {
        $results = collect();
        foreach ($actions as $actionConfig) {
            $name = $actionConfig['name'] ?? null;
            $data = $actionConfig['data'] ?? [];
            if (!$name) {
                throw new ActionException('Action name is required in parallel execution');
            }
            $result = $this->execute($name, $data);
            $results->push($result);
        }
        return $results;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getHistory(): Collection
    {
        return $this->history;
    }

    public function clearHistory(): void
    {
        $this->history = collect();
    }

    public function getStatistics(): array
    {
        $total = $this->history->count();
        $successful = $this->history->filter(fn($item) => $item['result']->isSuccess())->count();
        $failed = $total - $successful;
        return [
            'total_executions' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? ($successful / $total) * 100 : 0,
            'most_used_actions' => $this->history->countBy('name')->sortDesc()->take(5)->toArray(),
        ];
    }
}
