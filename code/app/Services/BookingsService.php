<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Exceptions\AuthException;

use Auth;
use DB;
use Log;

use App\BookedProductVariant;
use App\BookedProductComponent;
use App\ModifierType;
use App\ModifiedValue;

use App\Events\BookingDelivered;

class BookingsService extends BaseService
{
    protected function testAccess($target, $supplier, $delivering)
    {
        $user = Auth::user();

        if ($delivering) {
            if ($user->can('supplier.shippings', $supplier) == false) {
                throw new AuthException(403);
            }
        }
        else {
            if ($target->testUserAccess($user) == false && $user->can('supplier.shippings', $supplier) == false) {
                throw new AuthException(403);
            }
        }

        return $user;
    }

    private function initVariant($booked, $quantity, $delivering, $values)
    {
        if ($quantity == 0) {
            return null;
        }

        $bpv = new BookedProductVariant();
        $bpv->product_id = $booked->id;

        if ($delivering == false) {
            $bpv->quantity = $quantity;
            $bpv->delivered = 0;
        }
        else {
            $bpv->quantity = 0;
            $bpv->delivered = $quantity;
        }

        $bpv->save();

        foreach ($values as $variant_id => $value_id) {
            $bpc = new BookedProductComponent();
            $bpc->productvariant_id = $bpv->id;
            $bpc->variant_id = $variant_id;
            $bpc->value_id = $value_id;
            $bpc->save();
        }

        return $bpv;
    }

    private function findVariant($booked, $values, $saved_variants)
    {
        $query = BookedProductVariant::where('product_id', $booked->id);

        foreach ($values as $variant_id => $value_id) {
            $query->whereHas('components', function ($q) use ($variant_id, $value_id) {
                $q->where('variant_id', $variant_id)->where('value_id', $value_id);
            });
        }

        return $query->whereNotIn('id', $saved_variants)->first();
    }

    private function adjustVariantValues($values, $i)
    {
        $real_values = [];

        foreach($values as $variant_id => $vals) {
            if (isset($vals[$i]) && !empty($vals[$i])) {
                $real_values[$variant_id] = $vals[$i];
            }
        }

        return $real_values;
    }

    private function handlingParam($delivering) {
        if ($delivering == false) {
            return 'quantity';
        }
        else {
            return 'delivered';
        }
    }

    private function readVariants($product, $booked, $values, $quantities, $delivering)
    {
        $param = $this->handlingParam($delivering);
        $quantity = 0;
        $saved_variants = [];
        $param = $this->handlingParam($delivering);

        for ($i = 0; $i < count($quantities); ++$i) {
            $q = (float) $quantities[$i];

            $real_values = $this->adjustVariantValues($values, $i);
            if (empty($real_values)) {
                continue;
            }

            $bpv = $this->findVariant($booked, $real_values, $saved_variants);

            if (is_null($bpv)) {
                $bpv = $this->initVariant($booked, $q, $delivering, $real_values);
                if (is_null($bpv)) {
                    continue;
                }
            }
            else {
                if ($q == 0 && $delivering == false) {
                    $bpv->delete();
                    continue;
                }

                if ($bpv->$param != $q) {
                    $bpv->$param = $q;
                    $bpv->save();
                }
            }

            $saved_variants[] = $bpv->id;
            $quantity += $q;
        }

        /*
            Attenzione: in fase di consegna/salvataggio è lecito che una
            quantità sia a zero, ma ciò non implica eliminare la variante
        */
        if ($delivering == false) {
            BookedProductVariant::where('product_id', '=', $booked->id)->whereNotIn('id', $saved_variants)->delete();
        }

        /*
            Per ogni evenienza qui ricarico le varianti appena salvate, affinché
            il computo del prezzo totale finale per il prodotto risulti corretto
        */
        $booked->load('variants');

        return [$booked, $quantity];
    }

    private function readBooking(array $request, $order, $booking, $delivering)
    {
        $param = $this->handlingParam($delivering);

        if (isset($request['notes_' . $order->id])) {
            $booking->notes = $request['notes_' . $order->id] ?? '';
        }

        $existed_before = $booking->exists;
        $booking->save();

        $count_products = 0;
        $booked_products = new Collection();

        /*
            In caso di ordini chiusi ma con confezioni da completare, ci
            sono un paio di casi speciali...
            O sto prenotando tra i prodotti da completare, e dunque devo
            intervenire solo su di essi (nel form booking.edit viene
            aggiunto un campo nascosto "limited") senza intaccare le
            quantità già prenotate degli altri, oppure sono un
            amministratore e sto intervenendo sull'intera prenotazione
            (dunque posso potenzialmente modificare tutto).
        */
        if (isset($request['limited'])) {
            $products = $order->status == 'open' ? $order->products : $order->pendingPackages();
        }
        else {
            $products = $order->products;
        }

        foreach ($products as $product) {
            /*
                $booking->getBooked() all'occorrenza crea un nuovo
                BookedProduct, che deve essere salvato per potergli agganciare
                le varianti.
                Ma se la quantità è 0 (e bisogna badare che lo sia in caso di
                varianti che senza varianti) devo evitare di salvare tale
                oggetto temporaneo, che andrebbe solo a complicare le cose nel
                database
            */

            $quantity = (float) ($request[$product->id] ?? 0);
            if (empty($quantity)) {
                $quantity = 0;
            }

            $quantities = [];

            if ($product->variants->isEmpty() == false) {
                $quantities = $request['variant_quantity_' . $product->id] ?? '';
                if (empty($quantities)) {
                    continue;
                }
            }

            $booked = $booking->getBooked($product, true);

            if ($quantity != 0 || !empty($quantities)) {
                $booked->save();

                if ($product->variants->isEmpty() == false) {
                    $values = [];
                    foreach ($product->variants as $variant) {
                        if (isset($request['variant_selection_' . $variant->id])) {
                            $values[$variant->id] = $request['variant_selection_' . $variant->id];
                        }
                    }

                    list($booked, $quantity) = $this->readVariants($product, $booked, $values, $quantities, $delivering);
                }
            }

            if ($delivering == false && $quantity == 0) {
                $booked->delete();
            }
            else {
                if ($booked->$param != 0 || $quantity != 0) {
                    $booked->$param = $quantity;
                    $booked->save();

                    $count_products++;
                    $booked_products->push($booked);
                }
            }
        }

        /*
            Attenzione: se sto consegnando, e tutte le quantità sono a 0,
            comunque devo preservare i dati della prenotazione (se esistono).
            Va anche contemplato il caso in cui sto consegnando un ordine
            aggregato e l'utente non ha partecipato a qualcuno degli ordini; in
            tal caso, la sua prenotazione vuota non va salvata
        */
        if (($delivering == false || $existed_before == false) && $booking->products()->count() == 0) {
            $booking->delete();
            return null;
        }
        else {
            $booking->setRelation('products', $booked_products);
            return $booking;
        }
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

    protected function handlePreProcess($request, $booking)
    {
        $manual_total = $this->deliveringManualTotal($request, $booking->order);
        if ($manual_total > 0) {
            $booking->enforceTotal($manual_total);
        }

        return $booking;
    }

    protected function handlePostProcess($request, $booking)
    {
        $manual_total = $this->deliveringManualTotal($request, $booking->order);

        if ($manual_total > 0) {
            $manual_adjust_modifier = ModifierType::find('arrotondamento-consegna');
            $modifier = $booking->order->modifiers()->where('modifier_type_id', $manual_adjust_modifier->id)->first();
            if (is_null($modifier)) {
                $modifier = $booking->order->attachEmptyModifier($manual_adjust_modifier);
            }

            $booking->modifiedValues()->whereHas('modifier', function($query) use ($manual_adjust_modifier) {
                $query->where('modifier_type_id', $manual_adjust_modifier->id);
            })->delete();

            $modifier_value = new ModifiedValue();
            $modifier_value->modifier_id = $modifier->id;
            $modifier_value->target_type = get_class($booking);
            $modifier_value->target_id = $booking->id;
            $modifier_value->amount = $manual_total - $booking->getValue('effective', false, true);
            $modifier_value->save();

            $booking->unsetRelation('modifiedValues');
        }
    }

    public function handleBookingUpdate($request, $user, $order, $target_user, $delivering)
    {
        /*
            - recupero la prenotazione
            - resetto lo stato dei pagamenti e dei modificatori. Questo per evitare di dover gestire gli aggiornamenti: ricalcolo daccapo tutto
            - aggiorno i contenuti della prenotazione
            - ricalcolo i modificatori
            - gestisco i modificatori esterni (gli arrotondamenti sulle consegne manuali)
        */

        $booking = $order->userBooking($target_user);
        $booking->wipeStatus();
        $booking = $this->handlePreProcess($request, $booking);
        $booking = $this->readBooking($request, $order, $booking, $delivering);

        if ($booking && $delivering) {
            BookingDelivered::dispatch($booking, $request['action'], $user);
            $this->handlePostProcess($request, $booking);
        }

        return $booking;
    }

    public function bookingUpdate(array $request, $aggregate, $target_user, $delivering)
    {
        DB::transaction(function() use ($request, $aggregate, $target_user, $delivering) {
            foreach($aggregate->orders()->with(['products', 'bookings', 'modifiers'])->get() as $order) {
                $user = $this->testAccess($target_user, $order->supplier, $delivering);
                $this->handleBookingUpdate($request, $user, $order, $target_user, $delivering);
            }
        }, 3);
    }
}
