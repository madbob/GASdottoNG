<?php

namespace App\Services;

use DB;

use App\ModifierType;

class ModifierTypesService extends BaseService
{
    public function show($id)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        return ModifierType::findOrFail($id);
    }

    public function store(array $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $mt = new ModifierType();
        $this->setIfSet($mt, $request, 'name');
        $this->setIfSet($mt, $request, 'classes', []);
        $mt->save();

        return $mt;
    }

    public function update($id, array $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $mt = ModifierType::findOrFail($id);
        $this->setIfSet($mt, $request, 'name');
        $this->setIfSet($mt, $request, 'classes', []);
        $mt->save();

        return $mt;
    }

    public function destroy($id)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $mt = DB::transaction(function () use ($id) {
            $mt = $this->show($id);
            $mt->delete();

            return $mt;
        });

        return $mt;
    }
}
