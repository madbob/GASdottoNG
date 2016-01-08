<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;

use App\Measure;

class MeasuresController extends Controller
{
        public function store(Request $request)
        {
                DB::beginTransaction();

                $user = Auth::user();
                if ($user->gas->userHas('supplier.modify') == false)
                        return $this->errorResponse('Non autorizzato');

                $measure = new Measure();
                $measure->name = $request->input('name');
                $measure->save();

                return $this->successResponse([
			'id' => $measure->id,
			'name' => $measure->name
		]);
        }
}
