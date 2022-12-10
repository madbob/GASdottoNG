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

    protected function easyExecute($func)
    {
        try {
            return $func();
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
        catch (\Exception $e) {
            return $this->errorResponse(_i('Errore'));
        }
    }

    public function store(Request $request)
    {
        return $this->easyExecute(function() use ($request) {
            $subject = $this->service->store($request->all());
            return $this->commonSuccessResponse($subject);
        });
    }

    public function update(Request $request, $id)
    {
        return $this->easyExecute(function() use ($request, $id) {
            $subject = $this->service->update($id, $request->except('_method', '_token'));
            return $this->commonSuccessResponse($subject);
        });
    }

    public function destroy($id)
    {
        return $this->easyExecute(function() use ($id) {
            $this->service->destroy($id);
            return $this->successResponse();
        });
    }
}
