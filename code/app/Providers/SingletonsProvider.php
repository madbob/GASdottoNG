<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SingletonsProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        /*
            Questo si suppone essere l'elenco di tutte le classi in
            app/Singletons: da tenere aggiornato!
        */
        $classes = [
            \App\Singletons\AggregationSwitch::class,
            \App\Singletons\GlobalScopeHub::class,
            \App\Singletons\LogHarvester::class,
            \App\Singletons\ModifierEngine::class,
            \App\Singletons\MovementsHub::class,
            \App\Singletons\OrderNumbersDispatcher::class,
            \App\Singletons\RemoteRepository::class,
            \App\Singletons\TempCache::class,
        ];

        foreach($classes as $class) {
            $this->app->singleton(class_basename($class), function ($app) use ($class) {
                return new $class();
            });
        }
    }
}
