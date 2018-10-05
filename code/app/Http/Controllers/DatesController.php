<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\Services\DatesService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class DatesController extends BackedController
{
    public function __construct(DatesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Date',
            'endpoint' => 'dates',
            'service' => $service
        ]);
    }

    public function index()
    {
        try {
            $user = Auth::user();
            $dates = $this->service->list();
            return view('dates.edit', ['dates' => $dates]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }
}
