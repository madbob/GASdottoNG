<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;

use App\Category;

class CategoriesController extends Controller
{
        public function store(Request $request)
        {
                DB::beginTransaction();

                $user = Auth::user();
                if ($user->gas->userHas('supplier.modify') == false)
                        return $this->errorResponse('Non autorizzato');

                $category = new Category();
                $category->name = $request->input('name');
                $category->save();

                return $this->successResponse([
			'id' => $category->id,
			'name' => $category->name
		]);
        }
}
