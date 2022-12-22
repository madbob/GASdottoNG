<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;

use Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

use App\Observers\MovementObserver;
use App\Observers\UserObserver;
use App\Observers\SupplierObserver;
use App\Observers\OrderObserver;
use App\Observers\BookingObserver;
use App\Observers\ModifierObserver;
use App\Observers\ContactObserver;
use App\Observers\VariantObserver;
use App\Observers\ConfigObserver;

use App\Movement;
use App\User;
use App\Supplier;
use App\Order;
use App\Booking;
use App\Modifier;
use App\Contact;
use App\Variant;
use App\Config;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Schema::defaultStringLength(191);
        // Model::preventLazyLoading();

        Movement::observe(MovementObserver::class);
        User::observe(UserObserver::class);
        Supplier::observe(SupplierObserver::class);
        Order::observe(OrderObserver::class);
        Booking::observe(BookingObserver::class);
        Modifier::observe(ModifierObserver::class);
        Contact::observe(ContactObserver::class);
        Variant::observe(VariantObserver::class);
        Config::observe(ConfigObserver::class);

		if (env('MAIL_MAILER') == 'sendinblue') {
			Mail::extend('sendinblue', function () {
	            return (new SendinblueTransportFactory)->create(
	                new Dsn('sendinblue+api', 'default', config('services.sendinblue.key'))
	            );
	        });
		}
    }

    public function register()
    {
    }
}
