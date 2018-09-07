<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Exception\PPConnectionException;

use Session;
use Cache;
use Auth;
use Log;

use App\Movement;

class PaymentController extends Controller
{
    private function initPayPalContext()
    {
        $user = Auth::user();
        $gas = $user->gas;

        $this->api_context = new ApiContext(new OAuthTokenCredential($gas->paypal['client_id'], $gas->paypal['secret']));
        $this->api_context->setConfig(array(
            'mode' => $gas->paypal['mode'],
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => false,
        ));

        return $user;
    }

    private function initSatispayContext()
    {
        $user = Auth::user();
        if ($user == null)
            $gas = currentAbsoluteGas();
        else
            $gas = $user->gas;

        \SatispayOnline\Api::setSecurityBearer($gas->satispay['secret']);
        \SatispayOnline\Api::setSandbox(true);

        return $user;
    }

    public function doPayment(Request $request)
    {
        $type = $request->input('type');

        if ($type == 'paypal') {
            $user = $this->initPayPalContext();
            $gas = $user->gas;

            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $item_1 = new Item();
            $item_1->setName(_i('Credito Utente %s', $gas->name))->setCurrency('EUR')->setQuantity(1)->setPrice($request->input('amount'));

            $item_list = new ItemList();
            $item_list->setItems(array($item_1));

            $amount = new Amount();
            $amount->setCurrency('EUR')->setTotal($request->input('amount'));

            $transaction = new Transaction();
            $transaction->setAmount($amount)->setItemList($item_list)->setDescription($request->input('description'));

            $redirect_urls = new RedirectUrls();
            $redirect_urls->setReturnUrl(route('payment.status_paypal'))->setCancelUrl(route('payment.status_paypal'));

            $payment = new Payment();
            $payment->setIntent('Sale')->setPayer($payer)->setRedirectUrls($redirect_urls)->setTransactions(array($transaction));

            try {
                $payment->create($this->api_context);
            }
            catch (PPConnectionException $e) {
                Log::error('Errore in connessione per transazione PayPal: ' . $e->getMessage());
            }

            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() == 'approval_url') {
                    $redirect_url = $link->getHref();
                    break;
                }
            }

            Session::put('paypal_payment_id', $payment->getId());

            if (isset($redirect_url)) {
                return redirect()->away($redirect_url);
            }

            Log::error('Errore sconosciuto in transazione PayPal');
        }
        else if ($type == 'satispay') {
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
                    'callback_url' => urldecode(route('payment.status_satispay'm ['charge_id' => '{uuid}']))
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

                Cache::put('satispay_movement_' . $charge->uuid, $movement, 16);
            }
        }

        return redirect()->route('profile', ['tab' => 'accounting']);
    }

    public function statusPaymentPaypal(Request $request)
    {
        $user = $this->initPayPalContext();

        $payment_id = Session::get('paypal_payment_id');
        Session::forget('paypal_payment_id');

        if (empty($request->get('PayerID')) || empty($request->get('token'))) {
            return redirect()->route('profile', ['tab' => 'accounting']);
        }

        $movement = Movement::where('identifier', $payment_id)->first();
        if ($movement != null) {
            return redirect()->route('profile', ['tab' => 'accounting']);
        }

        $payment = Payment::get($payment_id, $this->api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($request->get('PayerID'));

        $result = $payment->execute($execution, $this->api_context);

        if ($result->getState() == 'approved') {
            $amount = 0;
            $fee = 0;
            $notes = '';

            foreach($result->getTransactions() as $transaction) {
                $notes = $transaction->getDescription();

                $a = $transaction->getAmount();
                $amount += (float) $a->getTotal();

                foreach($transaction->getRelatedResources() as $resource) {
                    $fee += (float) $resource->getSale()->getTransactionFee()->getValue();
                }
            }

            $amount = $amount - $fee;

            if (!empty($notes))
                $notes .= "\n";
            $notes .= _i('Commissioni PayPal: %s', printablePriceCurrency($fee));

            $movement = new Movement();
            $movement->identifier = $payment_id;
            $movement->type = 'user-credit';
            $movement->target_type = get_class($user);
            $movement->target_id = $user->id;
            $movement->amount = $amount;
            $movement->date = date('Y-m-d');
            $movement->notes = $notes;
            $movement->method = 'paypal';
            $movement->save();
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
