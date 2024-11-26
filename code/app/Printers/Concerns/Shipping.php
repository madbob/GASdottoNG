<?php

namespace App\Printers\Concerns;

use App\Formatters\User as UserFormatter;
use App\Formatters\Order as OrderFormatter;
use App\Delivery;
use App\ModifiedValue;

trait Shipping
{
    private function collectModifiersTotal($booking, $aggregate_data, $extra_modifiers)
    {
        $total_modifiers = 0;
        $labels = [];

        $modifiers = $booking->applyModifiersWithFriends($aggregate_data, false);
        $modifiers = $this->filterExtraModifiers($modifiers, $extra_modifiers);

        $aggregated_modifiers = ModifiedValue::aggregateByType($modifiers);
        foreach($aggregated_modifiers as $am) {
            $labels[$am->name] = printablePrice($am->total_amount);
            $total_modifiers += $am->amount;
        }

        return [$labels, $total_modifiers];
    }

    /*
        All'occorrenza, qui "falsifico" temporaneamente lo stato della
        prenotazione per far tornare i conti in fase di valutazione dei
        modificatori
    */
    private function fakeBookingStatus($status, $booking)
    {
        $original_booking_status = null;

        if ($status == 'pending') {
            if ($booking->status == 'shipped' || $booking->status == 'saved') {
                $original_booking_status = $booking->status;
                $booking->status = $status;

                foreach($booking->friends_bookings as $friend) {
                    $friend->status = $status;
                }
            }
        }

        return $original_booking_status;
    }

    private function restoreBookingStatus($original_booking_status, $booking)
    {
        if ($original_booking_status != null) {
            $booking->status = $original_booking_status;

            foreach($booking->friends_bookings as $friend) {
                $friend->status = $original_booking_status;
            }
        }
    }

    public function formatShipping($order, $fields, $status, $shipping_place, $extra_modifiers)
    {
        $ret = (object) [
            'headers' => $fields->headers,
            'contents' => [],
        ];

        $formattable_product = OrderFormatter::formattableColumns('shipping');
        $internal_offsets = $this->offsetsByStatus($status);

        $bookings = $order->topLevelBookings(null);
        $bookings = Delivery::sortBookingsByShippingPlace($bookings, $shipping_place);
        $listed_products = [];

        $modifiers = $order->involvedModifiers(true);
        $aggregate_data = $order->minimumRedux($modifiers);

        foreach ($bookings as $booking) {
            $obj = (object) [
                'user_id' => $booking->user->id,

                /*
                    Questi parametri vengono usati per riordinare le
                    prenotazioni rastrellate da diversi ordini, quando genero il
                    documento di Dettaglio Consegne per un aggregato
                */
                'user_sorting' => $booking->user->lastname,
                'gas_sorting' => $booking->user->gas_id,
                'shipping_sorting' => $booking->user->shippingplace ? $booking->user->shippingplace->name : 'AAAA',

                'user' => UserFormatter::format($booking->user, $fields->user_columns, $order->aggregate),
                'products' => [],
                'totals' => [],
                'notes' => !empty($booking->notes) ? [$booking->notes] : [],
            ];

            foreach($booking->products_with_friends as $booked) {
                if (isset($listed_products[$booked->product_id])) {
                    $product = $listed_products[$booked->product_id];
                    $booked->setRelation('product', $product);
                }
                else {
                    $product = $booked->product;
                    $listed_products[$booked->product_id] = $product;
                }

                $summary = $booked->as_summary;

                $row = $this->formatProduct($fields->product_columns, $formattable_product, $summary->products[$booked->product->id], $product, $internal_offsets);
                if (!empty($row)) {
                    $obj->products = array_merge($obj->products, $row);
                }
            }

            if (empty($obj->products)) {
                continue;
            }

            $original_booking_status = $this->fakeBookingStatus($status, $booking);

            list($labels_modifiers, $total_modifiers) = $this->collectModifiersTotal($booking, $aggregate_data, $extra_modifiers);
            $obj->totals = array_merge($obj->totals, $labels_modifiers);
            $obj->totals['total'] = $booking->getValue($internal_offsets->by_booking, true) + $total_modifiers;

            $this->restoreBookingStatus($original_booking_status, $booking);

            $ret->contents[] = $obj;
        }

        return $ret;
    }
}
