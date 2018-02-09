<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Response;

use App\Services\SuppliersService;
use App\Http\Controllers\REST\BackedController;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class SuppliersController extends BackedController
{
    public function __construct(SuppliersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Supplier',
            'endpoint' => 'suppliers',
            'service' => $service,
            'json_wrapper' => 'supplier',
        ]);
    }
}
