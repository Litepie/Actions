<?php

namespace Litepie\Actions\Batch;

use Illuminate\Support\Collection;
use Litepie\Actions\ActionResult;
use Litepie\Actions\Contracts\ActionContract;

class ActionBatch
{
    protected Collection $actions;
    protected Collection $results;
    protected bool $stopOnFailure = false;
    protected int $concurrency = 1;

    public function __construct()
    {
        $this->actions = collect();
        $this->results = collect();
    }

    public static function make(): static
    {
        return new static();
    }

    public function add(ActionContract $action): static
    {
        $this->actions->push($action);
        return $this;
    }

    public function stopOnFailure(bool $stop = true): static
    {
        $this->stopOnFailure = $stop;
        return $this;
    }

    public function concurrent(int $concurrency): static
    {
        $this->concurrency = $concurrency;
        return $this;
    }

    public function execute(): Collection
    {
        if ($this->concurrency > 1) {
            return $this->executeConcurrent();
        }
        return $this->executeSequential();
    }

    protected function executeSequential(): Collection
    {
        foreach ($this->actions as $action) {
            $result = $action->execute();
            $this->results->push($result);
            if ($this->stopOnFailure && $result->isFailure()) {
                break;
            }
        }
        return $this->results;
    }

    protected function executeConcurrent(): Collection
    {
        $chunks = $this->actions->chunk($this->concurrency);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $action) {
                $result = $action->execute();
                $this->results->push($result);
                if ($this->stopOnFailure && $result->isFailure()) {
                    return $this->results;
                }
            }
        }
        return $this->results;
    }

    public function getResults(): Collection
    {
        return $this->results;
    }

    public function getSuccessful(): Collection
    {
        return $this->results->filter(fn(ActionResult $result) => $result->isSuccess());
    }

    public function getFailed(): Collection
    {
        return $this->results->filter(fn(ActionResult $result) => $result->isFailure());
    }

    public function isAllSuccessful(): bool
    {
        return $this->results->every(fn(ActionResult $result) => $result->isSuccess());
    }
}
