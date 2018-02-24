<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Auth;
use DB;
use Theme;

use App\VatRate;

class VatratesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\VatRate'
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('gas.config', $user->gas) == false) {
            abort(503);
        }

        $vr = new VatRate();
        $vr->name = $request->input('name');
        $vr->percentage = $request->input('percentage');
        $vr->save();

        return $this->commonSuccessResponse($vr);
    }

    public function show($id)
    {
        $user = Auth::user();
        if ($user->can('gas.config', $user->gas) == false) {
            abort(503);
        }

        $vr = VatRate::findOrFail($id);
        return Theme::view('vatrates.edit', ['vatrate' => $vr]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('gas.config', $user->gas) == false) {
            abort(503);
        }

        $vr = Delivery::findOrFail($id);
        $vr->name = $request->input('name');
        $vr->percentage = $request->input('percentage');
        $vr->save();

        return $this->commonSuccessResponse($vr);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('gas.config', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $vr = VatRate::findOrFail($id);

        foreach($vr->products as $product) {
            $product->vat_rate_id = null;
            $product->save();
        }

        $vr->delete();

        return $this->successResponse();
    }
}
