<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class BackedController extends Controller
{
    protected $service = null;
    protected $single_wrapper = '';
    protected $multiple_wrapper = '';

    protected function commonInit($parameters)
    {
        $this->service = $parameters['service'];
        $this->single_wrapper = $parameters['json_wrapper'];
        $this->multiple_wrapper = str_plural($parameters['json_wrapper']);
        parent::commonInit($parameters);
    }

    public function index()
    {
        try {
            $objs = $this->service->list();
            return response()->json([$this->multiple_wrapper => $objs], 200);
        }
        catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

    public function show($id)
    {
        try {
            $obj = $this->service->show($id);
            return response()->json([$this->single_wrapper => $obj], 200);
        }
        catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

    public function store(Request $request)
    {
        try {
            $obj = $this->service->store($request->all());
            return response()->json([$this->single_wrapper => $obj], 200);
        }
        catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $obj = $this->service->update($id, $request->all());
            return response()->json([$this->single_wrapper => $obj], 200);
        }
        catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->destroy($id);
            return $this->successResponse();
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }
}
