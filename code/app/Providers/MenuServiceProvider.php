<?php

/*
    Da qui viene generato il menu primario, in funzione dei permessi abilitati
    per l'utente.
    Ad alcune voci viene assegnato un ID, che viene usato per generare il tour
    di onboarding in UsersController::startTour()
*/

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Auth;

class MenuServiceProvider extends ServiceProvider
{
    private function commonItems($user, &$menu)
    {
        $menu['<i class="bi-house"></i> ' . __('texts.generic.home')] = [
            'url' => route('dashboard'),
        ];

        $menu['<i class="bi-gear"></i> ' . $user->printableName()] = [
            'url' => route('profile'),
            'attributes' => ['id' => 'menu_profile'],
        ];
    }

    private function accessUsers($user, $gas, &$menu)
    {
        if ($user->can('users.admin', $gas) || $user->can('users.view', $gas)) {
            $menu['<i class="bi-people"></i> ' . __('texts.user.all')] = [
                'url' => route('users.index'),
                'attributes' => ['id' => 'menu_users'],
            ];
        }
    }

    private function accessSuppliers($user, $gas, &$menu)
    {
        if ($user->can('supplier.view', $gas) || $user->can('supplier.add', $gas) || $user->can('supplier.modify', null)) {
            $menu['<i class="bi-tags"></i> ' . __('texts.supplier.all')] = [
                'url' => route('suppliers.index'),
                'attributes' => ['id' => 'menu_suppliers'],
            ];
        }
    }

    private function accessOrders($user, $gas, &$menu)
    {
        if ($user->can('supplier.orders', null) || $user->can('supplier.shippings', null) || $user->can('order.view', $gas)) {
            $menu['<i class="bi-list-task"></i> ' . __('texts.orders.all')] = [
                'url' => route('orders.index'),
                'attributes' => ['id' => 'menu_orders'],
            ];
        }
    }

    private function accessBookings($user, $gas, &$menu)
    {
        if ($user->can('supplier.book', null)) {
            $menu['<i class="bi-bookmark"></i> ' . __('texts.generic.menu.bookings')] = [
                'url' => route('bookings.index'),
                'attributes' => ['id' => 'menu_bookings'],
            ];
        }
    }

    private function accessAccounting($user, $gas, &$menu)
    {
        if ($user->can('movements.view', $gas) || $user->can('movements.admin', $gas) || $user->can('supplier.movements', null) || $user->can('supplier.invoices', null)) {
            $menu['<i class="bi-piggy-bank"></i> ' . __('texts.generic.menu.accounting')] = [
                'url' => route('movements.index'),
                'attributes' => ['id' => 'menu_accouting'],
            ];
        }
    }

    private function accessStatistics($user, $gas, &$menu)
    {
        if ($user->can('gas.statistics', $gas)) {
            $menu['<i class="bi-graph-up"></i> ' . __('texts.generic.menu.stats')] = route('stats.index');
        }
    }

    private function accessNotifications($user, $gas, &$menu)
    {
        $menu['<i class="bi-bell"></i> ' . __('texts.generic.menu.notifications')] = route('notifications.index');
    }

    private function accessConfigs($user, $gas, &$menu)
    {
        if ($user->can('gas.config', $gas)) {
            $menu['<bi class="bi-tools"></i> ' . __('texts.generic.menu.configs')] = [
                'url' => route('gas.edit', $gas->id),
                'attributes' => ['id' => 'menu_config'],
            ];
        }
    }

    private function accessMultigas($user, $gas, &$menu)
    {
        if ($user->can('gas.multi', $gas)) {
            $menu['<i class="bi-globe"></i> ' . __('texts.generic.menu.multigas')] = route('multigas.index');
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
                'id' => 'menu_help',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#feedback-modal',
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
}
