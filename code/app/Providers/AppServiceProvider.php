<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Measure;
use App\Category;
use App\User;
use App\Role;
use App\Delivery;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /*
            Qui faccio in modo di avere sempre dei default.
            Serve solo per sistemare le istanze già esistenti in cui questi
            valori sono stato rimossi prima del blocco a livello di
            amministrazione, codice da rimuovere tra qualche tempo
            Addì: 09/01/2018
        */
        if (Measure::find('non-specificato') == null) {
            $measure = new Measure();
            $measure->name = _i('Non Specificato');
            $measure->save();
        }
        if (Category::find('non-specificato') == null) {
            $category = new Category();
            $category->name = _i('Non Specificato');
            $category->save();
        }

        User::created(function($user) {
            $default_roles = Role::where('always', true)->get();
            foreach($default_roles as $dr) {
                $user->addRole($dr, $user->gas);
            }

            $fallback_delivery = Delivery::where('default', true)->first();
            if ($fallback_delivery != null) {
                $user->preferred_delivery_id = $fallback_delivery->id;
                $user->save();
            }
        });
    }

    public function register()
    {
    }
}
