<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Event;
use Auth;

class GraphicInitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        view()->composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $view->with('currentuser', $user);
                $view->with('currentgas', $user->gas);
            }

            /*
                Qui vengono inizializzati alcuni valori di
                default utilizzati per i "widgets" in
                views/commons. Utile per evitare di sparpagliare
                di if() i relativi files
            */

            $defaults = [
                'squeeze' => false,
                'labelsize' => 4,
                'fieldsize' => 8,
                'help_text' => '',
                'extras' => [],
                'prefix' => false,
                'postfix' => false,
                'triggering_modal' => false,
                'extra_class' => false,
                'extra_selection' => [],
                'multiple_select' => false,
            ];

            foreach ($defaults as $name => $value) {
                if ($view->offsetExists($name) == false) {
                    $view->with($name, $value);
                }
            }

            if ($view->offsetGet('squeeze') == true) {
                $view->with('fieldsize', 12);
            }
        });
    }

    public function register()
    {
    }
}
