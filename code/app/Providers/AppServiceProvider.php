<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;

use Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

use App\Category;

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
			/** @var Collection $this */
			$collection = $this;
			$target_combo = $bookedvariant->variantsCombo();
			$found = false;

            if ($bookedvariant->product->product->canAggregateQuantities()) {
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
            }

			if ($found == false) {
				$collection->push($bookedvariant);
			}

			return $collection;
		});

        /*
            Questa va usata su Collection di Product, per estrapolare in un
            colpo solo tutte le categorie rilevanti
        */
        Collection::macro('getProductsCategories', function () {
            /** @var Collection $this */
            $categories = $this->pluck('category_id')->toArray();
            $categories = array_unique($categories);
            return Category::whereIn('id', $categories)->orderBy('name', 'asc')->get();
        });

        /*
            Questa va usata su Collection di Booking, per ordinarle in base al
            nome dell'utente
        */
        Collection::macro('sortByUserName', function () {
            /** @var Collection $this */
            return $this->sortBy(fn($b) => $b->user->printableName());
        });

        /*
            Estrapola da una Collection il primo elemento il cui attributo $attr
            ha un valore "simile" a $value
        */
        Collection::macro('firstWhereAbout', function ($attr, $value) {
            $value = preg_replace('/[^a-zA-Z0-9]*/', '', mb_strtolower(trim($value)));

            /** @var Collection $this */
            return $this->first(function($o, $k) use ($attr, $value) {
                $test = preg_replace('/[^a-zA-Z0-9]*/', '', mb_strtolower(trim($o->$attr)));
                return $test == $value;
            });
        });
    }

    public function register()
    {
    }
}
