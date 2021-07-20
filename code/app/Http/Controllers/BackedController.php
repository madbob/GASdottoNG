<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class BackedController extends Controller
{
    protected $service = null;

    protected function commonInit($parameters)
    {
        $this->service = $parameters['service'];
        parent::commonInit($parameters);
    }

    public function ensureAuth($permissions = [], $or = true)
    {
        return $this->service->ensureAuth($permissions, $or);
    }

    public function store(Request $request)
    {
        try {
            $subject = $this->service->store($request->all());
            return $this->commonSuccessResponse($subject);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $subject = $this->service->update($id, $request->except('_method', '_token'));
            return $this->commonSuccessResponse($subject);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
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
