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

				$menu->add('dashboard', '<span class="glyphicon glyphicon-home" aria-hidden="true"></span> Home');

				if ($gas->userCan('users.admin|users.view'))
					$menu->add('users', '<span class="glyphicon glyphicon-user" aria-hidden="true"></span> Utenti');

				$menu->add('suppliers', '<span class="glyphicon glyphicon-tags" aria-hidden="true"></span> Fornitori');
				$menu->add('orders', '<span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> Ordini');

				if ($gas->userCan('movements.view|movements.admin'))
					$menu->add('movements', '<span class="glyphicon glyphicon-piggy-bank" aria-hidden="true"></span> Contabilità');

				if ($gas->userCan('gas.statistics'))
					$menu->add('stats', '<span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> Statistiche');

				if ($gas->userCan('gas.config'))
					$menu->add('gas/' . $gas->id . '/edit', '<span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> Configurazioni');

				if ($gas->userCan('notifications.admin'))
					$menu->add('notifications', '<span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span> Notifiche');

				$menu->addClass('nav navbar-nav')->getItemsByContentType(Menu\Items\Contents\Link::class)->map(function($item) {
					if ($item->isActive())  {
						$item->addClass('active');
					}
				});
			}

			$view->with('menu', $menu);
		});
	}

	public function register()
	{
		//
	}
}
