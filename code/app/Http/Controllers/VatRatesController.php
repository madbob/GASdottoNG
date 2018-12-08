<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\VatRatesService;
use App\Exceptions\AuthException;

class VatratesController extends BackedController
{
    public function __construct(VatRatesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\VatRate',
            'endpoint' => 'vatrates',
            'service' => $service
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $vr = $this->service->show($id);
            return view('vatrates.edit', ['vatrate' => $vr]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }
}
