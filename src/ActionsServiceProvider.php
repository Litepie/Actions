<?php

namespace Litepie\Actions;

use Illuminate\Support\ServiceProvider;
use Litepie\Actions\Manager\ActionManager;

class ActionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/actions.php', 'actions');
        
        $this->app->singleton(ActionManager::class, function ($app) {
            return new ActionManager($app);
        });
        
        $this->app->alias(ActionManager::class, 'action-manager');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/actions.php' => config_path('actions.php'),
            ], 'actions-config');
            
            $this->commands([
                Console\MakeActionCommand::class,
            ]);
        }
    }
}
