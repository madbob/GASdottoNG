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

    protected function realHandle()
    {
        $aggregate = Aggregate::find($this->aggregate_id);

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

        foreach($aggregate->bookings as $booking) {
            if (in_array($booking->status, $status)) {
                try {
                    $booking->user->notify(new BookingNotification($booking, $this->message));
                }
                catch(\Exception $e) {
                    Log::error('Impossibile inviare notifica mail prenotazione di ' . $booking->user->id);
                }
            }
        }

        $this->hub->enable(true);
    }
}
