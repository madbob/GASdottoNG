<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\ModifierTypesService;
use App\Exceptions\AuthException;

class ModifierTypesController extends BackedController
{
    public function __construct(ModifierTypesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\ModifierType',
            'endpoint' => 'modtypes',
            'service' => $service
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $mt = $this->service->show($id);
            return view('modifiertype.edit', ['modtype' => $mt]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }
}
