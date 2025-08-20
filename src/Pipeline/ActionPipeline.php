<?php

namespace Litepie\Actions\Pipeline;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Litepie\Actions\ActionResult;
use Litepie\Actions\Contracts\ActionContract;

class ActionPipeline
{
    protected array $actions = [];
    protected array $middleware = [];
    protected mixed $initialData = null;

    public static function make(): static
    {
        return new static();
    }

    public function through(array $actions): static
    {
        $this->actions = $actions;
        return $this;
    }

    public function via(array $middleware): static
    {
        $this->middleware = $middleware;
        return $this;
    }

    public function send(mixed $data): static
    {
        $this->initialData = $data;
        return $this;
    }

    public function execute(): ActionResult
    {
        $data = $this->initialData;
        foreach ($this->actions as $actionClass) {
            $action = app($actionClass, ['data' => is_array($data) ? $data : ['input' => $data]]);
            if (!empty($this->middleware)) {
                $result = app(Pipeline::class)
                    ->send($action)
                    ->through($this->middleware)
                    ->then(fn($action) => $action->execute());
            } else {
                $result = $action->execute();
            }
            if ($result->isFailure()) {
                return $result;
            }
            $data = $result->getData();
        }
        return ActionResult::success($data);
    }
}
