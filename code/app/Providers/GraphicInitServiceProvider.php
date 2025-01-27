<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

use Auth;

class GraphicInitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        view()->composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                if ($view->offsetExists('currentuser') == false) {
                    $view->with('currentuser', $user);
                }

                if ($view->offsetExists('currentgas') == false) {
                    $view->with('currentgas', currentAbsoluteGas());
                }
            }

            /*
                Qui vengono inizializzati alcuni valori di
                default utilizzati per i "widgets" in
                views/commons. Utile per evitare di sparpagliare
                di if() i relativi files
            */

            $defaults = [
                'squeeze' => false,
                'help_popover' => '',
                'extras' => [],
                'extra_class' => false,
            ];

            foreach ($defaults as $name => $value) {
                if ($view->offsetExists($name) == false) {
                    $view->with($name, $value);
                }
            }
        });

        Paginator::useBootstrap();
    }
}
