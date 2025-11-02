<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class SingletonsProvider extends ServiceProvider implements DeferrableProvider
{
    private function singletons(): array
    {
        /*
            Questo si suppone essere l'elenco di tutte le classi in
            app/Singletons: da tenere aggiornato!
        */
        return [
            \App\Singletons\AggregationSwitch::class,
            \App\Singletons\AllBookings::class,
            \App\Singletons\GlobalScopeHub::class,
            \App\Singletons\LogHarvester::class,
            \App\Singletons\ModifierEngine::class,
            \App\Singletons\MovementsHub::class,
            \App\Singletons\OrderNumbersDispatcher::class,
            \App\Singletons\RemoteRepository::class,
            \App\Singletons\TempCache::class,
        ];
    }

    public function register(): void
    {
        $classes = $this->singletons();

        foreach ($classes as $class) {
            $this->app->singleton(class_basename($class), function () use ($class) {
                return new $class();
            });
        }
    }

    public function provides(): array
    {
        $classes = $this->singletons();
        $ret = [];

        foreach ($classes as $class) {
            $ret[] = class_basename($class);
        }

        return $ret;
    }
}
