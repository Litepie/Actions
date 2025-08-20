<?php

use Litepie\Actions\Manager\ActionManager;
use Litepie\Actions\Batch\ActionBatch;
use Litepie\Actions\Pipeline\ActionPipeline;
use Litepie\Actions\Conditional\ConditionalAction;

if (!function_exists('action')) {
    function action(string $name, array $data = []) {
        return app(ActionManager::class)->execute($name, $data);
    }
}

if (!function_exists('action_batch')) {
    function action_batch(): ActionBatch {
        return ActionBatch::make();
    }
}

if (!function_exists('action_pipeline')) {
    function action_pipeline(): ActionPipeline {
        return ActionPipeline::make();
    }
}

if (!function_exists('action_when')) {
    function action_when(Closure $condition): ConditionalAction {
        return ConditionalAction::make()->when($condition);
    }
}
