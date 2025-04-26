<?php

namespace Tests\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

use App\Gas;
use App\User;
use App\Booking;
use App\VariantCombo;

class OrdersServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = $this->initOrder(null);
        $this->userWithNoPerms = User::factory()->create(['gas_id' => $this->gas->id]);
        $this->userWithBasePerms = $this->createRoleAndUser($this->gas, 'supplier.book');
    }

    /*
        Creazione Ordine con permessi sbagliati
    */
    public function test_fails_to_store()
    {
        $this->expectException(AuthException::class);

        $this->actingAs($this->userWithNoPerms);
        app()->make('OrdersService')->store([
            'supplier' => $this->order->supplier_id,
        ]);
    }

    private function storeMailableOrder()
    {
        $this->gas->setConfig('notify_all_new_orders', '1');
        $this->userWithNoPerms->addContact('email', fake()->email());
        $this->userWithBasePerms->addContact('email', fake()->email());

        $this->nextRound();

        $this->userReferrer = User::find($this->userReferrer->id);
        $this->gas = Gas::find($this->gas->id);
        app()->make('GlobalScopeHub')->setGas($this->gas);
        $this->actingAs($this->userReferrer);
        $this->assertEquals($this->gas->id, $this->userReferrer->gas->id);

        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));
        $shipping = date('Y-m-d', strtotime('+30 days'));

        $aggregate = app()->make('OrdersService')->store([
            'supplier' => $this->order->supplier_id,
            'comment' => 'Commento di prova',
            'start' => printableDate($start),
            'end' => printableDate($end),
            'shipping' => printableDate($shipping),
            'status' => 'open',
        ]);

        return [$aggregate, $start, $end, $shipping];
    }

    /*
        Creazione Ordine
    */
    public function test_store()
    {
        Notification::fake();

        [$aggregate, $start, $end, $shipping] = $this->storeMailableOrder();

        $this->assertEquals(1, $aggregate->orders->count());
        $this->assertTrue($aggregate->isActive());
        $this->assertTrue($aggregate->isRunning());
        $this->assertFalse($aggregate->canShip());

        foreach ($aggregate->orders as $order) {
            $notifiable = $order->notifiableUsers($this->gas);
            $this->assertTrue($notifiable->count() > 0);
            foreach ($notifiable as $not) {
                Notification::assertSentTo([$not], \App\Notifications\NewOrderNotification::class);
            }
        }

        $this->actingAs($this->userWithShippingPerms);
        $this->assertTrue($aggregate->canShip());

        $this->actingAs($this->userReferrer);

        foreach ($aggregate->orders as $order) {
            $order = app()->make('OrdersService')->show($order->id);

            $this->assertEquals($this->order->supplier_id, $order->supplier_id);
            $this->assertEquals('Commento di prova', $order->comment);
            $this->assertEquals($start, $order->start->format('Y-m-d'));
            $this->assertEquals($end, $order->end->format('Y-m-d'));
            $this->assertEquals($shipping, $order->shipping->format('Y-m-d'));
            $this->assertEquals($this->order->supplier->products()->count(), $order->products()->count());
            $this->assertEquals(0, $order->bookings()->count());
            $this->assertEquals($aggregate->id, $order->aggregate_id);
            $this->assertEquals('open', $order->status);
            $this->assertNotNull($order->supplier);
            $this->assertNotNull($order->printableName());
            $this->assertNotNull($order->statusIcons());
            $this->assertNotNull($order->printableDates());
            $this->assertNotNull($order->printableHeader());
            $this->assertTrue($order->isActive());
            $this->assertTrue($order->isRunning());
        }
    }

    /*
        Creazione Ordine.
        Questa funzione è identica alla precedente, ma non intercetta le
        notifiche in modo che venga eseguito il codice destinato alla loro
        formattazione. Non vengono qui eseguiti test rilevanti: basta che non si
        schianti
    */
    public function test_store_mails()
    {
        $this->storeMailableOrder();
        $this->assertTrue(true);
    }

    /*
        Modifica Ordine con permessi sbagliati
    */
    public function test_fails_to_update()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('OrdersService')->update($this->order->id, []);
    }

    /*
        Modifica Ordine con ID non esistente
    */
    public function test_fails_to_update_because_no_user_with_id()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userReferrer);
        app()->make('OrdersService')->update('broken', []);
    }

    /*
        Modifica Ordine
    */
    public function test_update()
    {
        $this->actingAs($this->userReferrer);

        $new_shipping = date('Y-m-d', strtotime('+40 days'));

        app()->make('OrdersService')->update($this->order->id, [
            'comment' => 'Un altro commento',
            'shipping' => $new_shipping,
        ]);

        $order = app()->make('OrdersService')->show($this->order->id);

        $this->assertEquals($order->comment, 'Un altro commento');
        $this->assertEquals($order->shipping->format('Y-m-d'), $new_shipping);
        $this->assertEquals($order->start, $this->order->start);
        $this->assertEquals($order->end, $this->order->end);
    }

    private function initMailableOrder()
    {
        $this->populateOrder($this->order);

        $this->nextRound();

        $booking = $this->order->bookings()->first();

        $booking->user->addContact('email', fake()->email());
        $this->userReferrer->addContact('email', fake()->email());

        $this->gas->setConfig('auto_referent_order_summary', '1');
        $this->gas->setConfig('auto_user_order_summary', '1');
        $this->order->supplier->notify_on_close_enabled = 'shipping_summary';
        $this->order->supplier->addContact('email', fake()->email());
        $this->order->supplier->save();

        $this->nextRound();

        $this->travel(6)->days();

        $this->nextRound();

        Artisan::call('close:orders');

        return $booking;
    }

    /*
        Chiusura ordini automatica
    */
    public function test_auto_close()
    {
        Notification::fake();

        $booking = $this->initMailableOrder();

        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertEquals('closed', $order->status);

        Notification::assertSentTo([$order->supplier], \App\Notifications\SupplierOrderShipping::class);
        Notification::assertSentTo([$this->userReferrer], \App\Notifications\ClosedOrdersNotification::class);
        Notification::assertSentTo([$booking->user], \App\Notifications\BookingNotification::class);
    }

    /*
        Chiusura ordini automatica.
        Questa funzione è identica alla precedente, ma non intercetta le
        notifiche in modo che venga eseguito il codice destinato alla loro
        formattazione. Non vengono qui eseguiti test rilevanti: basta che non si
        schianti
    */
    public function test_auto_close_mails()
    {
        $this->initMailableOrder();
        $this->assertTrue(true);
    }

    /*
        Cambio stato
    */
    public function test_change_state()
    {
        Bus::fake();

        $this->actingAs($this->userReferrer);

        app()->make('OrdersService')->update($this->order->id, [
            'status' => 'closed',
        ]);

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertTrue($order->isActive());
        $this->assertFalse($order->isRunning());

        app()->make('OrdersService')->update($this->order->id, [
            'status' => 'open',
        ]);

        Bus::assertDispatched(\App\Jobs\NotifyNewOrder::class);

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertTrue($order->isActive());
        $this->assertTrue($order->isRunning());

        app()->make('OrdersService')->update($this->order->id, [
            'status' => 'shipped',
        ]);

        $this->nextRound();
        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertFalse($order->isActive());
        $this->assertFalse($order->isRunning());
    }

    /*
        Accesso Ordine con ID non esistente
    */
    public function test_fails_to_show_inexistent()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('OrdersService')->show('random');
    }

    /*
        Accesso Ordine
    */
    public function test_show()
    {
        $this->actingAs($this->userWithNoPerms);
        $order = app()->make('OrdersService')->show($this->order->id);

        $this->assertEquals($this->order->id, $order->id);
        $this->assertEquals($this->order->name, $order->name);
    }

    /*
        Cancellazione Ordine con permessi sbagliati
    */
    public function test_fails_to_destroy()
    {
        $this->expectException(AuthException::class);
        $this->actingAs($this->userWithNoPerms);
        app()->make('OrdersService')->destroy($this->order->id);
    }

    /*
        Cancellazione Ordine
    */
    public function test_destroy()
    {
        $this->actingAs($this->userReferrer);

        app()->make('OrdersService')->destroy($this->order->id);
        $this->expectException(ModelNotFoundException::class);
        $order = app()->make('OrdersService')->show($this->order->id);
    }

    /*
        Assegnazione numeri agli ordini
    */
    public function test_numbers()
    {
        $this->actingAs($this->userReferrer);

        $this_year = date('Y');

        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));
        $shipping = date('Y-m-d', strtotime('+30 days'));

        $aggregate = app()->make('OrdersService')->store([
            'supplier' => $this->order->supplier_id,
            'start' => printableDate($start),
            'end' => printableDate($end),
            'shipping' => printableDate($shipping),
            'status' => 'open',
        ]);

        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertEquals($order->internal_number, '1 / ' . $this_year);

        foreach ($aggregate->orders as $order) {
            $order = app()->make('OrdersService')->show($order->id);
            $this->assertEquals($order->internal_number, '2 / ' . $this_year);
        }

        $second_aggregate = app()->make('OrdersService')->store([
            'supplier' => $this->order->supplier_id,
            'start' => printableDate(date('Y-m-d', strtotime($start . ' +1 year'))),
            'end' => printableDate(date('Y-m-d', strtotime($end . ' +1 year'))),
            'status' => 'closed',
        ]);

        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertEquals($order->internal_number, '1 / ' . $this_year);

        foreach ($aggregate->orders as $order) {
            $order = app()->make('OrdersService')->show($order->id);
            $this->assertEquals($order->internal_number, '2 / ' . $this_year);
        }

        foreach ($second_aggregate->orders as $order) {
            $order = app()->make('OrdersService')->show($order->id);
            $this->assertEquals($order->internal_number, '1 / ' . ($this_year + 1));
        }
    }

    /*
        Modificatori ereditati dal fornitore
    */
    public function test_init_modifiers()
    {
        $this->actingAs($this->userReferrer);

        $this->order->supplier->applicableModificationTypes();
        $mod = $this->order->supplier->modifiers()->where('modifier_type_id', 'spese-trasporto')->first();
        $this->assertNotNull($mod);
        app()->make('ModifiersService')->update($mod->id, [
            'value' => 'absolute',
            'arithmetic' => 'sum',
            'scale' => 'minor',
            'applies_type' => 'none',
            'applies_target' => 'order',
            'distribution_type' => 'price',
            'simplified_amount' => 30,
        ]);

        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));
        $shipping = date('Y-m-d', strtotime('+30 days'));

        $this->nextRound();

        $aggregate = app()->make('OrdersService')->store([
            'supplier' => $this->order->supplier_id,
            'start' => printableDate($start),
            'end' => printableDate($end),
            'shipping' => printableDate($shipping),
            'status' => 'open',
        ]);

        $this->assertEquals(1, $aggregate->orders->count());

        foreach ($aggregate->orders as $order) {
            $order = app()->make('OrdersService')->show($order->id);
            $this->assertEquals($order->modifiers->count(), 1);
            $this->assertEquals($order->modifiers->first()->modifierType->id, 'spese-trasporto');
        }
    }

    /*
        Esportazione GDXP
    */
    public function test_export_gdxp()
    {
        $this->actingAs($this->userReferrer);
        $this->assertNotNull($this->order->exportXML());
        $this->assertNotNull($this->order->exportJSON());
    }

    /*
        Modifica prodotti nell'ordine
    */
    public function test_remove_product()
    {
        $this->actingAs($this->userWithBasePerms);

        $target_product_1 = $this->order->products()->orderBy('id', 'asc')->first();
        $target_product_2 = $this->order->products()->orderBy('id', 'asc')->skip(1)->first();

        $data = [
            'action' => 'booked',
            $target_product_1->id => 2,
            $target_product_2->id => 3,
        ];

        $booking = $this->updateAndFetch($data, $this->order, $this->userWithBasePerms, false);

        $this->nextRound();
        $booking = Booking::find($booking->id);
        $this->assertEquals($booking->products()->count(), 2);
        $this->assertEquals($this->order->bookings()->count(), 1);

        $this->actingAs($this->userReferrer);

        app()->make('OrdersService')->update($this->order->id, [
            'supplier' => $this->order->supplier_id,
            'start' => printableDate($this->order->start),
            'end' => printableDate($this->order->end),
            'shipping' => printableDate($this->order->shipping),
            'status' => 'open',
            'enabled' => $this->order->products->filter(function ($p) use ($target_product_1) {
                return $p->id != $target_product_1->id;
            })->pluck('id')->toArray(),
        ]);

        $this->nextRound();
        $booking = Booking::find($booking->id);
        $this->assertEquals($booking->products()->count(), 1);
        $this->assertEquals($this->order->bookings()->count(), 1);

        app()->make('OrdersService')->update($this->order->id, [
            'supplier' => $this->order->supplier_id,
            'start' => printableDate($this->order->start),
            'end' => printableDate($this->order->end),
            'shipping' => printableDate($this->order->shipping),
            'status' => 'open',
            'enabled' => $this->order->products->filter(function ($p) use ($target_product_1, $target_product_2) {
                return $p->id != $target_product_1->id && $p->id != $target_product_2->id;
            })->pluck('id')->toArray(),
        ]);

        $this->nextRound();
        $this->assertEquals($this->order->bookings()->count(), 0);
        $booking = Booking::find($booking->id);
        $this->assertNull($booking);
    }

    /*
        Preserva prodotto eliminato dal listino
    */
    public function test_keep_removed_product()
    {
        $this->actingAs($this->userWithBasePerms);

        $count_products = $this->order->products()->count();
        $target_product_1 = $this->order->products()->orderBy('id', 'asc')->first();
        $target_product_2 = $this->order->products()->orderBy('id', 'asc')->skip(1)->first();

        $data = [
            'action' => 'booked',
            $target_product_1->id => 2,
            $target_product_2->id => 3,
        ];

        $booking = $this->updateAndFetch($data, $this->order, $this->userWithBasePerms, false);

        $this->nextRound();

        $this->actingAs($this->userReferrer);
        app()->make('ProductsService')->destroy($target_product_2->id);
        $this->order = app()->make('OrdersService')->show($this->order->id);
        $this->assertEquals($count_products, $this->order->products()->count());

        $this->nextRound();

        app()->make('OrdersService')->update($this->order->id, [
            'supplier' => $this->order->supplier_id,
            'start' => printableDate($this->order->start),
            'end' => printableDate($this->order->end),
            'shipping' => printableDate($this->order->shipping),
            'status' => 'open',
            'enabled' => $this->order->products->pluck('id')->toArray(),
        ]);

        $this->nextRound();
        $this->order = app()->make('OrdersService')->show($this->order->id);
        $this->assertEquals($count_products, $this->order->products()->count());
    }

    /*
        Cambio prezzo di un prodotto
    */
    public function test_change_product_price()
    {
        $this->actingAs($this->userReferrer);

        $product = $this->order->products()->inRandomOrder()->first();
        $old_price = $product->getPrice();
        $new_price = $old_price + 2;
        app()->make('ProductsService')->update($product->id, [
            'name' => $product->name,
            'price' => $new_price,
        ]);

        $this->nextRound();

        $product_order = $this->order->products()->where('product_id', $product->id)->first();
        $this->assertEquals($old_price, $product_order->getPrice());

        $product_raw = app()->make('ProductsService')->show($product->id);
        $this->assertEquals($new_price, $product_raw->getPrice());

        $this->assertFalse($product_raw->comparePrices($product_order));
    }

    /*
        Cambio prezzo di un prodotto e consegna
    */
    public function test_change_product_price_on_delivery()
    {
        $this->populateOrder($this->order);

        $this->nextRound();

        $this->actingAs($this->userWithShippingPerms);
        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertTrue($order->bookings()->count() > 0);
        app()->make('FastBookingsService')->fastShipping($this->userWithShippingPerms, $order->aggregate, null);

        $this->nextRound();

        $this->actingAs($this->userReferrer);
        $order = app()->make('OrdersService')->show($this->order->id);
        foreach ($order->bookings as $booking) {
            foreach ($booking->products as $product) {
                $target_product = $product->product;
                break;
            }
        }

        $old_price = $target_product->getPrice();
        $new_price = $old_price + 2;
        app()->make('ProductsService')->update($target_product->id, [
            'name' => $target_product->name,
            'price' => $new_price,
        ]);

        $this->nextRound();

        $this->actingAs($this->userReferrer);
        $tested = false;
        $order = app()->make('OrdersService')->show($this->order->id);
        foreach ($order->bookings as $booking) {
            foreach ($booking->products as $product) {
                if ($product->product_id == $target_product->id) {
                    $this->assertTrue($product->final_price != 0);
                    $assigned = closestNumber([$old_price, $new_price], $product->getFinalUnitPrice());
                    $this->assertTrue($assigned == $old_price);
                    $tested = true;
                }
            }
        }

        $this->assertTrue($tested);
    }

    /*
        Cambio prezzo di un prodotto e aggiornamento ordine
    */
    public function test_do_not_update_price()
    {
        $this->actingAs($this->userReferrer);

        $product = $this->order->products()->inRandomOrder()->first();
        $old_price = $product->getPrice();
        $new_price = $old_price + 2;
        app()->make('ProductsService')->update($product->id, [
            'name' => $product->name,
            'price' => $new_price,
        ]);

        $this->nextRound();

        $aggregate = app()->make('OrdersService')->update($this->order->id, [
            'supplier' => $this->order->supplier_id,
            'start' => printableDate($this->order->start),
            'end' => printableDate($this->order->end),
            'shipping' => printableDate($this->order->shipping),
            'status' => 'open',
            'enabled' => $this->order->products->pluck('id')->toArray(),
        ]);

        $this->nextRound();

        $order = app()->make('OrdersService')->show($aggregate->orders->first()->id);
        $product_order = $order->products()->where('product_id', $product->id)->first();
        $this->assertEquals($old_price, $product_order->getPrice());
    }

    /*
        Cambio prezzo di una variante
    */
    public function test_change_product_variant_price()
    {
        $this->actingAs($this->userReferrer);

        $product = $this->order->supplier->products()->inRandomOrder()->first();
        $product_price = $product->getPrice();

        $ids = [];
        $active = [];
        $variant = $this->createVariant($product);
        foreach ($variant->values as $index => $val) {
            $ids[] = $val->id;

            $combo = VariantCombo::byValues([$val->id]);
            $actives[] = $combo->id;
        }

        app()->make('VariantsService')->matrix($product, $ids, $actives, ['', '', ''], [0, 0, 0], [0, 0, 0]);

        $this->nextRound();

        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+20 days'));
        $shipping = date('Y-m-d', strtotime('+30 days'));

        $aggregate = app()->make('OrdersService')->store([
            'supplier' => $this->order->supplier_id,
            'start' => printableDate($start),
            'end' => printableDate($end),
            'shipping' => printableDate($shipping),
            'status' => 'open',
        ]);

        $product_in_order = $aggregate->orders->first()->products()->where('product_id', $product->id)->first();
        $this->assertTrue(isset($product_in_order->pivot->prices));
        $this->assertTrue(isset(json_decode($product_in_order->pivot->prices)->variants));
        $combos = $product_in_order->variant_combos;
        $this->assertFalse($combos->isEmpty());
        foreach ($combos as $combo) {
            $this->assertEquals($product_price, $combo->getPrice());
        }

        $this->nextRound();

        $product = app()->make('ProductsService')->show($product->id);
        app()->make('VariantsService')->matrix($product, $ids, $actives, ['', '', ''], [0, 0, 1], [0, 0, 0]);

        $this->nextRound();

        $product_in_order = $aggregate->orders->first()->products()->where('product_id', $product->id)->first();
        $this->assertTrue(isset($product_in_order->pivot->prices));
        $this->assertTrue(isset(json_decode($product_in_order->pivot->prices)->variants));

        $this->nextRound();

        $product = app()->make('ProductsService')->show($product->id);
        $order = app()->make('OrdersService')->show($aggregate->orders->first()->id);
        $new_product = $order->products->firstWhere('id', $product->id);
        $this->assertFalse($product->comparePrices($new_product));
    }

    /*
        Gestione ordine con prenotazioni non chiuse
    */
    public function test_pending()
    {
        $product = $this->order->products->random();
        $product->package_size = 10;
        $product->save();

        $this->order->keep_open_packages = 'each';
        $this->order->save();

        $booking = $this->initMailableOrder();

        $this->nextRound();

        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertTrue($order->aggregate->hasPendingPackages());
        $pending = $order->pendingPackages();
        $this->assertEquals(1, $pending->count());

        foreach($pending as $pend) {
            $this->assertEquals($product->id, $pend->id);
        }
    }

    /*
        Registra il pagamento al fornitore
    */
    public function test_order_payment()
    {
        $this->populateOrder($this->order);

        $this->nextRound();

        $this->actingAs($this->userWithShippingPerms);
        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertTrue($order->bookings()->count() > 0);
        app()->make('FastBookingsService')->fastShipping($this->userWithShippingPerms, $order->aggregate, null);

        $this->nextRound();

        $this->actingAs($this->userReferrer);
        $order = app()->make('OrdersService')->show($this->order->id);
        $summary = $order->aggregate->reduxData();
        $this->assertTrue($summary->price > 0);

        $this->actingAs($this->userAdmin);
        $currency = defaultCurrency();

        app()->make('MovementsService')->store([
            'type' => 'order-payment',
            'method' => 'bank',
            'sender_id' => $this->gas->id,
            'sender_type' => 'App\Gas',
            'target_id' => $this->order->id,
            'target_type' => 'App\Order',
            'currency_id' => $currency->id,
            'amount' => $summary->price,
        ]);

        $this->nextRound();

        $this->actingAs($this->userReferrer);
        $order = app()->make('OrdersService')->show($this->order->id);
        $this->assertNotNull($order->payment);
        $this->assertEquals('archived', $order->status);
    }
}
