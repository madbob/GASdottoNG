<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;

use App\Services\ProductsService;
use App\Http\Controllers\REST\BackedController;

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
