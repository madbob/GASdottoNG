<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\ModifiersService;
use App\Exceptions\AuthException;

class ModifiersController extends BackedController
{
    public function __construct(ModifiersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Modifier',
            'endpoint' => 'modifiers',
            'service' => $service
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $modifier = $this->service->show($id);
            return view('modifier.show', ['modifier' => $modifier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }
}
