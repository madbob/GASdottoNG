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
    public function list($target = null)
    {
        $this->ensureAuth(['supplier.orders' => null]);
        $query = Date::orderBy('date', 'asc');

        if ($target != null)
            $query->where('target_type', get_class($target))->where('target_id', $target->id);

        return $query->get();
    }

    public function update($useless, array $request)
    {
        $this->ensureAuth(['supplier.orders' => null]);

        $ids = $request['id'];
        $targets = $request['target_id'];
        $dates = $request['date'];
        $descriptions = $request['description'];
        $types = $request['type'];

        $saved_ids = [];

        foreach($ids as $index => $id) {
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

        Date::whereNotIn('id', $saved_ids)->delete();
        return null;
    }
}
