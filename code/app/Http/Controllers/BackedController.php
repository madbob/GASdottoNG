<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            \Log::debug('Errore autorizzazione: ' . $e->getMessage());
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            \Log::debug('Errore input: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
        catch (\Exception $e) {
            \Log::error('Errore non identificato: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            return $this->errorResponse(_i('Errore') . ': ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        return $this->easyExecute(function () use ($request) {
            $subject = $this->service->store($request->all());

            return $this->commonSuccessResponse($subject);
        });
    }

    public function update(Request $request, $id)
    {
        return $this->easyExecute(function () use ($request, $id) {
            $subject = $this->service->update($id, $request->except('_method', '_token'));

            return $this->commonSuccessResponse($subject);
        });
    }

    public function destroy($id)
    {
        return $this->easyExecute(function () use ($id) {
            $subject = $this->service->destroy($id);

            return $this->commonSuccessResponse($subject);
        });
    }
}
