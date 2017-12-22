<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Theme;

use App\Product;
use App\Measure;

class MeasuresController extends Controller
{
    public function __construct()
    {
        $this->commonInit([
            'reference_class' => 'App\\Measure'
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        if ($user->can('measures.admin', $user->gas) == false) {
            abort(503);
        }

        $measures = Measure::orderBy('name', 'asc')->get();

        return Theme::view('measures.edit', ['measures' => $measures]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('measures.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $measure = new Measure();
        $measure->name = $request->input('name');
        $measure->discrete = $request->input('discrete', false) ? true : false;
        $measure->save();

        return $this->successResponse([
            'id' => $measure->id,
            'discrete' => $measure->discrete,
            'name' => $measure->name,
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('measures.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $ids = $request->input('id', []);
        $new_names = $request->input('name', []);
        $new_discretes = $request->input('discrete', []);
        $saved_ids = [];

        for ($i = 0; $i < count($ids); ++$i) {
            $id = $ids[$i];
            $name = $new_names[$i];

            if ($id == '') {
                $measure = new Measure();
            } else {
                $measure = Measure::find($id);
                $measure->discrete = (array_search($id, $new_discretes) !== false);
            }

            $measure->name = $name;
            $measure->save();

            $saved_ids[] = $measure->id;
        }

        $deleted = Measure::whereNotIn('id', $saved_ids)->get();
        foreach ($deleted as $del) {
            Product::where('measure_id', '=', $del->id)->update(['measure_id' => null]);
            $del->delete();
        }

        return $this->successResponse();
    }

    public function listProducts(Request $request, $id)
    {
        $measure = Measure::findOrFail($id);
        return Theme::view('measures.products-list', ['products' => $measure->products]);
    }
}
