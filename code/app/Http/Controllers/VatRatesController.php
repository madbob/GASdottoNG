<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\VatRatesService;

class VatRatesController extends BackedController
{
    public function __construct(VatRatesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\VatRate',
            'service' => $service
        ]);
    }

    public function show($id)
    {
        return $this->easyExecute(function() use ($id) {
            $vr = $this->service->show($id);
            return view('vatrates.edit', ['vatrate' => $vr]);
        });
    }
}
