<?php

namespace App\Printers\Concerns;

use App\Formatters\User as UserFormatter;
use App\Formatters\Order as OrderFormatter;
use App\Delivery;
use App\ModifiedValue;

trait Shipping
{
    private function collectModifiersTotal($booking, $aggregate_data, $isolate_friends, $extra_modifiers)
    {
        $total_modifiers = 0;
        $labels = [];

        if ($isolate_friends) {
            $modifiers = $booking->applyModifiers($aggregate_data, false);
        }
        else {
            $modifiers = $booking->applyModifiersWithFriends($aggregate_data, false);
        }

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
    private function fakeBookingStatus($status, $isolate_friends, $booking)
    {
        $original_booking_status = null;

        if ($status == 'pending') {
            if ($booking->status == 'shipped' || $booking->status == 'saved') {
                $original_booking_status = $booking->status;
                $booking->status = $status;

                if ($isolate_friends == false) {
                    foreach($booking->friends_bookings as $friend) {
                        $friend->status = $status;
                    }
                }
            }
        }

        return $original_booking_status;
    }

    private function restoreBookingStatus($original_booking_status, $isolate_friends, $booking)
    {
        if ($original_booking_status != null) {
            $booking->status = $original_booking_status;

            if ($isolate_friends == false) {
                foreach($booking->friends_bookings as $friend) {
                    $friend->status = $original_booking_status;
                }
            }
        }
    }

    public function formatShipping($order, $fields, $status, $isolate_friends, $shipping_place, $extra_modifiers)
    {
        $ret = (object) [
            'headers' => $fields->headers,
            'contents' => [],
        ];

        $formattable_product = OrderFormatter::formattableColumns('shipping');
        $internal_offsets = $this->offsetsByStatus($status);

        if ($isolate_friends == false) {
            $bookings = $order->topLevelBookings(null);
            $products_source = 'products_with_friends';
        }
        else {
            $bookings = $order->bookings;
            $products_source = 'products';
        }

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

            foreach($booking->$products_source as $booked) {
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

            $original_booking_status = $this->fakeBookingStatus($status, $isolate_friends, $booking);

            list($labels_modifiers, $total_modifiers) = $this->collectModifiersTotal($booking, $aggregate_data, $isolate_friends, $extra_modifiers);
            $obj->totals = array_merge($obj->totals, $labels_modifiers);
            $obj->totals['total'] = $booking->getValue($internal_offsets->by_booking, $isolate_friends == false) + $total_modifiers;

            $this->restoreBookingStatus($original_booking_status, $isolate_friends, $booking);

            $ret->contents[] = $obj;
        }

        return $ret;
    }
}
