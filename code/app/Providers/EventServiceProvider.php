<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'App\Events\SluggableCreating' => [
            'App\Listeners\SlugModel',
        ],
        'App\Events\AttachableToGas' => [
            'App\Listeners\AttachToGas',
        ],
        'App\Events\SupplierDeleting' => [
            'App\Listeners\DetachSupplierRole',
        ],
        'Illuminate\Log\Events\MessageLogged' => [
            'App\Listeners\HarvestLogs',
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
