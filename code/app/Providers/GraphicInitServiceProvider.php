<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
		});

		setlocale(LC_TIME, 'it_IT.UTF-8');
	}

	public function register()
	{
		//
	}
}
