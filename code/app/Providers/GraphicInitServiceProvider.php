<?php

namespace app\Providers;

use Illuminate\Support\ServiceProvider;
use Event;
use Auth;
use Theme;

class GraphicInitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Theme::setLayout('app');

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

        setlocale(LC_TIME, 'it_IT.UTF-8');

        Event::listen('eloquent.creating*', function ($model) {
            if (array_search('App\SluggableID', class_uses($model))) {
                if (empty($model->id)) {
                    $model->id = $model->getSlugID();
                }
            }
        });
    }

    public function register()
    {
    }
}
