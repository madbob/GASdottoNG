<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;

use App\Services\VatRatesService;
use App\Http\Controllers\REST\BackedController;

class VatRatesController extends BackedController
{
    public function __construct(VatRatesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\VatRate',
            'endpoint' => 'vatrates',
            'service' => $service,
            'json_wrapper' => 'vatrate',
        ]);
    }
}
