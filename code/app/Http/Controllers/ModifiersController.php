<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Modifier;

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
            if (is_null($modifier)) {
                abort(404);
            }

            return view('modifier.show', ['modifier' => $modifier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $modifier = $this->service->show($id);
            return view('modifier.edit', ['modifier' => $modifier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function strings()
    {
        return response()->json(Modifier::descriptions());
    }
}
