<?php

namespace IonBazan\Laravel\ContainerDebug;

use Illuminate\Support\ServiceProvider as BaseProviderAlias;
use IonBazan\Laravel\ContainerDebug\Command\ContainerDebugCommand;

class ServiceProvider extends BaseProviderAlias
{
    /**
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(ContainerDebugCommand::class);
        }
    }
}
