<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;

use App\Services\SuppliersService;
use App\Http\Controllers\REST\BackedController;

class SuppliersController extends BackedController
{
    public function __construct(SuppliersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Supplier',
            'service' => $service,
            'json_wrapper' => 'supplier',
        ]);
    }
}
