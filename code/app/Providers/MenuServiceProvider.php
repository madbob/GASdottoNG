<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Auth;

class MenuServiceProvider extends ServiceProvider
{
    private function commonItems($user, &$menu)
    {
        $menu['<i class="bi-house"></i> ' . _i('Home')] = route('dashboard');
        $menu['<i class="bi-gear"></i> ' . $user->printableName()] = route('profile');
    }

    private function accessUsers($user, $gas, &$menu)
    {
        if ($user->can('users.admin', $gas) || $user->can('users.view', $gas)) {
            $menu['<i class="bi-people"></i> ' . _i('Utenti')] = route('users.index');
        }
    }

    private function accessSuppliers($user, $gas, &$menu)
    {
        if ($user->can('supplier.view', $gas) || $user->can('supplier.add', $gas) || $user->can('supplier.modify', null)) {
            $menu['<i class="bi-tags"></i> ' . _i('Fornitori')] = route('suppliers.index');
        }
    }

    private function accessOrders($user, $gas, &$menu)
    {
        if ($user->can('supplier.orders', null) || $user->can('supplier.shippings', null) || $user->can('order.view', $gas)) {
            $menu['<i class="bi-list-task"></i> ' . _i('Ordini')] = route('orders.index');
        }
    }

    private function accessBookings($user, $gas, &$menu)
    {
        if ($user->can('supplier.book', null)) {
            $menu['<i class="bi-bookmark"></i> ' . _i('Prenotazioni')] = route('bookings.index');
        }
    }

    private function accessAccounting($user, $gas, &$menu)
    {
        if ($user->can('movements.view', $gas) || $user->can('movements.admin', $gas)) {
            $menu['<i class="bi-piggy-bank"></i> ' . _i('Contabilità')] = route('movements.index');
        }
    }

    private function accessStatistics($user, $gas, &$menu)
    {
        if ($user->can('gas.statistics', $gas)) {
            $menu['<i class="bi-graph-up"></i> ' . _i('Statistiche')] = route('stats.index');
        }
    }

    private function accessNotifications($user, $gas, &$menu)
    {
        $menu['<i class="bi-bell"></i> ' . _i('Notifiche')] = route('notifications.index');
    }

    private function accessConfigs($user, $gas, &$menu)
    {
        if ($user->can('gas.config', $gas)) {
            $menu['<bi class="bi-tools"></i> ' . _i('Configurazioni')] = route('gas.edit', $gas->id);
        }
    }

    private function accessMultigas($user, $gas, &$menu)
    {
        if ($user->can('gas.multi', $gas)) {
            $menu['<i class="bi-globe"></i> ' . _i('Multi-GAS')] = route('multigas.index');
        }
    }

    private function startMenu($view)
    {
        $menu = [];
        $user = Auth::user();
        $gas = $user->gas;

        $this->commonItems($user, $menu);

        if (is_null($user->suspended_at)) {
            $this->accessUsers($user, $gas, $menu);
            $this->accessSuppliers($user, $gas, $menu);
            $this->accessOrders($user, $gas, $menu);
            $this->accessBookings($user, $gas, $menu);
            $this->accessAccounting($user, $gas, $menu);
            $this->accessStatistics($user, $gas, $menu);
            $this->accessNotifications($user, $gas, $menu);
            $this->accessConfigs($user, $gas, $menu);
            $this->accessMultigas($user, $gas, $menu);
        }

        $view->with('menu', $menu);
    }

    private function endMenu($view)
    {
        $view->with('end_menu', [
            '<i class="bi-megaphone-fill"></i>' => ['attributes' => [
                'data-bs-toggle' => "modal",
                'data-bs-target' => "#feedback-modal",
            ]],

            '<i class="bi-power"></i>' => ['url' => route('logout'), 'attributes' => [
                'onclick' => "event.preventDefault(); document.getElementById('logout-form').submit();",
            ]],
        ]);
    }

    public function boot()
    {
        view()->composer(['pages.*', 'auth.*'], function ($view) {
            if (Auth::check()) {
                $this->startMenu($view);
                $this->endMenu($view);
            }
            else {
                $view->with('menu', []);
                $view->with('end_menu', []);
            }
        });
    }

    public function register()
    {
    }
}
