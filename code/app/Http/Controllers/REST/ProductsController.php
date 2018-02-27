<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Response;

use App\Services\ProductsService;
use App\Http\Controllers\REST\BackedController;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class ProductsController extends BackedController
{
    public function __construct(ProductsService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Product',
            'endpoint' => 'products',
            'service' => $service,
            'json_wrapper' => 'product',
        ]);
    }
}
