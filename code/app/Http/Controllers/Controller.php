<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use DB;
use Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $reference_class;

    protected function commonInit($parameters)
    {
        $this->reference_class = $parameters['reference_class'];
    }

    protected function errorResponse($message, $target = '')
    {
        $ret = (object) [
            'status' => 'error',
            'target' => $target,
            'message' => $message,
        ];

        DB::rollback();

        return json_encode($ret);
    }

    protected function successResponse($data = [])
    {
        $data['status'] = 'success';
        DB::commit();

        return json_encode((object) $data);
    }

    protected function commonSuccessResponse($obj)
    {
        if ($obj) {
            $response = [
                'id' => $obj->id,
                'name' => $obj->printableName(),
                'header' => $obj->printableHeader(),
                'url' => $obj->exists ? $obj->getShowURL() : '',
            ];
        }
        else {
            $response = [];
        }

        return $this->successResponse($response);
    }

    public function objhead(Request $request, $id)
    {
        try {
            $class = $this->reference_class;
            $subject = $class::tFind($id);

            if ($subject) {
                return response()->json([
                    'id' => $subject->id,
                    'header' => $subject->printableHeader(),
                    'url' => $subject->getShowURL(),
                ]);
            }
        }
        catch (\Exception $e) {
            Log::error('Unable to generate object header: ' . $this->reference_class . ' / ' . $id);
        }

        abort(404);
    }

    /*
        Controparte della funzione JS collectFilteredUsers(), questa funzione ne
        deserializza il contenuto
    */
    protected function collectedFilteredUsers($request)
    {
        $users = $request->input('users', []);

        if (is_array($users)) {
            return $users;
        }
        else {
            return explode(',', $users);
        }
    }
}
