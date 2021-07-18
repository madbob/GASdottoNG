<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Auth;

class MenuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        view()->composer(['pages.*', 'auth.*'], function ($view) {
            $menu = [];
            $end_menu = [];

            if (Auth::check()) {
                $user = Auth::user();
                $gas = $user->gas;

                $menu['<i class="bi-house"></i> ' . _i('Home')] = route('dashboard');
                $menu['<i class="bi-gear"></i> ' . $user->printableName()] = route('profile');

                if (is_null($user->suspended_at)) {
                    if ($user->can('users.admin', $gas) || $user->can('users.view', $gas)) {
                        $menu['<i class="bi-people"></i> ' . _i('Utenti')] = route('users.index');
                    }

                    if ($user->can('supplier.view', $gas) || $user->can('supplier.add', $gas) || $user->can('supplier.modify', null)) {
                        $menu['<i class="bi-tags"></i> ' . _i('Fornitori')] = route('suppliers.index');
                    }

                    if ($user->can('supplier.orders', null) || $user->can('supplier.shippings', null) || $user->can('order.view', $gas)) {
                        $menu['<i class="bi-list-task"></i> ' . _i('Ordini')] = route('orders.index');
                    }

                    if ($user->can('supplier.book', null)) {
                        $menu['<i class="bi-bookmark"></i> ' . _i('Prenotazioni')] = route('bookings.index');
                    }

                    if ($user->can('movements.view', $gas) || $user->can('movements.admin', $gas)) {
                        $menu['<i class="bi-piggy-bank"></i> ' . _i('Contabilità')] = route('movements.index');
                    }

                    if ($user->can('gas.statistics', $gas)) {
                        $menu['<i class="bi-graph-up"></i> ' . _i('Statistiche')] = route('stats.index');
                    }

                    $menu['<i class="bi-bell"></i> ' . _i('Notifiche')] = route('notifications.index');

                    if ($user->can('gas.config', $gas)) {
                        $menu['<bi class="bi-tools"></i> ' . _i('Configurazioni')] = route('gas.edit', $gas->id);
                    }

                    if ($user->can('gas.multi', $gas)) {
                        $menu['<i class="bi-globe"></i> ' . _i('Multi-GAS')] = route('multigas.index');
                    }
                }

                $end_menu['<i class="bi-megaphone-fill"></i>'] = ['attributes' => [
                    'data-bs-toggle' => "modal",
                    'data-bs-target' => "#feedback-modal",
                ]];

                $end_menu['<i class="bi-power"></i>'] = ['url' => route('logout'), 'attributes' => [
                    'onclick' => "event.preventDefault(); document.getElementById('logout-form').submit();",
                ]];
            }

            $view->with('menu', $menu);
            $view->with('end_menu', $end_menu);
        });
    }

    public function register()
    {
    }
}
