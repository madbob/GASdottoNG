<?php

namespace App\Printers;

use PDF;

class AggregateBooking extends Printer
{
    public function document($obj, $type, $request)
    {
        $bookings = [$obj];

        foreach ($obj->user->friends as $friend) {
            $friend_booking = $obj->aggregate->bookingBy($friend->id);
            if ($friend_booking->bookings->isEmpty() === false) {
                $bookings[] = $friend_booking;
            }
        }

        $names = [];
        foreach ($obj->aggregate->orders as $order) {
            $names[] = sprintf('%s %s', $order->supplier->name, $order->internal_number);
        }

        $names = implode(' / ', $names);
        $filename = sanitizeFilename(__('texts.orders.documents.shipping.filename', ['suppliers' => $names]));

        $pdf = PDF::loadView('documents.personal_aggregate_shipping', [
            'aggregate' => $obj->aggregate,
            'bookings' => $bookings,
        ]);

        return $pdf->download($filename);
    }
}
