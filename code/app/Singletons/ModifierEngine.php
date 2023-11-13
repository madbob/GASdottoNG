<?php

namespace App\Singletons;

use App\ModifiedValue;

use Log;

class ModifierEngine
{
	private function applicationOffsets($booking)
	{
		if ($booking->status == 'pending') {
            $quantity_attribute = 'quantity';
            $price_attribute = 'price';
			$weight_attribute = 'weight';
        }
        else {
            $quantity_attribute = 'delivered';
            $price_attribute = 'price_delivered';
			$weight_attribute = 'weight_delivered';
        }

		return [$quantity_attribute, $price_attribute, $weight_attribute];
	}

    private function applyDefinition($booking, $modifier, $amount, $definition, $target)
    {
		list($quantity_attribute, $price_attribute, $weight_attribute) = $this->applicationOffsets($booking);
        $reference_quantity = 1;

        if ($modifier->applies_target == 'product') {
            $reference_quantity = $target->$quantity_attribute;
        }

        if ($modifier->value == 'percentage') {
			$amount = round($amount * ($definition->amount / 100), 4);
        }
        else if ($modifier->value == 'absolute') {
            $amount = $reference_quantity * $definition->amount;
        }
		else if ($modifier->value == 'mass') {
			/*
				Per i calcoli "a peso" si applicano sempre valori assoluti, mai
				percentuali
			*/
			$amount = ($reference_quantity * $definition->amount) * $target->$weight_attribute;
		}
        else {
            /*
                Per i modificatori che incidono sul prezzo del prodotti
                ($modifier->value = 'apply') faccio la differenza tra il prezzo
                normale ed il prezzo modificato
            */
            $amount = round($target->$price_attribute - ($target->$quantity_attribute * $definition->amount), 4);
        }

        return $amount;
    }

    private function retrieveExistingValue($modifier, $obj_mod_target)
    {
        if ($obj_mod_target) {
            $modifier_value = $obj_mod_target->modifiedValues->firstWhere('modifier_id', $modifier->id);
            if (is_null($modifier_value)) {
                $modifier_value = new ModifiedValue();
                $obj_mod_target->modifiedValues->push($modifier_value);
            }
        }
        else {
            $modifier_value = new ModifiedValue();
        }

        $modifier_value->setRelation('modifier', $modifier);
        return $modifier_value;
    }

    private function targetDefinition($modifier, $value)
    {
        if ($modifier->scale == 'minor') {
            foreach($modifier->definitions as $def) {
                if ($value < $def->threshold) {
                    return $def;
                }
            }
        }
        else if ($modifier->scale == 'major') {
            foreach($modifier->definitions as $def) {
                if ($value > $def->threshold) {
                    return $def;
                }
            }
        }

        return null;
    }

    private function handlingAttributes($booking, $modifier)
    {
        /*
            Fintantoché l'ordine non è marcato come "consegnato" uso le quantità
            prenotate come riferimento per i calcoli (sulle soglie o per la
            distribuzione dei costi sulle prenotazioni).
            Se poi, alla fine, le quantità consegnate non corrispondono con
            quelle prenotate, e dunque i calcoli devono essere revisionati per
            ridistribuire in modo corretto il tutto, allora uso come riferimento
            le quantità realmente consegnate: tale ricalcolo viene invocato da
            OrdersController::postFixModifiers(), previa conferma dell'utente,
            quando l'ordine è davvero in stato "consegnato"
        */
        if ($booking->status != 'pending') {
            switch($modifier->applies_type) {
                case 'none':
                case 'quantity':
                    $attribute = 'delivered';
                    break;
                case 'price':
                    $attribute = 'price_delivered';
                    break;
                case 'weight':
                    $attribute = 'weight_delivered';
                    break;
                default:
                    $attribute = '';
                    break;
            }

            $mod_attribute = 'price_delivered';
        }
        else {
            $attribute = $modifier->applies_type;
            if ($attribute == 'none') {
                $attribute = 'price';
            }

            $mod_attribute = 'price';
        }

        return [$attribute, $mod_attribute];
    }

    private function saveValue($modifier, $obj_mod_target, $altered_amount)
    {
        $modifier_value = $this->retrieveExistingValue($modifier, $obj_mod_target);

        /*
            Se alla fine il modificatore non modifica nulla, lo ignoro (e ne
            elimino il valore esistente, se c'è)
        */
        if ($altered_amount == 0) {
            if ($modifier_value->exists) {
                $modifier_value->delete();
            }

            return null;
        }

        $modifier_value->modifier_id = $modifier->id;
        $modifier_value->amount = $altered_amount;

        if ($obj_mod_target) {
            /*
                Ci sono casi in cui il soggetto non è salvato sul database,
                e.g. se c'è una prenotazione per un amico, ed il suo utente
                principale non ha effettuato prenotazioni, se viene
                artificiosamente creata una nuova e vuota
                (cfr. Order::topLevelBookings())
            */
            if ($obj_mod_target->exists == false) {
                $obj_mod_target->save();
            }

            $modifier_value->target_type = get_class($obj_mod_target);
            $modifier_value->target_id = $obj_mod_target->id;
            $modifier_value->save();
        }

        return $modifier_value;
    }

    public function apply($modifier, $booking, $aggregate_data)
    {
        if ($modifier->active == false) {
            return null;
        }

		if (is_null($modifier->target)) {
			\Log::debug('Modificatore senza oggetto di riferimento: ' . $modifier->id);
            return null;
        }

        if (!isset($aggregate_data->orders[$booking->order_id])) {
            return null;
        }

        $product_target_id = 0;

        if ($modifier->target_type == 'App\Product') {
            $product_target_id = $modifier->target_id;

            switch($modifier->applies_target) {
                case 'order':
                    $check_target = $aggregate_data->orders[$booking->order_id]->products[$product_target_id] ?? null;
                    break;

                default:
                    $check_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id]->products[$product_target_id] ?? null;
                    break;
            }
        }
        else {
            switch($modifier->applies_target) {
                case 'order':
                    $check_target = $aggregate_data->orders[$booking->order_id] ?? null;
                    break;

                case 'booking':
                    $check_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id] ?? null;
                    break;

                case 'product':
                    $check_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id]->products[$modifier->target->id] ?? null;
                    break;

                default:
                    Log::error('applies_target non riconosciuto per modificatore: ' . $modifier->applies_target);
                    return null;
            }
        }

        switch($modifier->applies_target) {
            case 'order':
                $mod_target = $aggregate_data->orders[$booking->order_id] ?? null;
                $obj_mod_target = $booking;
                break;

            case 'booking':
                $mod_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id] ?? null;
                $obj_mod_target = $booking;
                break;

            case 'product':
                $product_target_id = $modifier->target->id;
                $mod_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id]->products[$product_target_id] ?? null;
                $obj_mod_target = $booking->products()->where('product_id', $product_target_id)->first();
                break;

            default:
				Log::error('applies_target non riconosciuto per modificatore: ' . $modifier->applies_target);
                return null;
        }

        list($attribute, $mod_attribute) = $this->handlingAttributes($booking, $modifier);
        $check_value = $check_target->$attribute ?? 0;
        $target_definition = null;
        $altered_amount = null;

        if ($check_value == 0) {
            $altered_amount = 0;
        }
        else {
            $target_definition = $this->targetDefinition($modifier, $check_value);

            if (is_null($target_definition) == false) {
                $altered_amount = $this->applyDefinition($booking, $modifier, $mod_target->$mod_attribute ?? 0, $target_definition, $check_target);

                /*
                    Se il modificatore è applicato su un ordine, qui applico alla
                    singola prenotazione il suo valore relativo e proporzionale.
                */
                if ($modifier->applies_target == 'order') {
                    $distribution_attribute = $modifier->distribution_type;
                    if ($distribution_attribute == 'none') {
                        $distribution_attribute = 'price';
                    }

                    $distribution_attribute = 'relative_' . $distribution_attribute;

                    if ($modifier->target_type == 'App\Product') {
                        $booking_mod_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id]->products[$product_target_id] ?? null;
                        $reference = $mod_target->products[$product_target_id]->$distribution_attribute;
                    }
                    else {
                        $booking_mod_target = $aggregate_data->orders[$booking->order_id]->bookings[$booking->id] ?? null;
                        $reference = $mod_target->$distribution_attribute;
                    }

                    if ($booking_mod_target && $reference) {
                        $altered_amount = round(($booking_mod_target->$distribution_attribute * $altered_amount) / $reference, 4);
                    }
                    else {
                        $altered_amount = 0;
                    }
                }
            }
            else {
                $modifier_value = $this->retrieveExistingValue($modifier, $obj_mod_target);
                if ($modifier_value->exists) {
                    $modifier_value->delete();
                }

                return null;
            }
        }

        return $this->saveValue($modifier, $obj_mod_target, $altered_amount);
    }
}
