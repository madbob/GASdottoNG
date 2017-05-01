<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Auth;
use DB;
use Theme;

use App\User;
use App\Delivery;

class DeliveriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('gas.config', $user->gas) == false) {
            abort(503);
        }

        $d = new Delivery();
        $d->name = $request->input('name');
        $d->address = $request->input('address');
        $d->default = $request->has('default');

        if ($d->default) {
            Delivery::where('default', true)->update('default', false);
        }

        $d->save();

        return $this->successResponse([
            'id' => $d->id,
            'name' => $d->name,
            'header' => $d->printableHeader(),
            'url' => url('deliveries/' . $d->id),
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        if ($user->can('gas.config', $user->gas) == false) {
            abort(503);
        }

        $d = Delivery::findOrFail($id);
        return Theme::view('deliveries.edit', ['delivery' => $d]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('gas.config', $user->gas) == false) {
            abort(503);
        }

        $d = Delivery::findOrFail($id);
        $d->name = $request->input('name');
        $d->address = $request->input('address');
        $d->default = $request->has('default');

        if ($d->default) {
            Delivery::where('default', true)->update(['default' => false]);
        }

        $d->save();

        return $this->successResponse([
            'id' => $d->id,
            'header' => $d->printableHeader(),
            'url' => url('deliveries/' . $d->id),
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('gas.config', $user->gas) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $d = Delivery::findOrFail($id);

        $fallback_id = 0;

        if ($d->default) {
            $fallback = Delivery::where('id', '!=', $d->id)->orderBy(DB::raw('RAND()'))->first();
            if ($fallback != null)
                $fallback_id = $fallback->id;
        }
        else {
            $fallback = Delivery::where('default', true)->first();
            if ($fallback != null)
                $fallback_id = $fallback->id;
        }

        foreach($d->users as $u) {
            $u->preferred_delivery_id = $fallback_id;
            $u->save();
        }

        $d->delete();

        return $this->successResponse();
    }
}
