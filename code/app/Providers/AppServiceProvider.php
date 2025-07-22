<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Mailer\Bridge\Scaleway\Transport\ScalewayTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

use App\Extensions\Translator;
use App\Category;

class AppServiceProvider extends ServiceProvider
{
    private function extendTranslations()
    {
        app()->extend('translator', function($command, $app) {
            $loader = $app->make('translation.loader');
            $locale = $app->getLocale();
            return new Translator($loader, $locale);
        });
    }

    /*
        Qui vengono inizializzate configurazioni speciali per i driver mail
    */
    private function initMailing()
    {
        Mail::extend('scaleway', function () {
            return (new ScalewayTransportFactory())->create(
                new Dsn('scaleway+api', 'default', config('mail.mailers.scaleway.username'), config('mail.mailers.scaleway.password'))
            );
        });
    }

    private function initCollectionMacros()
    {
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
                foreach ($collection as $variant) {
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

            if ($found === false) {
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
            return $this->sortBy(fn ($b) => $b->user->printableName());
        });

        /*
            Estrapola da una Collection il primo elemento il cui attributo $attr
            ha un valore "simile" a $value
        */
        Collection::macro('firstWhereAbout', function ($attr, $value) {
            $value = preg_replace('/[^a-zA-Z0-9]*/', '', mb_strtolower(trim($value)));

            /** @var Collection $this */
            return $this->first(function ($o, $k) use ($attr, $value) {
                $test = preg_replace('/[^a-zA-Z0-9]*/', '', mb_strtolower(trim($o->$attr)));

                return $test == $value;
            });
        });
    }

    public function boot()
    {
        Schema::defaultStringLength(191);
        // Model::preventLazyLoading();

        $this->extendTranslations();
        $this->initMailing();
        $this->initCollectionMacros();
    }
}
