<?php

namespace Litepie\Actions\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $name, string $actionClass)
 * @method static \Litepie\Actions\ActionResult execute(string $name, array $data = [])
 * @method static \Illuminate\Support\Collection chain(array $actions)
 * @method static \Illuminate\Support\Collection parallel(array $actions)
 * @method static array getActions()
 * @method static \Illuminate\Support\Collection getHistory()
 * @method static void clearHistory()
 * @method static array getStatistics()
 */
class ActionManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Litepie\Actions\Manager\ActionManager::class;
    }
}
