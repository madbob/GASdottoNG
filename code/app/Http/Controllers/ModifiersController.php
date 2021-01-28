<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Modifier;
use App\ModifierType;

class ModifiersController extends Controller
{
    private function testAccess($user, $modifier)
    {
        $test = false;

        switch($modifier->target_type) {
            case 'App\Supplier':
                $test = $user->can('supplier.modify', $modifier->target);
                break;

            case 'App\Product':
                $test = $user->can('supplier.modify', $modifier->target->supplier);
                break;

            case 'App\Order':
                $test = $user->can('supplier.modify', $modifier->target->supplier) || $user->can('supplier.orders', $modifier->target->supplier);
                break;

            case 'App\Aggregate':
                foreach($modifier->target->orders as $order) {
                    if ($user->can('supplier.modify', $modifier->target->supplier) || $user->can('supplier.orders', $modifier->target->supplier)) {
                        $test = true;
                        break;
                    }
                }

                break;

            case 'App\Delivery':
                $test = $user->can('gas.config', $user->gas);
                break;
        }

        if ($test == false) {
            abort(503);
        }
    }

    public function show($id)
    {
        $modifier = Modifier::find($id);
        return view('modifier.show', ['modifier' => $modifier]);
    }

    public function edit(Request $request, $id)
    {
        $user = $request->user();
        $modifier = Modifier::find($id);
        $this->testAccess($user, $modifier);
        return view('modifier.edit', ['modifier' => $modifier]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $modifier = Modifier::find($id);
        $this->testAccess($user, $modifier);

        $modifier->value = $request->input('value');
        $modifier->arithmetic = $request->input('arithmetic');
        $modifier->scale = $request->input('scale');
        $modifier->applies_type = $request->input('applies_type');
        $modifier->applies_target = $request->input('applies_target');
        $modifier->distribution_target = $request->input('distribution_target');
        $modifier->distribution_type = $request->input('distribution_type');

        $definition = [];
        $thresholds = $request->input('threshold');
        $amounts = $request->input('amount');

        foreach($thresholds as $index => $threshold) {
            $threshold = trim($threshold);
            $amount = trim($amounts[$index]);

            if ($modifier->applies_type == 'none') {
                if (empty($amount)) {
                    $amount = 0;
                }

                if ($amount == 0) {
                    continue;
                }

                /*
                    Se non ho soglie, forzo comunque la soglia dell'unico valore
                    esistente al valore più estremo
                */
                if ($modifier->scale == 'minor') {
                    $threshold = PHP_INT_MAX;
                }
                else {
                    $threshold = PHP_INT_MIN;
                }
            }
            else {
                if (empty($threshold)) {
                    $threshold = 0;
                }

                if (empty($amount)) {
                    $amount = 0;
                }

                if ($threshold == 0 && $amount == 0) {
                    continue;
                }
            }

            $definition[] = (object) [
                'threshold' => $threshold,
                'amount' => $amount,
            ];
        }

        /*
            Mantengo le soglie ordinate secondo il canone più comodo per la
            successiva valutazione in Modifier::apply()
        */
        if ($modifier->scale == 'minor') {
            usort($definition, function($a, $b) {
                return $a->threshold <=> $b->threshold;
            });
        }
        else {
            usort($definition, function($a, $b) {
                return ($a->threshold <=> $b->threshold) * -1;
            });
        }

        $modifier->definition = json_encode($definition);
        $modifier->save();
        return $this->commonSuccessResponse($modifier);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $modifier = Modifier::find($id);
        $this->testAccess($user, $modifier);

        $modifier->definition = '[]';
        $modifier->save();
        return $this->commonSuccessResponse($modifier);
    }
}
