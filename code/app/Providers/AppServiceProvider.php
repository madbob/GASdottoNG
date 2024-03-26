<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobRetryRequested;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueTransportFactory;
use Symfony\Component\Mailer\Bridge\Scaleway\Transport\ScalewayTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Dotenv\Dotenv;

use App\Category;

class AppServiceProvider extends ServiceProvider
{
    /*
        Qui vengono inizializzate configurazioni speciali per i driver mail
    */
    private function initMailing()
    {
        $mailer = env('MAIL_MAILER');

		if ($mailer == 'sendinblue') {
			Mail::extend('sendinblue', function () {
	            return (new SendinblueTransportFactory)->create(
	                new Dsn('sendinblue+api', 'default', config('services.sendinblue.key'))
	            );
	        });
		}
        else if ($mailer == 'scaleway') {
			Mail::extend('scaleway', function () {
	            return (new ScalewayTransportFactory)->create(
	                new Dsn('scaleway+api', 'default', config('mail.mailers.scaleway.username'), config('mail.mailers.scaleway.password'))
	            );
	        });
		}
    }

    protected function getEventPayload($event): ?array
    {
        return match (true) {
            $event instanceof JobProcessing => $event->job->payload(),
            $event instanceof JobRetryRequested => $event->payload(),
            default => null,
        };
    }

    protected function enforceInstance($event)
    {
        $payload = $this->getEventPayload($event);

        $env_file = $payload['env_file'] ?? null;
        if ($env_file) {
            /*
                Qui viene riletto l'.env dell'istanza desiderata, vengono
                ricalcolate le configurazioni e viene resettata la connessione
                al DB. Tutto questo perché i job vengono eseguiti nello stesso
                ambiente condiviso, e di volta in volta occorre abilitare
                daccapo l'istanza corrente
            */
            $start_connection = env('DB_CONNECTION');
            app()->loadEnvironmentFrom($env_file);
            Dotenv::create(Env::getRepository(), app()->environmentPath(), app()->environmentFile())->load();
            (new LoadConfiguration())->bootstrap(app());
            app('db')->purge($start_connection);

            URL::forceRootUrl(env('APP_URL'));
        }

        $gas_id = $payload['gas_id'] ?? null;
        if ($gas_id) {
            app()->make('GlobalScopeHub')->setGas($gas_id);
        }
    }

    /*
        Questo è per predisporre la manipolazione dinamica dei job immessi nella
        queue, per iniettare dati che aiutano a determinare l'istanza ed il GAS
        di riferimento quando il job stesso viene eseguito
    */
    private function initQueues()
    {
        app('queue')->createPayloadUsing(function ($connectionName, $queue, $payload) {
            $ret = [
                'gas_id' => app()->make('GlobalScopeHub')->getGas(),
            ];

            if (global_multi_installation()) {
                $ret['env_file'] = env_file();
            }

            return $ret;
        });

        app('events')->listen(JobProcessing::class, function (JobProcessing $event) {
            $this->enforceInstance($event);
        });

        app('events')->listen(JobRetryRequested::class, function (JobRetryRequested $event) {
            $this->enforceInstance($event);
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

    public function boot()
    {
        Schema::defaultStringLength(191);
        // Model::preventLazyLoading();

        $this->initMailing();
        $this->initQueues();
        $this->initCollectionMacros();
    }

    public function register()
    {
    }
}
