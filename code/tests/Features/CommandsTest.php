<?php

namespace Tests\Features;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

use App\Notifications\RemindOrderNotification;
use App\User;

class CommandsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reminders()
    {
        Notification::fake();

        $this->gas->setConfig('send_order_reminder', 5);
        $this->gas->setConfig('notify_all_new_orders', 1);
        $this->gas = $this->gas->fresh();

        $users = User::factory()->count(3)->create(['gas_id' => $this->gas->id]);
        foreach($users as $user) {
            $user->addContact('email', fake()->email());
        }

        $this->nextRound();

        $users = User::whereHas('contacts', function ($query) {
            $query->where('type', 'email');
        })->get();

        $order = $this->initOrder(null);
        $this->assertEquals(Carbon::now()->addDays(5)->format('Y-m-d'), $order->end->format('Y-m-d'));
        Artisan::call('remind:orders');

        Notification::assertSentTo($users, RemindOrderNotification::class);
    }
}
