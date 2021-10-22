<?php

namespace App\Printers;

use PDF;

class AggregateBooking extends Printer
{
    public function document($obj, $type, $request)
    {
        $bookings = [$obj];

        foreach($obj->user->friends as $friend) {
            $friend_booking = $obj->aggregate->bookingBy($friend->id);
            if (!empty($friend_booking->bookings)) {
                $bookings[] = $friend_booking;
            }
        }

        $names = [];
        foreach($obj->aggregate->orders as $order) {
            $names[] = sprintf('%s %s', $order->supplier->name, $order->internal_number);
        }

        $names = join(' / ', $names);
        $filename = sanitizeFilename(_i('Dettaglio Consegne ordini %s.pdf', [$names]));

        $pdf = PDF::loadView('documents.personal_aggregate_shipping', [
            'aggregate' => $obj->aggregate,
            'bookings' => $bookings,
        ]);

        return $pdf->download($filename);
    }
}
