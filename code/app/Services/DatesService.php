<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use Auth;
use Log;
use Artisan;
use DB;

use App\Date;

class DatesService extends BaseService
{
    public function list($target = null, $editable = false, $types = [])
    {
        $user = $this->ensureAuth(['supplier.orders' => null]);
        $query = Date::where('type', '!=', 'internal')->orderBy('date', 'asc');

        if ($target != null) {
            $query->where('target_type', get_class($target))->where('target_id', $target->id);
        }

        if ($editable == true) {
            $suppliers = $user->targetsByAction('supplier.orders');
            $query->where('target_type', 'App\Supplier')->whereIn('target_id', array_keys($suppliers));
        }

        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        return $query->get();
    }

    public function show($id)
    {
        return Date::findOrFail($id);
    }

    /*
        Questa funzione gestisce sia l'aggiornamento collettivo delle date
        relative ai fornitori che l'aggiornamento di una singola data "interna"
        editata dal pannello delle notifiche
    */
    public function update($id, array $request)
    {
        if ($id == 0) {
            $user = $this->ensureAuth(['supplier.orders' => null]);
            $suppliers = array_keys($user->targetsByAction('supplier.orders'));

            $ids = $request['id'] ?? [];
            $targets = $request['target_id'] ?? [];
            $dates = $request['date'] ?? [];
            $recurrings = $request['recurring'] ?? [];
            $descriptions = $request['description'] ?? [];
            $types = $request['type'] ?? [];

            $saved_ids = [];

            $generic_types = array_keys(Date::types());

            foreach($ids as $index => $id) {
                if (in_array($targets[$index], $suppliers) == false)
                    continue;

                if (empty($id)) {
                    $date = new Date();
                }
                else {
                    $date = Date::find($id);
                    if (is_null($date)) {
                        $date = new Date();
                    }
                }

                $date->target_type = 'App\Supplier';
                $date->target_id = $targets[$index];
                $date->date = null;
                $date->recurring = '';

                if (!empty($dates[$index])) {
                    $date->date = decodeDate($dates[$index]);
                }

                if (empty($date->date) && !empty($recurrings[$index])) {
                    $date->recurring = json_encode(decodePeriodic($recurrings[$index]));
                }

                if (empty($date->date) && empty($date->recurring)) {
                    continue;
                }

                $date->description = $descriptions[$index];
                $date->type = $types[$index];
                $date->save();

                $saved_ids[] = $date->id;
            }

            Date::whereIn('type', $generic_types)->whereIn('target_id', $suppliers)->whereNotIn('id', $saved_ids)->delete();
            return null;
        }
        else {
            $this->ensureAuth(['notifications.admin' => 'gas']);
            $date = Date::findOrFail($id);
            $date->date = decodeDate($request['date']);
            $date->description = $request['description'];
            $date->save();
            return $date;
        }
    }

    /*
        Salva la configurazione per gli ordini automatici
    */
    public function updateOrders(array $request)
    {
        $user = $this->ensureAuth(['supplier.orders' => null]);
        $suppliers = array_keys($user->targetsByAction('supplier.orders'));

        $ids = $request['id'];
        $targets = $request['target_id'];
        $recurrings = $request['recurring'];
        $ends = $request['end'];
        $shippings = $request['shipping'];
        $comments = $request['comment'];
        $suspends = $request['suspend'] ?? [];

        $saved_ids = [];

        foreach($ids as $index => $id) {
            if (in_array($targets[$index], $suppliers) == false || empty($recurrings[$index])) {
                continue;
            }

            if (empty($id)) {
                $date = new Date();
            }
            else {
                $date = Date::find($id);
            }

            $date->target_type = 'App\Supplier';
            $date->target_id = $targets[$index];
            $date->date = null;
            $date->recurring = json_encode(decodePeriodic($recurrings[$index]));

            $date->description = json_encode([
                'end' => $ends[$index],
                'shipping' => $shippings[$index],
                'comment' => $comments[$index],
                'suspend' => in_array($id, $suspends) ? 'true' : 'false',
            ]);

            $date->type = 'order';
            $date->save();

            $saved_ids[] = $date->id;
        }

        Date::where('type', 'order')->whereIn('target_id', $suppliers)->whereNotIn('id', $saved_ids)->delete();

        /*
            Quando vengono salvati gli ordini automatici, controllo se c'è
            qualcosa da aprire subito
        */
        Artisan::call('open:orders');

        return null;
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        $date = $this->show($id);
        $this->ensureAuth(['notifications.admin' => 'gas']);
        $date->delete();
        DB::commit();
        return $date;
    }
}
