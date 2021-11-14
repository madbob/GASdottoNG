<?php

/*
    I Modifiers vengono creati, vuoti, nel momento in cui vengono acceduti, in
    ModifiableTrait::applicableModificationTypes()
    Motivo percui non esiste una esplicita funzione store()
*/

namespace App\Services;

use App\Exceptions\AuthException;

use Auth;
use Log;
use DB;

use App\Modifier;

class ModifiersService extends BaseService
{
    private function testAccess($modifier)
    {
        switch($modifier->target_type) {
            case 'App\Supplier':
                $this->ensureAuth(['supplier.modify' => $modifier->target]);
                break;

            case 'App\Product':
                $this->ensureAuth(['supplier.modify' => $modifier->target->supplier]);
                break;

            case 'App\Order':
                $this->ensureAuth(['supplier.modify' => $modifier->target->supplier, 'supplier.orders' => $modifier->target->supplier]);
                break;

            case 'App\Aggregate':
                $test = false;

                $user = Auth::user();
                foreach($modifier->target->orders as $order) {
                    if ($user->can('supplier.modify', $order->supplier) || $user->can('supplier.orders', $order->supplier)) {
                        $test = true;
                        break;
                    }
                }

                if ($test == false) {
                    throw new AuthException(403);
                }

                break;

            case 'App\Delivery':
                $this->ensureAuth(['gas.config' => 'gas']);
                break;
        }
    }

    public function show($id)
    {
        return Modifier::find($id);
    }

    public function update($id, array $request)
    {
        $modifier = $this->show($id);
        $user = $this->testAccess($modifier);

        $this->boolIfSet($modifier, $request, 'always_on');
        $this->setIfSet($modifier, $request, 'value');
        $this->setIfSet($modifier, $request, 'arithmetic');
        $this->setIfSet($modifier, $request, 'scale');
        $this->setIfSet($modifier, $request, 'applies_type');
        $this->setIfSet($modifier, $request, 'applies_target');
        $this->setIfSet($modifier, $request, 'distribution_type');

        $definition = [];

        if ($modifier->applies_type == 'none') {
            $amount = $request['simplified_amount'];

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

            $definition[] = (object) [
                'threshold' => $threshold,
                'amount' => $amount,
            ];
        }
        else {
            $thresholds = $request['threshold'];
            $amounts = $request['amount'];

            foreach($thresholds as $index => $threshold) {
                $threshold = trim($threshold);
                $amount = trim($amounts[$index]);

                if (empty($threshold)) {
                    $threshold = 0;
                }

                if (empty($amount)) {
                    $amount = 0;
                }

                if ($threshold == 0 && $amount == 0) {
                    continue;
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
        }

        $modifier->definition = json_encode($definition);
        $modifier->save();

        return $modifier;
    }

    public function destroy($id)
    {
        $modifier = DB::transaction(function() use ($id) {
            $modifier = Modifier::find($id);
            $this->testAccess($modifier);
            $modifier->definition = '[]';
            $modifier->save();
        });

        return $modifier;
    }
}
