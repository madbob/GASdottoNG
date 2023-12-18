<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Exceptions\AuthException;

use App\Exceptions\InvalidQuantityConstraint;
use App\Exceptions\AnnotatedQuantityConstraint;
use App\Events\BookingDelivered;

use DB;
use App;
use Artisan;
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
        */

        $quantity = $delivering ? $subject->delivered : $subject->quantity;

        try {
            $final_quantity = $product->testConstraints($quantity, $variant, $delivering);
            $message = '';
        }
        catch(InvalidQuantityConstraint $e) {
            $final_quantity = 0;
            $message = $e->getMessage();
        }
        catch(AnnotatedQuantityConstraint $e) {
            $final_quantity = $quantity;
            $message = $e->getMessage();
        }

        return [$final_quantity, $message];
    }

    private function reduceVariants($product, $delivering)
    {
        return $product->variants->reduce(function($varcarry, $variant) use ($product, $delivering) {
            list($final_variant_quantity, $variant_message) = $this->handleQuantity($delivering, $product, $variant, $variant);
            $combo = $variant->variantsCombo();

            $varcarry[] = (object) [
                'components' => $variant->components->reduce(function($componentcarry, $component) {
                    $componentcarry[] = $component->value->id;
                    return $componentcarry;
                }, []),

                'quantity' => (float) $final_variant_quantity,
                'unitprice' => (float) $combo->getPrice(),
                'unitprice_human' => $product->product->printablePrice($combo),
                'total' => (float) printablePrice($delivering ? $variant->deliveredValue() : $variant->quantityValue()),
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
        $calculated_total = $booking->getValue('effective', false, true);

        $ret = (object) [
            'total' => printablePrice($calculated_total),
            'modifiers' => [],
            'products' => $booking->products->reduce(function($carry, $product) use ($booking, $delivering) {
                $product->setRelation('booking', $booking);
                list($final_quantity, $message) = $this->handleQuantity($delivering, $product, $product, null);

                $carry[$product->product_id] = (object) [
                    'unitprice' => (float) $product->product->getPrice(false),
                    'unitprice_human' => $product->product->printablePrice(),
                    'total' => (float) printablePrice($delivering ? $product->getValue('delivered') : $product->getValue('booked')),
                    'quantity' => (float) $final_quantity,
                    'message' => $message,
                    'variants' => $this->reduceVariants($product, $delivering),
                ];
                return $carry;
            }, []),
        ];

        $booking->status = $delivering ? 'shipped' : 'pending';
        $modified = $booking->applyModifiers(null, false);
        foreach($modified as $mod) {
            if (!isset($ret->modifiers[$mod->modifier_id])) {
                $ret->modifiers[$mod->modifier_id] = $this->initDynamicModifier($mod);
            }

            $ret->modifiers[$mod->modifier_id]->amount += $mod->effective_amount;
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
        /*
            Innanzitutto, qui sospendo l'esecuzione delle callback sui movimenti
            contabili. Nella fase di revisione della prenotazione capita che i
            relativi movimenti di pagamento vengano aggiunti, modificati o
            rimossi, ma considerando che tutto quel che viene calcolato a
            partire da questa funzione viene poi distrutto non val la pena stare
            ad effettuare tutti i calcoli sui saldi
        */
        App::make('MovementsHub')->setSuspended(true);

        for ($i = 0; $i <= 3; $i++) {
            /*
                Se viene sollevata una eccezione, questo intero blocco viene
                reiterato almeno 3 volte. Questo per eventualmente aggirare
                problemi di lock sul database, considerando anche che sta tutto
                in transazioni.
                Per scrupolo ad ogni iterazione svuoto la cache dei modelli, che
                resta in RAM, per evitare che i risultati delle iterazioni
                precedenti vadano ad interferire
            */
            Artisan::call('modelCache:clear');

            DB::beginTransaction();

            try {
                $bookings = [];
                $delivering = $request['action'] != 'booked';

                $ret = (object) [
                    'bookings' => [],
                ];

                $orders = $aggregate->orders()->with(['products', 'products.measure', 'bookings', 'modifiers'])->get();
                $user = $this->testAccess($target_user, $orders, $delivering);

                foreach($orders as $order) {
                    $order->setRelation('aggregate', $aggregate);
                    $booking = $this->handleBookingUpdate($request, $user, $order, $target_user, $delivering);

                    if ($booking) {
                        $ret->bookings[$booking->id] = $this->translateBooking($booking, $delivering);
                    }
                }

                /*
                    Lo scopo di questa funzione è ottenere una preview dei
                    totali della prenotazione, dunque al termine invalido tutte
                    le modifiche fatte sul database
                */
                DB::rollback();

                return $ret;
            }
            catch(\Exception $e) {
                DB::rollback();

                if ($i == 3) {
                    throw $e;
                }
            }
        }

        return null;
    }
}
