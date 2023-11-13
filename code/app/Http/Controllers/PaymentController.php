<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Movement;

class PaymentController extends Controller
{
    private function initSatispayContext()
    {
        $user = Auth::user();
        if ($user == null) {
            $gas = currentAbsoluteGas();
        }
        else {
            $gas = $user->gas;
        }

        \SatispayGBusiness\Api::setPublicKey($gas->satispay['public']);
        \SatispayGBusiness\Api::setPrivateKey($gas->satispay['secret']);
        \SatispayGBusiness\Api::setKeyId($gas->satispay['key']);

        return $user;
    }

    public function doPayment(Request $request)
    {
        $type = $request->input('type');

        if ($type == 'satispay') {
            $user = $this->initSatispayContext();

            $charge = null;

            $amount = $request->input('amount');
            $notes = $request->input('description');

            $phone = $request->input('mobile');
            $phone = str_replace('+', '00', $phone);
            $phone = preg_replace('/^[^0-9]$/', '', $phone);

            if (substr($phone, 0, 4) != '0039') {
                $phone = '0039' . $phone;
            }

            $phone = preg_replace('/^0039/', '+39', $phone);

            try {
                $satispay_user = \SatispayGBusiness\Consumer::get($phone);

                $charge = \SatispayGBusiness\Payment::create([
                    'flow' => 'MATCH_USER',
                    'consumer_uid' => $satispay_user->id,
                    'currency' => 'EUR',
                    'amount_unit' => $amount * 100,
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
                $movement->identifier = $charge->id;
                $movement->type = 'user-credit';
                $movement->target_type = get_class($user);
                $movement->target_id = $user->id;
                $movement->registerer_id = $user->id;
                $movement->amount = $amount;
                $movement->date = date('Y-m-d');
                $movement->notes = $notes;
                $movement->method = 'satispay';

                Cache::put('satispay_movement_' . $charge->id, $movement, 16 * 60);
            }
        }

        return redirect()->route('profile', ['tab' => 'accounting']);
    }

    public function statusPaymentSatispay(Request $request)
    {
        $this->initSatispayContext();

        $charge_id = $request->input('payment_id');
        $charge = \SatispayGBusiness\Payment::get($charge_id);

        if ($charge->status == 'ACCEPTED') {
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
