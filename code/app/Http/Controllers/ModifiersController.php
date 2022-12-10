<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Modifier;
use App\Order;

use App\Services\ModifiersService;

class ModifiersController extends BackedController
{
    public function __construct(ModifiersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Modifier',
            'service' => $service
        ]);
    }

    public function show($id)
    {
        return $this->easyExecute(function() use ($id) {
            $modifier = $this->service->show($id);
            if (is_null($modifier)) {
                abort(404);
            }

            return view('modifier.show', ['modifier' => $modifier]);
        });
    }

    public function edit($id)
    {
        return $this->easyExecute(function() use ($id) {
            $modifier = $this->service->show($id);
            return view('modifier.edit', ['modifier' => $modifier]);
        });
    }

    public function strings($target)
    {
        \Log::debug('Richieste stringhe per modificatori');
        $target = fromInlineId($target);
        return response()->json(\App\View\Texts\Modifier::descriptions($target));
    }

    public function postFeedback($id)
    {
        return $this->easyExecute(function() use ($id) {
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
        });
    }

    public function getFixOrderAttach($id)
    {
        return $this->easyExecute(function() use ($id) {
            $modifier = $this->service->show($id);
            return view('modifier.fixorders', ['modifier' => $modifier]);
        });
    }

    public function postFixOrderAttach(Request $request, $id)
    {
        return $this->easyExecute(function() use ($request, $id) {
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
        });
    }
}
