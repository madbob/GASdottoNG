<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Group;

class GroupsService extends BaseService
{
    public function show($id)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        return Group::findOrFail($id);
    }

    public function store(array $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $g = new Group();
        $this->setIfSet($g, $request, 'name');
        $g->save();

        return $g;
    }

    /*
        Quando un gruppo cambia contesto, esplicitamente sgancio le relazioni
        con gli altri contesti sul DB.
        Questo per evitare il caso - tutt'altro che improbabile - che venga
        ad esempio creato un gruppo da assegnare agli utenti, tutti abbiano
        assegnata la relativa cerchia di default, e questo gruppo poi cambi di
        destinazione per essere usato per le prenotazioni, salvo apparire
        impropriamente anche negli utenti
    */
    private function detachMassive($group, $table)
    {
        $circles = $group->circles->pluck('id')->toArray();
        DB::table($table)->whereIn('circle_id', $circles)->delete();
    }

    public function update($id, array $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $g = Group::findOrFail($id);
        $this->setIfSet($g, $request, 'name');
        $this->setIfSet($g, $request, 'context', 'user');

        switch ($g->context) {
            case 'user':
                $this->setIfSet($g, $request, 'cardinality');
                $this->boolIfSet($g, $request, 'filters_orders');
                $this->boolIfSet($g, $request, 'user_selectable');
                $this->detachMassive($g, 'booking_circle');
                break;

            case 'booking':
                $g->cardinality = 'single';
                $g->filters_orders = false;
                $g->user_selectable = true;
                $this->detachMassive($g, 'circle_user');
                break;

            case 'order':
                $g->cardinality = 'many';
                $g->filters_orders = false;
                $g->user_selectable = true;
                $this->detachMassive($g, 'circle_user');
                $this->detachMassive($g, 'booking_circle');
                break;
        }

        $g->save();

        return $g;
    }

    public function destroy($id)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        DB::beginTransaction();
        $g = $this->show($id);
        $g->delete();
        DB::commit();

        return $g;
    }
}
