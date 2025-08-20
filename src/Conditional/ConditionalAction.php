<?php

namespace Litepie\Actions\Conditional;

use Closure;
use Litepie\Actions\ActionResult;
use Litepie\Actions\BaseAction;

class ConditionalAction extends BaseAction
{
    protected ?Closure $condition = null;
    protected ?string $trueAction = null;
    protected ?string $falseAction = null;
    protected array $trueActionData = [];
    protected array $falseActionData = [];

    public function when(Closure $condition): static
    {
        $this->condition = $condition;
        return $this;
    }

    public function then(string $actionClass, array $data = []): static
    {
        $this->trueAction = $actionClass;
        $this->trueActionData = $data;
        return $this;
    }

    public function otherwise(string $actionClass, array $data = []): static
    {
        $this->falseAction = $actionClass;
        $this->falseActionData = $data;
        return $this;
    }

    protected function handle(): ActionResult
    {
        $conditionResult = ($this->condition)($this->data);
        if ($conditionResult && $this->trueAction) {
            $action = app($this->trueAction, ['data' => array_merge($this->data, $this->trueActionData)]);
            return $action->execute();
        } elseif (!$conditionResult && $this->falseAction) {
            $action = app($this->falseAction, ['data' => array_merge($this->data, $this->falseActionData)]);
            return $action->execute();
        }
        return ActionResult::success(['condition_result' => $conditionResult]);
    }
}
