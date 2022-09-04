<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthException;
use App\Services\MovementTypesService;

class MovementTypesController extends BackedController
{
    public function __construct(MovementTypesService $service)
    {
        $this->commonInit([
            'reference_class' => 'App\\MovementType',
            'service' => $service
        ]);
    }

    public function show($id)
    {
        try {
            $type = $this->service->show($id);
            return view('movementtypes.edit', ['type' => $type]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }
}
