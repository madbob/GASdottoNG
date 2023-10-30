<?php

namespace App\Services;

use App\Exceptions\IllegalArgumentException;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

use App\Models\Concerns\CreditableTrait;
use App\Movement;
use App\User;
use App\Balance;
use App\Currency;
use App\Supplier;

class MovementsService extends BaseService
{
    public function list($request)
    {
        /*
            TODO sarebbe assai più efficiente usare with('sender') e
            with('target'), ma poi la relazione in Movement si spacca (cambiando
            in virtù del tipo di oggetto linkato). Sarebbe opportuno introdurre
            un'altra relazione espressamente dedicata ai tipi di oggetto
            soft-deletable
        */
        $query = Movement::orderBy('date', 'desc');
        $own_movements = false;
        $currentuser = Auth::user();

        if (isset($request['startdate'])) {
            $start = decodeDate($request['startdate']);
        }
        else {
            $start = date('Y-m-d', strtotime('-1 weeks'));
        }

        if (!empty($start)) {
            $query->where('date', '>=', $start);
        }

        if (isset($request['enddate'])) {
            $end = decodeDate($request['enddate']);
        }
        else {
            $end = date('Y-m-d');
        }

        if (!empty($end)) {
            $query->where('date', '<=', $end);
        }

        if (isset($request['type']) && $request['type'] != 'none') {
            $query->where('type', $request['type']);
        }

        if (isset($request['method']) && $request['method'] != 'none') {
            $query->where('method', $request['method']);
        }

        if (isset($request['user_id']) && !empty($request['user_id']) && $request['user_id'] != '0') {
            $user_id = $request['user_id'];
            if ($user_id == $currentuser->id) {
                $own_movements = true;
            }

            $generic_target = User::find($user_id);
            if ($generic_target) {
                $query = $generic_target->queryMovements($query);
            }
        }

        if (isset($request['supplier_id']) && $request['supplier_id'] != '0') {
            $supplier_id = $request['supplier_id'];
            $generic_target = Supplier::withTrashed()->find($supplier_id);
            if ($generic_target) {
                $query = $generic_target->queryMovements($query);
            }
        }

        if (isset($request['currency_id']) && $request['currency_id'] != '0') {
            $query->where('currency_id', $request['currency_id']);
        }

        if (isset($request['generic_target_id']) && $request['generic_target_id'] != '0') {
            $target_id = $request['generic_target_id'];
            $target_type = $request['generic_target_type'];

            if ($target_type == User::class && $target_id == $currentuser->id) {
                $own_movements = true;
            }

            $generic_target = $target_type::tFind($target_id);

            if ($generic_target) {
                $query = $generic_target->queryMovements($query);
            }
        }

        /*
            Gli utenti devono poter accedere ai propri movimenti, ma non a tutti
            gli altri. Se viene fatta una richiesta esplicita per l'utente
            corrente la prendo sempre per buona, altrimenti ne controllo i
            permessi prima di procedere oltre
        */
        if ($own_movements == false) {
            $this->ensureAuth(['movements.admin' => 'gas', 'movements.view' => 'gas']);
        }

        if (isset($request['amountstart']) && !empty($request['amountstart']) && $request['amountstart'] != '0') {
            $query->where('amount', '>=', $request['amountstart']);
        }

        if (isset($request['amountend']) && !empty($request['amountend']) && $request['amountend'] != '0') {
            $query->where('amount', '<=', $request['amountend']);
        }

        return $query->with(['currency'])->get();
    }

    public function show($id)
    {
        /*
            Nota bene: in lettura gli accessi sono più complicati che in
            scrittura, ad esempio ogni utente può vedere i dettagli del
            pagamento della sua quota.
            TODO Definire delle regole
        */
        $this->ensureAuth();
        $movement = Movement::findOrFail($id);
        return $movement;
    }

    private function setCommonAttributes($movement, $request)
    {
        $user = Auth::user();
        $movement->registration_date = date('Y-m-d G:i:s');
        $movement->registerer_id = $user->id;
        $this->transformAndSetIfSet($movement, $request, 'date', 'decodeDate');
        $this->setIfSet($movement, $request, 'sender_type');
        $this->setIfSet($movement, $request, 'sender_id');
        $this->setIfSet($movement, $request, 'target_type');
        $this->setIfSet($movement, $request, 'target_id');
        $this->setIfSet($movement, $request, 'amount');
        $this->setIfSet($movement, $request, 'currency_id');
        $this->setIfSet($movement, $request, 'method');
        $this->setIfSet($movement, $request, 'type');
        $this->setIfSet($movement, $request, 'identifier');
        $this->setIfSet($movement, $request, 'notes');

        if ($movement->method == 'integralces') {
            $currency = Currency::where('context', 'integralces')->first();
            $movement->currency_id = $currency->id;
        }

        $movement->parseRequest($request);

        return $movement;
    }

    private function testAuth($type)
    {
        /*
            TODO Questo non prende in considerazione l'effettivo fornitore su
            cui si sta agendo, e se si hanno i permessi o meno.
            Sarebbe meglio spostare queste regole nella classi in Params
        */
        switch($type) {
            case 'deposit-pay':
            case 'deposit-return':
            case 'annual-fee':
                $this->ensureAuth(['movements.admin' => 'gas', 'users.admin' => 'gas', 'users.movements' => 'gas']);
                break;

            case 'booking-payment':
                $this->ensureAuth(['movements.admin' => 'gas', 'supplier.shippings' => null]);
                break;

            case 'order-payment':
                $this->ensureAuth(['movements.admin' => 'gas', 'supplier.orders' => null]);
                break;

            default:
                $this->ensureAuth(['movements.admin' => 'gas']);
                break;
        }
    }

    public function store(array $request)
    {
        $this->testAuth($request['type']);

        return DB::transaction(function() use ($request) {
            $movement = new Movement();
            $movement = $this->setCommonAttributes($movement, $request);
            $movement->save();

            if ($movement->saved == false) {
                throw new IllegalArgumentException(_i('Salvataggio fallito'));
            }

            return $movement;
        });
    }

    public function update($id, array $request)
    {
        return DB::transaction(function() use ($id, $request) {
            $movement = Movement::findOrFail($id);
            $this->testAuth($movement->type);
            $movement = $this->setCommonAttributes($movement, $request);
            $movement->save();

            if ($movement->saved == false)
                throw new IllegalArgumentException(_i('Salvataggio fallito'));

            return $movement;
        });
    }

    private function recalculateCurrentBalance()
    {
        $this->ensureAuth(['movements.admin' => 'gas']);

        DB::transaction(function() {
            $current_date = date('Y-m-d H:i:s');
            $index = 0;

            do {
                $movements = Movement::where('archived', false)->take(100)->offset(100 * $index)->get();
                if ($movements->count() == 0) {
                    break;
                }

                foreach($movements as $m) {
                    $m->updated_at = $current_date;
                    $m->save();
                }

                unset($movements);
                $index++;

            } while(true);
        });
    }

    public function recalculate()
    {
        $this->ensureAuth(['movements.admin' => 'gas']);
        $hub = App::make('MovementsHub');

        try {
            return DB::transaction(function() use ($hub) {
                $hub->setRecalculating(true);
                $current_status = resetAllCurrentBalances();
                $this->recalculateCurrentBalance();
                $hub->setRecalculating(false);
                $diffs = CreditableTrait::compareBalances($current_status);
                return $diffs;
            });
        }
        catch(\Exception $e) {
            Log::error('Errore nel ricalcolo saldi: ' . $e->getMessage());
            $hub->setRecalculating(false);
            return null;
        }
    }

    public function closeBalance($request)
    {
        $this->ensureAuth(['movements.admin' => 'gas']);
        $hub = App::make('MovementsHub');

        try {
            $date = decodeDate($request['date']);

            return DB::transaction(function() use ($hub, $date) {
                $hub->setRecalculating(true);

                /*
                    Azzero tutti i saldi
                */
                resetAllCurrentBalances();

                /*
                    Ricalcolo i movimenti fino alla data desiderata
                */
                $current_date = date('Y-m-d');

                $index = 0;
                do {
                    $movements = Movement::where('date', '<', $date)->where('archived', false)->take(100)->offset(100 * $index)->get();
                    if ($movements->count() == 0) {
                        break;
                    }

                    foreach($movements as $m) {
                        $m->updated_at = $current_date;
                        $m->save();
                    }

                    unset($movements);
                    $index++;

                } while(true);

                /*
                    Archivio i movimenti più vecchi della data indicata
                */
                Movement::where('date', '<', $date)->where('archived', false)->update(['archived' => true]);

                /*
                    Duplico i saldi appena calcolati, e alle copie precedenti
                    assegno la data della chiusura del bilancio
                */
                duplicateAllCurrentBalances($date);

                /*
                    Ricalcolo i saldi correnti, che a questo punto saranno dalla
                    data di chiusura alla data corrente
                */
                $this->recalculateCurrentBalance();

                $hub->setRecalculating(false);
                return true;
            });
        }
        catch(\Exception $e) {
            Log::error('Errore nel ricalcolo saldi: ' . $e->getMessage());
            $hub->setRecalculating(false);
            return false;
        }
    }

	public function creditHistory($class, $date)
	{
		$currencies = Currency::enabled();
		$balances = Balance::whereDate('date', $date)->where('target_type', $class)->get();
		$ret = [];

		foreach($balances as $balance) {
			$target = $balance->target;
			if ($target) {
				$name = $target->printableName();
				$amounts = [];

				foreach($currencies as $currency) {
					$amounts[] = $target->retrieveBalanceAmount($currency, $date);
				}

				$ret[$name] = $amounts;
			}
		}

		ksort($ret, SORT_STRING | SORT_FLAG_CASE);
		return $ret;
	}

    public function deleteBalance($id)
    {
        $this->ensureAuth(['movements.admin' => 'gas']);
        $balance = Balance::find($id);
        $balance->delete();
        return true;
    }

    public function destroy($id)
    {
        $movement = DB::transaction(function () use ($id) {
            $this->ensureAuth(['movements.admin' => 'gas']);
            $movement = Movement::findOrFail($id);
            $movement->delete();
            return $movement;
        });

        return $movement;
    }
}
