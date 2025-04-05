<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Notifications\BookingNotification;
use App\Aggregate;

class AggregateSummaries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $aggregate_id;

    public $message;

    public function __construct($aggregate_id, $message = '')
    {
        $this->aggregate_id = $aggregate_id;
        $this->message = $message;
    }

    private function handleBookings($aggregate)
    {
        $bookings = $aggregate->notifiableBookings();
        $redux = $aggregate->reduxData();

        foreach ($bookings as $booking) {
            try {
                $booking->user->notify(new BookingNotification($this->aggregate_id, $redux, $booking->user->id, $this->message));
            }
            catch (\Exception $e) {
                \Log::error('Impossibile inviare notifica mail prenotazione di ' . $booking->user->id . ': ' . $e->getMessage());
            }
        }
    }

    public function handle()
    {
        $hub = app()->make('GlobalScopeHub');

        $aggregate = Aggregate::findOrFail($this->aggregate_id);
        $hub->enable(false);

        $date = date('Y-m-d');
        foreach ($aggregate->orders as $order) {
            $order->last_notify = $date;
            $order->save();
        }

        $this->handleBookings($aggregate);
        $hub->enable(true);
    }
}
