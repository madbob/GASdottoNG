<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Observers\MovementObserver;
use App\Observers\UserObserver;
use App\Observers\SupplierObserver;
use App\Observers\OrderObserver;
use App\Observers\BookedProductObserver;
use App\Observers\InvoiceObserver;
use App\Observers\ModifierObserver;
use App\Observers\ContactObserver;
use App\Observers\VariantObserver;
use App\Observers\AttachmentObserver;
use App\Observers\ConfigObserver;

use App\Movement;
use App\User;
use App\Supplier;
use App\Order;
use App\BookedProduct;
use App\Booking;
use App\Invoice;
use App\Modifier;
use App\Contact;
use App\Variant;
use App\Attachment;
use App\Config;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'App\Events\SluggableCreating' => [
            'App\Listeners\SlugModel',
        ],
        'App\Events\AttachableToGas' => [
            'App\Listeners\AttachToGas',
        ],
        'App\Events\BookingDelivered' => [
            'App\Listeners\DeliverBooking',
        ],
        'App\Events\VariantChanged' => [
            'App\Listeners\ReviewProductVariantCombos',
        ],
        'Illuminate\Log\Events\MessageLogged' => [
            'App\Listeners\HarvestLogs',
        ],
        'Illuminate\Notifications\Events\NotificationSent' => [
            'App\Listeners\AfterNotification',
        ],
		'Illuminate\Mail\Events\MessageSending' => [
			'App\Listeners\CustomMailTag',
		],
    ];

    public function boot()
    {
        parent::boot();

		Movement::observe(MovementObserver::class);
        User::observe(UserObserver::class);
        Supplier::observe(SupplierObserver::class);
        Order::observe(OrderObserver::class);
        BookedProduct::observe(BookedProductObserver::class);
		Invoice::observe(InvoiceObserver::class);
        Modifier::observe(ModifierObserver::class);
        Contact::observe(ContactObserver::class);
        Variant::observe(VariantObserver::class);
        Attachment::observe(AttachmentObserver::class);
        Config::observe(ConfigObserver::class);
    }
}
