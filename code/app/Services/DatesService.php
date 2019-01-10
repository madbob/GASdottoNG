<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use Auth;
use Log;
use DB;

use App\Date;

class DatesService extends BaseService
{
    public function list($target = null, $editable = false)
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

            $ids = $request['id'];
            $targets = $request['target_id'];
            $dates = $request['date'];
            $descriptions = $request['description'];
            $types = $request['type'];

            $saved_ids = [];

            foreach($ids as $index => $id) {
                if (in_array($targets[$index], $suppliers) == false)
                    continue;

                if (empty($id))
                    $date = new Date();
                else
                    $date = Date::find($id);

                $date->target_type = 'App\Supplier';
                $date->target_id = $targets[$index];
                $date->date = decodeDate($dates[$index]);
                $date->description = $descriptions[$index];
                $date->type = $types[$index];
                $date->save();

                $saved_ids[] = $date->id;
            }

            Date::where('type', '!=', 'internal')->whereIn('target_id', $suppliers)->whereNotIn('id', $saved_ids)->delete();
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

    public function destroy($id)
    {
        $date = DB::transaction(function() use ($id) {
            $date = $this->show($id);
            $this->ensureAuth(['notifications.admin' => 'gas']);
            $date->delete();
            return $date;
        });

        return $date;
    }
}
