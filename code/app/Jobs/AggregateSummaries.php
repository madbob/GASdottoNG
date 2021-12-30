<?php

namespace App\Jobs;

use Log;

use App\Notifications\BookingNotification;

use App\Aggregate;

class AggregateSummaries extends Job
{
    public $aggregate_id;
    public $message;

    public function __construct($aggregate_id, $message = '')
    {
        parent::__construct();
        $this->aggregate_id = $aggregate_id;
        $this->message = $message;
    }

    private function handleBookings($aggregate, $status)
    {
        $bookings = $aggregate->bookings->filter(function($booking) use ($status) {
            return in_array($booking->status, $status);
        });

        foreach($bookings as $booking) {
            try {
                $booking->user->notify(new BookingNotification($this->aggregate_id, $booking->user->id, $this->message));
            }
            catch(\Exception $e) {
                Log::error('Impossibile inviare notifica mail prenotazione di ' . $booking->user->id);
            }
        }
    }

    protected function realHandle()
    {
        $aggregate = Aggregate::findOrFail($this->aggregate_id);
        $this->hub->enable(false);

        $date = date('Y-m-d');
        foreach($aggregate->orders as $order) {
            $order->last_notify = $date;
            $order->save();
        }

        if ($aggregate->isActive()) {
            $status = ['pending', 'saved'];
        }
        else {
            $status = ['shipped'];
        }

        $this->handleBookings($aggregate, $status);
        $this->hub->enable(true);
    }
}
