<?php

namespace App\Http\Controllers;

use App\Services\CirclesService;

class CirclesController extends BackedController
{
    public function __construct(CirclesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Circle',
            'service' => $service,
        ]);
    }

    public function show($id)
    {
        return $this->easyExecute(function () use ($id) {
            return view('circles.edit', [
                'circle' => $this->service->show($id),
            ]);
        });
    }
}
