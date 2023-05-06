<?php

namespace Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

use Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $baseUrl = 'http://localhost';
    protected $services = null;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:refresh');
        Artisan::call('db:seed', ['--force' => true, '--class' => 'CurrenciesSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'MovementTypesSeeder']);
        Artisan::call('db:seed', ['--force' => true, '--class' => 'ModifierTypesSeeder']);

        /*
            Questo serve a generare le stringhe delle date in italiano, per la
            corretta formattazione da parte di printableDate()
        */
        setlocale(LC_TIME, 'it_IT.UTF-8');

        $this->services = [
            'users' => new \App\Services\UsersService(),
            'roles' => new \App\Services\RolesService(),
            'movement_types' => new \App\Services\MovementTypesService(),
            'movements' => new \App\Services\MovementsService(),
            'vat_rates' => new \App\Services\VatRatesService(),
            'suppliers' => new \App\Services\SuppliersService(),
            'products' => new \App\Services\ProductsService(),
            'variants' => new \App\Services\VariantsService(),
            'orders' => new \App\Services\OrdersService(),
            'bookings' => new \App\Services\BookingsService(),
            'dynamic_bookings' => new \App\Services\DynamicBookingsService(),
            'fast_bookings' => new \App\Services\FastBookingsService(),
            'dates' => new \App\Services\DatesService(),
            'invoices' => new \App\Services\InvoicesService(),
            'modifier_types' => new \App\Services\ModifierTypesService(),
            'modifiers' => new \App\Services\ModifiersService(),
            'multigas' => new \App\Services\MultiGasService(),
            'notifications' => new \App\Services\NotificationsService(),
        ];

        $this->gas = \App\Gas::factory()->create();

        /*
            Nota: alcuni comportamenti sono influenzati dalla presenza di almeno
            un utente che abbia permessi di amministrazione dei movimenti
            contabili. Qui lo considero sempre presente, che è il caso in
            assoluto più comune
            Cfr. DeliverBooking::handle()
        */
        $this->userAdmin = $this->createRoleAndUser($this->gas, 'gas.config,movements.admin');
    }

    public function enabledQueryDump()
    {
        \DB::listen(function ($sql, $bindings) {
            var_dump($sql);
            var_dump($bindings);
        });
    }

    public function disableQueryDump()
    {
        \DB::getEventDispatcher()->forget('illuminate.query');
    }

    public function tearDown(): void
    {
        $this->disableQueryDump();
        parent::tearDown();
    }

    /*
        Per creare un ruolo coi dati permessi ed assegnargli un utente
    */
    protected function createRoleAndUser($gas, $permissions, $target = null)
    {
        $role = \App\Role::factory()->create([
            'actions' => $permissions
        ]);

        $user = \App\User::factory()->create(['gas_id' => $gas->id]);
        $user->addRole($role->id, $target ?: $gas);

        return $user;
    }

    /*
        Per creare un utente "amico" per un dato utente
    */
    protected function createFriend($master)
    {
        $friends_role = \App\Role::factory()->create(['actions' => 'users.subusers']);
        $master->addRole($friends_role->id, $this->gas);

        $this->actingAs($master);
        $friend = $this->services['users']->storeFriend(array(
            'username' => 'test friend user',
            'firstname' => 'mario',
            'lastname' => 'rossi',
            'password' => 'password'
        ));

        $booking_role = \App\Role::factory()->create(['actions' => 'supplier.book']);
        $friend->addRole($booking_role->id, $this->gas);

        return $friend;
    }

    /*
        Per creare una variante
    */
    protected function createVariant($product)
    {
        return $this->services['variants']->store([
            'product_id' => $product->id,
            'name' => 'Colore',
            'id' => ['', '', ''],
            'value' => ['Rosso', 'Verde', 'Blu'],
        ]);
    }

    /*
        Per predisporre il minimo essenziale per fare delle prenotazioni.
        Ovvero: un ordine
    */
    protected function initOrder($other_order)
    {
        $category = \App\Category::factory()->create();
        $measure = \App\Measure::factory()->create();

        $this->actingAs($this->userAdmin);
        $supplier = \App\Supplier::factory()->create();

        $this->userReferrer = $this->createRoleAndUser($this->gas, 'supplier.modify,supplier.orders', $supplier);
        $this->userWithShippingPerms = $this->createRoleAndUser($this->gas, 'supplier.shippings', $supplier);

        $products = \App\Product::factory()->count(10)->create([
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'measure_id' => $measure->id
        ]);

        $this->actingAs($this->userReferrer);
        $order = \App\Order::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        if ($other_order != null) {
            $order->aggregate_id = $other_order->aggregate_id;
            $order->save();
        }

        return $order;
    }

    protected function populateOrder($order)
    {
        $this->booking_role = \App\Role::factory()->create(['actions' => 'supplier.book']);

        $this->users = \App\User::factory()->count(5)->create(['gas_id' => $this->gas->id]);
        foreach($this->users as $user) {
            $user->addRole($this->booking_role->id, $this->gas);
        }

        foreach($this->users as $user) {
            $this->actingAs($user);
            list($data, $booked_count, $total) = $this->randomQuantities($order->products);
            $data['action'] = 'booked';
            $this->services['bookings']->bookingUpdate($data, $order->aggregate, $user, false);
        }
    }

    protected function randomQuantities($products)
    {
        $data = [];
        $booked_count = 0;
        $total = 0;

        foreach($products as $product) {
            $q = rand(0, 20);
            $data[$product->id] = $q;

            if ($q != 0) {
                $booked_count++;
                $total += $product->price * $q;
            }
        }

        return [$data, $booked_count, $total];
    }

    /*
        Per unire due array generati con randomQuantities() - tendenzialmente
        usato per testare le prenotazioni in presenza di amici
    */
    protected function mergeBookingQuantity($master, $friend)
    {
        $data = [];

        foreach($master as $product => $quantity) {
            if (is_numeric($quantity) == false) {
                continue;
            }

            if (isset($friend[$product])) {
                $quantity += $friend[$product];
            }

            $data[$product] = $quantity;
        }

        foreach($friend as $product => $quantity) {
            if (isset($data[$product]) == false) {
                $data[$product] = $quantity;
            }
        }

        return $data;
    }

	protected function updateAndFetch($data, $order, $user, $deliver)
    {
        $this->services['bookings']->bookingUpdate($data, $order->aggregate, $user, $deliver);
        return \App\Booking::where('user_id', $user->id)->where('order_id', $order->id)->first();
    }

    /*
        Normalmente, la cache dei modelli funziona solo all'interno di ogni
        singola richiesta. Questa funzione è per forzare un flush tra una
        macro-operazione e l'altra, appunto per simulare richieste diverse che
        arrivano in sequenza (che all'interno di un test possono svolgersi anche
        nella stessa funzione, e dunque attingerebbero sempre agli stessi
        modelli anziché quelli freschi e modificati pescati dal DB)
    */
    protected function nextRound()
    {
        Artisan::call('modelCache:clear');
    }
}
