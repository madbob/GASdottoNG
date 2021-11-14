<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Exceptions\AuthException;
use App\Exceptions\InvalidQuantityConstraint;

use DB;
use Log;

use App\User;
use App\Aggregate;
use App\ModifierType;

class DynamicBookingsService extends BookingsService
{
    private function reduceVariants($product, $delivering)
    {
        return $product->variants->reduce(function($varcarry, $variant) use ($product, $delivering) {
            try {
                $final_variant_quantity = $delivering ? $variant->delivered : $product->testConstraints($variant->quantity, $variant);
                $variant_message = '';
            }
            catch(InvalidQuantityConstraint $e) {
                $final_variant_quantity = 0;
                $variant_message = $e->getMessage();
            }

            $varcarry[] = (object) [
                'components' => $variant->components->reduce(function($componentcarry, $component) {
                    $componentcarry[] = $component->value->id;
                    return $componentcarry;
                }, []),

                'quantity' => $final_variant_quantity,
                'total' => printablePrice($delivering ? $variant->deliveredValue() : $variant->quantityValue()),
                'message' => $variant_message,
            ];

            return $varcarry;
        }, []);
    }

    private function deliveringManualTotal($request, $order)
    {
        if (isset($request['manual_total_' . $order->id])) {
            $manual_total = $request['manual_total_' . $order->id];
            if (filled($manual_total)) {
                return $manual_total;
            }
        }

        return 0;
    }

    private function initDynamicModifier($mod)
    {
        return (object) [
            'label' => $mod->descriptive_name,
            'url' => $mod->modifier->getROShowURL(),
            'amount' => 0,
            'variable' => $mod->is_variable,
            'passive' => ($mod->type == 'passive'),
        ];
    }

    private function translateBooking($booking, $delivering)
    {
        /*
            Qui forzo sempre il ricalcolo dei modificatori, altrimenti vengono
            letti quelli effettivamente salvati sul DB.
            Nota bene: passo il parametro real = true perché qui sono già
            all'interno di una transazione, ed i valori qui calcolati devono
            esistere anche successivamente mentre recupero i totali dei singoli
            prodotti.
            La prenotazione è ancora in fase di consegna, lo status è impostato
            temporaneamente a "shipped" ed andrebbe a leggere quelli salvati
            anche se ancora non ce ne sono
        */
        $modified = $booking->calculateModifiers(null, true);
        $calculated_total = $booking->getValue('effective', false);

        $ret = (object) [
            'total' => printablePrice($calculated_total),
            'modifiers' => [],
            'products' => $booking->products->reduce(function($carry, $product) use ($delivering) {
                /*
                    Mentre computo il valore totale della prenotazione in fase
                    di modifica, controllo anche che le quantità prenotate siano
                    coerenti coi limiti imposti sul prodotto prenotato (massimo,
                    minimo, disponibile...).
                    Lo faccio qui, server-side, per evitare problemi di
                    compatibilità client-side (è stato più volte segnalato che
                    su determinati browser mobile ci siano problemi su questi
                    controlli).
                    Ma solo se non sono in consegna: in quel caso è ammesso
                    immettere qualsiasi quantità
                */
                try {
                    $final_quantity = $delivering ? $product->delivered : $product->testConstraints($product->quantity);
                    $message = '';
                }
                catch(InvalidQuantityConstraint $e) {
                    $final_quantity = 0;
                    $message = $e->getMessage();
                }

                $carry[$product->product_id] = (object) [
                    'total' => printablePrice($product->getValue('effective')),
                    'quantity' => $final_quantity,
                    'message' => $message,
                    'variants' => $this->reduceVariants($product, $delivering),
                    'modifiers' => [],
                ];
                return $carry;
            }, []),
        ];

        foreach($modified as $mod) {
            if ($mod->target_type == 'App\Product') {
                if (!isset($ret->products[$mod->target->product_id]->modifiers[$mod->modifier_id])) {
                    $ret->products[$mod->target->product_id]->modifiers[$mod->modifier_id] = $this->initDynamicModifier($mod);
                }

                $ret->products[$mod->target->product_id]->modifiers[$mod->modifier_id]->amount += $mod->effective_amount;
            }
            else {
                if (!isset($ret->modifiers[$mod->modifier_id])) {
                    $ret->modifiers[$mod->modifier_id] = $this->initDynamicModifier($mod);
                }

                $ret->modifiers[$mod->modifier_id]->amount += $mod->effective_amount;
            }
        }

        return $ret;
    }

    private function handleManualTotalShipping($request, $data, $booking)
    {
        $manual_total = $this->deliveringManualTotal($request, $booking->order);

        if ($manual_total > 0) {
            $manual_adjust_modifier = ModifierType::find('arrotondamento-consegna');

            $data->modifiers['arrotondamento-consegna'] = (object) [
                'label' => $manual_adjust_modifier->name,
                'url' => '',
                'amount' => $manual_total - $booking->getValue('effective', false),
                'variable' => false,
                'passive' => false,
            ];

            $data->total = printablePrice($manual_total);
        }

        return $data;
    }

    private function initBookingFromRequest($request, $order, $target_user, $delivering)
    {
        $booking = $this->readBooking($request, $order, $target_user, $delivering);

        if ($booking) {
            $booking->setRelation('order', $order);

            if ($delivering) {
                $booking->status = 'shipped';
                $booking->saveFinalPrices();
            }
            else {
                $booking->status = 'pending';
            }

            $booking->save();
        }

        return $booking;
    }

    /*
        Questa funzione viene invocata dai pannelli di prenotazione e consegna,
        ogni volta che viene apportata una modifica sulle quantità, e permette
        di controllare che le quantità immesse siano coerenti coi constraints
        imposti sui prodotti (quantità minima, quantità multipla...) e calcolare
        tutti i valori tenendo in considerazione tutti i modificatori esistenti.
        Eseguire tutti questi calcoli client-side in JS sarebbe complesso, e
        ridondante rispetto all'implementazione server-side che comunque sarebbe
        necessaria
    */
    public function dynamicModifiers(array $request, $aggregate, $target_user)
    {
        return app()->make('Locker')->execute('lock_aggregate_' . $aggregate->id, function() use ($request, $aggregate, $target_user) {
            DB::beginTransaction();

            $bookings = [];
            $delivering = $request['action'] != 'booked';

            $ret = (object) [
                'bookings' => [],
            ];

            foreach($aggregate->orders as $order) {
                $this->testAccess($target_user, $order->supplier, $delivering);

                $order->setRelation('aggregate', $aggregate);
                $booking = $this->initBookingFromRequest($request, $order, $target_user, $delivering);
                if ($booking) {
                    $bookings[] = $booking;
                }
            }

            foreach($bookings as $booking) {
                $ret->bookings[$booking->id] = $this->translateBooking($booking, $delivering);
                if ($delivering) {
                    $ret->bookings[$booking->id] = $this->handleManualTotalShipping($request, $ret->bookings[$booking->id], $booking);
                }
            }

            /*
                Lo scopo di questa funzione è ottenere una preview dei totali della
                prenotazione, dunque al termine invalido tutte le modifiche fatte
                sul database
            */
            DB::rollback();

            return $ret;
        });
    }
}
