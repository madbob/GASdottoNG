<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Exceptions\AuthException;

use App\Exceptions\InvalidQuantityConstraint;
use App\Exceptions\AnnotatedQuantityConstraint;
use App\Events\BookingDelivered;

use DB;
use Log;
use Auth;

use App\User;
use App\Aggregate;
use App\ModifierType;
use App\ModifiedValue;

class DynamicBookingsService extends BookingsService
{
    private function handleQuantity($delivering, $product, $subject, $variant)
    {
        /*
            Mentre computo il valore totale della prenotazione in fase di
            modifica, controllo anche che le quantità prenotate siano coerenti
            coi limiti imposti sul prodotto prenotato (massimo, minimo,
            disponibile...).
            Lo faccio qui, server-side, per evitare problemi di compatibilità
            client-side (è stato più volte segnalato che su determinati browser
            mobile ci siano problemi su questi controlli).
            Ma solo se non sono in consegna: in quel caso è ammesso immettere
            qualsiasi quantità
        */

        if ($delivering) {
            $final_quantity = $subject->delivered;
            $message = '';
        }
        else {
            try {
                $final_quantity = $product->testConstraints($subject->delivered, $variant);
                $message = '';
            }
            catch(InvalidQuantityConstraint $e) {
                $final_quantity = 0;
                $message = $e->getMessage();
            }
            catch(AnnotatedQuantityConstraint $e) {
                $final_quantity = $subject->delivered;
                $message = $e->getMessage();
            }
        }

        return [$final_quantity, $message];
    }

    private function reduceVariants($product, $delivering)
    {
        return $product->variants->reduce(function($varcarry, $variant) use ($product, $delivering) {
            list($final_variant_quantity, $variant_message) = $this->handleQuantity($delivering, $product, $variant, $variant);

            $varcarry[] = (object) [
                'components' => $variant->components->reduce(function($componentcarry, $component) {
                    $componentcarry[] = $component->value->id;
                    return $componentcarry;
                }, []),

                'quantity' => $final_variant_quantity,
                'total' => printablePrice($variant->deliveredValue()),
                'message' => $variant_message,
            ];

            return $varcarry;
        }, []);
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
        $booking->unsetRelation('modifiedValues');
        $modified = $booking->modifiedValues;
        $calculated_total = $booking->getValue('effective', false, true);

        $ret = (object) [
            'total' => printablePrice($calculated_total),
            'modifiers' => [],
            'products' => $booking->products->reduce(function($carry, $product) use ($delivering) {
                list($final_quantity, $message) = $this->handleQuantity($delivering, $product, $product, null);

                $carry[$product->product_id] = (object) [
                    'total' => printablePrice($product->getValue('delivered')),
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
        return DB::transaction(function() use ($request, $aggregate, $target_user) {
            $bookings = [];
            $delivering = $request['action'] != 'booked';

            $ret = (object) [
                'bookings' => [],
            ];

            foreach($aggregate->orders as $order) {
                $user = $this->testAccess($target_user, $order->supplier, $delivering);

                $request['action'] = 'shipped';
                $booking = $this->handleBookingUpdate($request, $user, $order, $target_user, true);

                if ($booking) {
                    $ret->bookings[$booking->id] = $this->translateBooking($booking, $delivering);
                }
            }

            /*
                Lo scopo di questa funzione è ottenere una preview dei totali della
                prenotazione, dunque al termine invalido tutte le modifiche fatte
                sul database
            */
            DB::rollback();

            return $ret;
        }, 3);
    }
}
