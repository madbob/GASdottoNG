<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;

use Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Schema::defaultStringLength(191);
        // Model::preventLazyLoading();

		if (env('MAIL_MAILER') == 'sendinblue') {
			Mail::extend('sendinblue', function () {
	            return (new SendinblueTransportFactory)->create(
	                new Dsn('sendinblue+api', 'default', config('services.sendinblue.key'))
	            );
	        });
		}

		/*
			Questa va usata solo per una Collection di BookedProductVariant,
			come ad esempio la relazione variants() di BookedProduct
		*/
		Collection::macro('squashBookedVariant', function ($bookedvariant) {
			$collection = $this;
			$target_combo = $bookedvariant->variantsCombo();
			$found = false;

			foreach($collection as $variant) {
				$combo = $variant->variantsCombo();

				if ($combo->id == $target_combo->id) {
					$variant->quantity += $bookedvariant->quantity;
					$variant->delivered += $bookedvariant->delivered;
					$variant->final_price += $bookedvariant->final_price;

					$found = true;
					break;
				}
			}

			if ($found == false) {
				$collection->push($bookedvariant);
			}

			return $collection;
		});
    }

    public function register()
    {
    }
}
