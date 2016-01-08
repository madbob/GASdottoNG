<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Theme;

use App\Gas;

class GasController extends Controller
{
        public function edit($id)
        {
                $gas = Gas::findOrFail($id);
                if ($gas->userCan('gas.config') == false)
                        abort(503);

                return Theme::view('pages.gas', ['gas' => $gas]);
        }

        public function update(Request $request, $id)
        {
                DB::beginTransaction();

                $gas = Gas::findOrFail($id);
                if ($gas->userCan('gas.config') == false)
                        return $this->errorResponse('Non autorizzato');

                $gas->name = $request->input('name');
                $gas->email = $request->input('email');
                $gas->description = $request->input('description');
                $gas->message = $request->input('message');
                $gas->save();

                return $this->successResponse();
        }

        public function destroy($id)
        {
        //
        }
}
