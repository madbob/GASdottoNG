<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Session;
use Cache;
use Auth;
use Log;

use App\Movement;

class PaymentController extends Controller
{
    private function initSatispayContext()
    {
        $user = Auth::user();
        if ($user == null)
            $gas = currentAbsoluteGas();
        else
            $gas = $user->gas;

        \SatispayOnline\Api::setSecurityBearer($gas->satispay['secret']);

        return $user;
    }

    public function doPayment(Request $request)
    {
        $type = $request->input('type');

        if ($type == 'satispay') {
            $user = self::initSatispayContext();

            $charge = null;

            $amount = $request->input('amount');
            $notes = $request->input('description');

            $phone = $request->input('mobile');
            $phone = str_replace('+', '00', $phone);
            $phone = preg_replace('/^[^0-9]$/', '', $phone);
            if (substr($phone, 0, 4) != '0039')
                $phone = '0039' . $phone;
            $phone = preg_replace('/^0039/', '+39', $phone);

            try {
                $satispay_user = \SatispayOnline\User::create([
                    'phone_number' => $phone
                ]);

                $charge = \SatispayOnline\Charge::create([
                    'user_id' => $satispay_user->id,
                    'currency' => 'EUR',
                    'amount' => $amount * 100,
                    'description' => $notes,
                    'callback_url' => urldecode(route('payment.status_satispay', ['charge_id' => '{uuid}']))
                ]);
            }
            catch(\Exception $e) {
                Log::error('Errore richiesta Satispay: ' . $e->getMessage());
                $charge = null;
            }

            /*
                Se la richiesta di pagamento va a buon fine, creo il relativo
                movimento contabile e lo parcheggio in cache. Quando ricevo la
                conferma da parte di Satispay, lo prelevo e lo salvo sul DB.
                cfr. self::statusPaymentSatispay()
            */
            if ($charge != null) {
                $movement = new Movement();
                $movement->identifier = $charge->uuid;
                $movement->type = 'user-credit';
                $movement->target_type = get_class($user);
                $movement->target_id = $user->id;
                $movement->registerer_id = $user->id;
                $movement->amount = $amount;
                $movement->date = date('Y-m-d');
                $movement->notes = $notes;
                $movement->method = 'satispay';

                Cache::put('satispay_movement_' . $charge->uuid, $movement, 16 * 60);
            }
        }

        return redirect()->route('profile', ['tab' => 'accounting']);
    }

    public function statusPaymentSatispay(Request $request)
    {
        self::initSatispayContext();

        $charge_id = $request->input('charge_id');
        $charge = \SatispayOnline\Charge::get($charge_id);

        if ($charge->status == 'SUCCESS') {
            $movement = Cache::pull('satispay_movement_' . $charge_id);
            if ($movement != null) {
                $movement->save();
            }
            else {
                Log::error('Richiesta Satispay non trovata in cache: ' . $charge_id);
            }
        }
    }
}
