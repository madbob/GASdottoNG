<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use MadBob\Larastrap\Integrations\LarastrapStack;

use App\VatRate;

class VatRatesService extends BaseService
{
    public function show($id)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        return VatRate::findOrFail($id);
    }

    public function store(array $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);
        $vr = LarastrapStack::autoreadSave($request, VatRate::class);
        $vr->save();

        return $vr;
    }

    public function destroy($id)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $vr = DB::transaction(function () use ($id) {
            $vr = $this->show($id);

            foreach ($vr->products as $product) {
                $product->vat_rate_id = null;
                $product->save();
            }

            $vr->delete();

            return $vr;
        });

        return $vr;
    }
}
