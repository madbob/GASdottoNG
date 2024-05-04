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

    public function update($id, array $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $g = Group::findOrFail($id);
        $this->setIfSet($g, $request, 'name');
        $this->setIfSet($g, $request, 'context', 'user');

        switch($g->context) {
            case 'user':
                $this->setIfSet($g, $request, 'cardinality');
                $this->boolIfSet($g, $request, 'filters_orders');
                $this->boolIfSet($g, $request, 'user_selectable');
                break;

            case 'booking':
                $g->cardinality = 'single';
                $g->filters_orders = false;
                $g->user_selectable = true;
                break;

            case 'order':
                $g->cardinality = 'many';
                $g->filters_orders = false;
                $g->user_selectable = true;
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
