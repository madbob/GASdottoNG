<?php

namespace App\Services;

use DB;

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

        $vr = new VatRate();
        $this->setIfSet($vr, $request, 'name');
        $this->setIfSet($vr, $request, 'percentage');
        $vr->save();

        return $vr;
    }

    public function update($id, array $request)
    {
        $this->ensureAuth(['gas.config' => 'gas']);

        $vr = VatRate::findOrFail($id);
        $this->setIfSet($vr, $request, 'name');
        $this->setIfSet($vr, $request, 'percentage');
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
