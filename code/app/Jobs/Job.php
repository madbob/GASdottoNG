<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App;

use App\Order;

abstract class Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $hub;
    public $gas_id;

    public function __construct()
    {
        $this->hub = '';
        $this->gas_id = App::make('GlobalScopeHub')->getGas();
    }

    public function handle()
    {
        /*
            Per scrupolo, attendo sempre un poco di tempo prima di eseguire il
            Job. Onde evitare race conditions sul database o cose del genere.
        */
        sleep(5);

        $this->hub = App::make('GlobalScopeHub');
        $this->hub->setGas($this->gas_id);
        $this->realHandle();
    }

    abstract protected function realHandle();
}
