<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\URL;

use Dotenv\Dotenv;

use App\Order;

abstract class Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $hub;
    public $gas_id;
    public $env_file;

    public function __construct()
    {
        $this->hub = '';
        $this->gas_id = app()->make('GlobalScopeHub')->getGas();
        $this->env_file = env_file();
    }

    public function handle()
    {
        /*
            Per scrupolo, attendo sempre un poco di tempo prima di eseguire il
            Job. Onde evitare race conditions sul database o cose del genere.
        */
        sleep(2);

        if (env('GASDOTTO_NET', false) == true) {
            /*
                Qui viene riletto l'.env dell'istanza desiderata, vengono
                ricalcolate le configurazioni e viene resettata la connessione al
                DB. Tutto questo perché i job vengono eseguiti nello stesso ambiente
                condiviso, e di volta in volta occorre abilitare daccapo l'istanza
                corrente
            */
            $start_connection = env('DB_CONNECTION');
            app()->loadEnvironmentFrom($this->env_file);
            Dotenv::create(Env::getRepository(), app()->environmentPath(), app()->environmentFile())->load();
            (new LoadConfiguration())->bootstrap(app());
            app('db')->purge($start_connection);

            URL::forceRootUrl(env('APP_URL'));
        }

        $this->hub = app()->make('GlobalScopeHub');
        $this->hub->setGas($this->gas_id);
        $this->realHandle();
    }

    abstract protected function realHandle();
}
