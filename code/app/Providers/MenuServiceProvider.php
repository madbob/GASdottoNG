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

                $menu = Menu::handler('main');

                $menu->add('dashboard', '<span class="glyphicon glyphicon-home" aria-hidden="true"></span> ' . _i('Home'));
                $menu->add('users/profile', '<span class="glyphicon glyphicon-cog" aria-hidden="true"></span> ' . $user->printableName());

                if ($user->can('users.admin', $gas) || $user->can('users.view', $gas)) {
                    $menu->add('users', '<span class="glyphicon glyphicon-user" aria-hidden="true"></span> ' . _i('Utenti'));
                }

                if ($user->can('supplier.view', $gas) || $user->can('supplier.add', $gas) || $user->can('supplier.modify', null)) {
                    $menu->add('suppliers', '<span class="glyphicon glyphicon-tags" aria-hidden="true"></span> ' . _i('Fornitori'));
                }

                if ($user->can('supplier.orders', null) || $user->can('supplier.shippings', null)) {
                    $menu->add('orders', '<span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> ' . _i('Ordini'));
                }

                if ($user->can('supplier.book', null)) {
                    $menu->add('bookings', '<span class="glyphicon glyphicon-bookmark" aria-hidden="true"></span> ' . _i('Prenotazioni'));
                }

                if ($user->can('movements.view', $gas) || $user->can('movements.admin', $gas)) {
                    $menu->add('movements', '<span class="glyphicon glyphicon-euro" aria-hidden="true"></span> ' . _i('Contabilità'));
                }

                if ($user->can('gas.statistics', $gas)) {
                    $menu->add('stats', '<span class="glyphicon glyphicon-stats" aria-hidden="true"></span> ' . _i('Statistiche'));
                }

                $menu->add('notifications', '<span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span> ' . _i('Notifiche'));

                if ($user->can('gas.config', $gas)) {
                    $menu->add('gas/'.$gas->id.'/edit', '<span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> ' . _i('Configurazioni'));
                }

                if ($user->can('gas.multi', $gas)) {
                    $menu->add('multigas', '<span class="glyphicon glyphicon-globe" aria-hidden="true"></span> ' . _i('Multi-GAS'));
                }

                $menu->addClass('nav navbar-nav')->getItemsByContentType(Menu\Items\Contents\Link::class)->map(function ($item) {
                    if ($item->isActive()) {
                        $item->addClass('active');
                    }
                });
            }

            $view->with('menu', $menu);
        });
    }

    public function register()
    {
    }
}
