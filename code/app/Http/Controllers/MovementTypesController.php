<?php

namespace App\Http\Controllers;

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
        return $this->easyExecute(function() use ($id) {
            $type = $this->service->show($id);
            return view('movementtypes.edit', ['type' => $type]);
        });
    }
}
