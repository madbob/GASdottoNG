<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class ServicesProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        //
    }

    private function services(): array
    {
        /*
            Questo si suppone essere l'elenco di tutte le classi in
            app/Services: da tenere aggiornato!
        */
        return [
            \App\Services\BookingsService::class,
            \App\Services\CirclesService::class,
            \App\Services\DatesService::class,
            \App\Services\DynamicBookingsService::class,
            \App\Services\FastBookingsService::class,
            \App\Services\GroupsService::class,
            \App\Services\InvoicesService::class,
            \App\Services\ModifiersService::class,
            \App\Services\ModifierTypesService::class,
            \App\Services\MovementsService::class,
            \App\Services\MovementTypesService::class,
            \App\Services\MultiGasService::class,
            \App\Services\NotificationsService::class,
            \App\Services\OrdersService::class,
            \App\Services\ProductsService::class,
            \App\Services\ReceiptsService::class,
            \App\Services\RolesService::class,
            \App\Services\SuppliersService::class,
            \App\Services\UsersService::class,
            \App\Services\VariantsService::class,
            \App\Services\VatRatesService::class,
        ];
    }

    public function register(): void
    {
        $classes = $this->services();

        foreach($classes as $class) {
            $this->app->singleton(class_basename($class), function ($app) use ($class) {
                return new $class();
            });
        }
    }

    public function provides(): array
    {
        $classes = $this->services();
        $ret = [];

        foreach($classes as $class) {
            $ret[] = class_basename($class);
        }

        return $ret;
    }
}
