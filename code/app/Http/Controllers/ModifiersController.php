<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Modifier;
use App\Order;

use App\Services\ModifiersService;
use App\Exceptions\AuthException;

class ModifiersController extends BackedController
{
    public function __construct(ModifiersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Modifier',
            'endpoint' => 'modifiers',
            'service' => $service
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $modifier = $this->service->show($id);
            if (is_null($modifier)) {
                abort(404);
            }

            return view('modifier.show', ['modifier' => $modifier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $modifier = $this->service->show($id);
            return view('modifier.edit', ['modifier' => $modifier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function strings()
    {
        return response()->json(\App\View\Texts\Modifier::descriptions());
    }

    public function postFeedback(Request $request, $id)
    {
        try {
            $ret = [];
            $modifier = $this->service->show($id);

            /*
                Se il modificatore appartiene ad un fornitore, il quale ha
                attualmente degli ordini aperti per i quali lo stesso
                modificatore non è attivo, viene proposto all'utente di
                agganciarli a questi
            */
            if ($modifier->target_type == 'App\Supplier' && ($modifier->active || $modifier->always_on)) {
                $to_be_attached = false;

                foreach ($modifier->target->active_orders as $order) {
                    $to_be_attached = true;

                    foreach ($order->modifiers()->where('modifier_type_id', $modifier->modifier_type_id)->get() as $m) {
                        $to_be_attached = ($m->active == false) && $to_be_attached;
                    }

                    if ($to_be_attached) {
                        break;
                    }
                }

                if ($to_be_attached) {
                    $ret[] = route('modifiers.fixorderattach', $id);
                }
            }

            return response()->json($ret);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function getFixOrderAttach(Request $request, $id)
    {
        try {
            $modifier = $this->service->show($id);
            return view('modifier.fixorders', ['modifier' => $modifier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function postFixOrderAttach(Request $request, $id)
    {
        try {
            $modifier = $this->service->show($id);
            $activated = array_unique($request->input('activated', []));

            foreach($activated as $activate) {
                $order = Order::find($activate);

                if ($order && $order->supplier_id == $modifier->target_id && $request->user()->can('supplier.orders', $order->supplier)) {
                    foreach ($order->modifiers()->where('modifier_type_id', $modifier->modifier_type_id)->get() as $m) {
                        $m->delete();
                    }

                    $new_mod = $modifier->replicate();
                    $new_mod->target_id = $order->id;
                    $new_mod->target_type = get_class($order);
                    $new_mod->save();
                }
            }

            return $this->successResponse();
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }
}
