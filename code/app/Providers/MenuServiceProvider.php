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

				$menu->add('dashboard', 'Home');

				if ($gas->userCan('users.admin|users.view'))
					$menu->add('users', 'Utenti');

				$menu->add('suppliers', 'Fornitori');
				$menu->add('orders', 'Ordini');

				if ($gas->userCan('movements.view|movements.admin'))
					$menu->add('movements', 'Contabilità');

				if ($gas->userCan('gas.config'))
					$menu->add('gas/' . $gas->id . '/edit', 'Configurazioni');

				if ($gas->userCan('notifications.admin'))
					$menu->add('notifications', 'Notifiche');

				$menu->addClass('nav nav-pills nav-stacked')->getItemsByContentType(Menu\Items\Contents\Link::class)->map(function($item) {
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
