<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;

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

        $measures = Measure::where('id', '!=', Measure::defaultValue())->orderBy('name', 'asc')->get();

        return view('measures.edit', ['measures' => $measures]);
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

        $saved_ids = [
            Measure::defaultValue()
        ];

        for ($i = 0; $i < count($ids); ++$i) {
            $name = trim($new_names[$i]);
            if (empty($name))
                continue;

            $id = $ids[$i];
            $measure = Measure::find($id);
            if (is_null($measure)) {
                $measure = new Measure();
            }

            $measure->discrete = (array_search($id, $new_discretes) !== false);
            $measure->name = $name;
            $measure->save();

            $saved_ids[] = $measure->id;
        }

        Product::whereNotIn('measure_id', $saved_ids)->withTrashed()->update(['measure_id' => Measure::defaultValue()]);
        Measure::whereNotIn('id', $saved_ids)->delete();

        return $this->successResponse();
    }

    public function discretes()
    {
        $measures = Measure::all();
        $ret = (object) [];

        foreach($measures as $m) {
            $mid = $m->id;
            $ret->$mid = $m->discrete;
        }

        return response()->json($ret);
    }
}
