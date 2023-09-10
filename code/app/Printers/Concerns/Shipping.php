<?php

namespace App\Printers\Concerns;

use App\Formatters\User as UserFormatter;
use App\Formatters\Order as OrderFormatter;
use App\Delivery;
use App\ModifiedValue;

trait Shipping
{
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

                'user' => UserFormatter::format($booking->user, $fields->user_columns),
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

            /*
                All'occorrenza, qui "falsifico" temporaneamente lo stato della
                prenotazione per far tornare i conti in fase di valutazione dei
                modificatori
            */
            $original_booking_status = null;
            if (($booking->status == 'shipped' || $booking->status == 'saved') && $status == 'booked') {
                $original_booking_status = $booking->status;
                $booking->status = 'pending';
            }

            $modifiers = $booking->applyModifiers($aggregate_data, false);

            foreach($booking->friends_bookings as $friend) {
                $friend_modifiers = $friend->applyModifiers($aggregate_data, false);
                $modifiers = $modifiers->merge($friend_modifiers);
            }

            if ($extra_modifiers == false) {
                $modifiers = $modifiers->filter(function($mod) {
                    return is_null($mod->modifier->movementType);
                });
            }

            $total_modifiers = 0;
            $aggregated_modifiers = ModifiedValue::aggregateByType($modifiers);
            foreach($aggregated_modifiers as $am) {
                $obj->totals[$am->name] = printablePrice($am->amount);
                $total_modifiers += $am->amount;
            }

            $obj->totals['total'] = $booking->getValue($internal_offsets->by_booking, true) + $total_modifiers;

            if ($original_booking_status != null) {
                $booking->status = $original_booking_status;
            }

            $ret->contents[] = $obj;
        }

        return $ret;
    }
}
