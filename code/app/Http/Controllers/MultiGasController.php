<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\Gas;

use App\Services\MultiGasService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class MultiGasController extends Controller
{
    public function __construct(MultiGasService $service)
    {
        $this->service = $service;

        $this->commonInit([
            'reference_class' => 'App\\Gas',
            'service' => $service
        ]);
    }

    public function index()
    {
        return view('pages.multigas', ['groups' => $this->service->list()]);
    }

    public function store(Request $request)
    {
        try {
            $gas = $this->service->store($request->all());

            return $this->successResponse([
                'id' => $gas->id,
                'name' => $gas->printableName(),
                'header' => $gas->printableHeader(),
                'url' => route('multigas.show', $gas->id)
            ]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function show($id)
    {
        try {
            $gas = $this->service->show($id);
            return view('multigas.edit', ['gas' => $gas]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $gas = $this->service->update($id, $request->all());
            return $this->commonSuccessResponse($gas);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        return $this->successResponse();
    }

    public function attach(Request $request)
    {
        $this->service->attach($request->all());
        return $this->successResponse();
    }

    public function detach(Request $request)
    {
        $this->service->detach($request->all());
        return $this->successResponse();
    }

    public function goTo($id)
    {
        $user = Auth::user();
        $gas = Gas::findOrFail($id);

        if ($user->can('gas.multi', $gas) == false) {
            abort(503);
        }

        $user->gas_id = $id;
        $user->save();
        return redirect('/');
    }
}
