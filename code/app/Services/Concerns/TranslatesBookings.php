<?php

/*
    Lo scopo di questa classe è serializzare un Booking nel formato usato
    durante la valutazione dinamica di prenotazioni e consegne sul client,
    dunque essenzialmente viene usata da DynamicBookingsService.
    La funzione viene però usata anche da BookingsService allo scopo di iterare
    i prodotti inclusi nel Booking e convalidarne le quantità secondo i
    constraints definiti, prima dell'effettivo salvataggio; se qualcosa non
    torna viene sollevata una eccezione che spacca il processo, a mo' di misura
    preventiva considerando che questi controlli dovrebbero comunque essere già
    stati fatti da DynamicBookingsService
*/

namespace App\Services\Concerns;

use App\Exceptions\InvalidQuantityConstraint;
use App\Exceptions\AnnotatedQuantityConstraint;

trait TranslatesBookings
{
    protected $break_on_contraint = true;

    protected function handleQuantity($delivering, $product, $subject, $variant)
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

        $attribute = $delivering ? 'delivered' : 'quantity';
        $quantity = $subject->$attribute;

        try {
            $final_quantity = $product->testConstraints($quantity, $variant, $delivering);
            $message = '';
        }
        catch (InvalidQuantityConstraint $e) {
            $final_quantity = 0;
            $message = $e->getMessage();

            if ($this->break_on_contraint) {
                throw $e;
            }
            else {
                $subject->$attribute = 0;
                $subject->save();
            }
        }
        catch (AnnotatedQuantityConstraint $e) {
            $final_quantity = $quantity;
            $message = $e->getMessage();
        }

        return [$final_quantity, $message];
    }

    private function reduceVariants($product, $delivering)
    {
        return $product->variants->reduce(function ($varcarry, $variant) use ($product, $delivering) {
            [$final_variant_quantity, $variant_message] = $this->handleQuantity($delivering, $product, $variant, $variant);
            $combo = $variant->variantsCombo();

            $varcarry[] = (object) [
                'components' => $variant->components->reduce(function ($componentcarry, $component) {
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

    private function optionalTranslate($booking, $ret, $delivering)
    {
        $booking->unsetRelation('products');
        $ret->total = printablePrice($booking->getValue('effective', false, true));

        $booking->status = $delivering ? 'shipped' : 'pending';
        $modified = $booking->applyModifiers(null, false);
        foreach ($modified as $mod) {
            if (! isset($ret->modifiers[$mod->modifier_id])) {
                $ret->modifiers[$mod->modifier_id] = $this->initDynamicModifier($mod);
            }

            $ret->modifiers[$mod->modifier_id]->amount += $mod->effective_amount;
        }

        return $ret;
    }

    protected function translateBooking($booking, $delivering, $full_translate)
    {
        $ret = (object) [
            'modifiers' => [],
            'products' => $booking->products->reduce(function ($carry, $product) use ($booking, $delivering) {
                $product->setRelation('booking', $booking);
                [$final_quantity, $message] = $this->handleQuantity($delivering, $product, $product, null);

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

        if ($full_translate) {
            $ret = $this->optionalTranslate($booking, $ret, $delivering);
        }

        return $ret;
    }
}
