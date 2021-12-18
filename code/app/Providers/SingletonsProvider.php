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
        $classes = classesInNamespace('App\\Singletons');

        foreach($classes as $class) {
            $this->app->singleton(class_basename($class), function ($app) use ($class) {
                return new $class();
            });
        }
    }
}
