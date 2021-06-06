<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Auth;
use Menu;

class MenuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        view()->composer('pages.*', function ($view) {
            $menu = null;

            if (Auth::check()) {
                $user = Auth::user();
                $gas = $user->gas;

                Menu::make('MainMenu', function($menu) use ($user, $gas) {
                    $menu->add('<span class="glyphicon glyphicon-home" aria-hidden="true"></span> ' . _i('Home'), 'dashboard');
                    $menu->add('<span class="glyphicon glyphicon-cog" aria-hidden="true"></span> ' . $user->printableName(), 'users/profile');

                    if (is_null($user->suspended_at)) {
                        if ($user->can('users.admin', $gas) || $user->can('users.view', $gas)) {
                            $menu->add('<span class="glyphicon glyphicon-user" aria-hidden="true"></span> ' . _i('Utenti'), 'users');
                        }

                        if ($user->can('supplier.view', $gas) || $user->can('supplier.add', $gas) || $user->can('supplier.modify', null)) {
                            $menu->add('<span class="glyphicon glyphicon-tags" aria-hidden="true"></span> ' . _i('Fornitori'), 'suppliers');
                        }

                        if ($user->can('supplier.orders', null) || $user->can('supplier.shippings', null) || $user->can('order.view', $gas)) {
                            $menu->add('<span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> ' . _i('Ordini'), 'orders');
                        }

                        if ($user->can('supplier.book', null)) {
                            $menu->add('<span class="glyphicon glyphicon-bookmark" aria-hidden="true"></span> ' . _i('Prenotazioni'), 'bookings');
                        }

                        if ($user->can('movements.view', $gas) || $user->can('movements.admin', $gas)) {
                            $menu->add('<span class="glyphicon glyphicon-euro" aria-hidden="true"></span> ' . _i('Contabilità'), 'movements');
                        }

                        if ($user->can('gas.statistics', $gas)) {
                            $menu->add('<span class="glyphicon glyphicon-stats" aria-hidden="true"></span> ' . _i('Statistiche'), 'stats');
                        }

                        $menu->add('<span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span> ' . _i('Notifiche'), 'notifications');

                        if ($user->can('gas.config', $gas)) {
                            $menu->add('<span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> ' . _i('Configurazioni'), 'gas/' . $gas->id . '/edit');
                        }

                        if ($user->can('gas.multi', $gas)) {
                            $menu->add('<span class="glyphicon glyphicon-globe" aria-hidden="true"></span> ' . _i('Multi-GAS'), 'multigas');
                        }
                    }
                });
            }
        });
    }

    public function register()
    {
    }
}
