<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use MadBob\Larastrap\Integrations\LarastrapStack;

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
        $mt = LarastrapStack::autoreadSave($request, ModifierType::class);
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
