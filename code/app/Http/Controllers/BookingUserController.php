<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use DB;
use URL;
use Auth;
use Log;
use PDF;
use Cache;

use App\User;
use App\Aggregate;

class BookingUserController extends BookingHandler
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $aggregate_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        if ($request->user()->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        return view('booking.list', ['aggregate' => $aggregate]);
    }

    public function show(Request $request, $aggregate_id, $user_id)
    {
        $user = User::findOrFail($user_id);
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->testUserAccess() == false && $request->user()->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        if (!is_null($request->user()->suspended_at)) {
            $required_mode = 'show';
            $extended = 'false';
        }
        else {
            $required_mode = $request->input('enforce', '');
            if (empty($required_mode)) {
                $required_mode = $aggregate->isRunning() ? 'edit' : 'show';
            }

            $extended = $request->input('extended', 'false');
        }

        /*
            $extended == true quando sto aprendo la prenotazione di un altro
            utente dall'apposito pannello, e devo rendere visibili sia la
            prenotazione dell'utente stesso che quelle degli amici
        */
        if ($extended == 'true' && $user->can('users.subusers')) {
            return view('booking.editwrap', ['aggregate' => $aggregate, 'user' => $user, 'standalone' => true, 'required_mode' => $required_mode]);
        }
        else {
            /*
                booking.edit o booking.show
            */
            return view('booking.' . $required_mode, ['aggregate' => $aggregate, 'user' => $user]);
        }
    }

    public function update(Request $request, $aggregate_id, $user_id)
    {
        return $this->bookingUpdate($request, $aggregate_id, $user_id, false);
    }

    private function initDynamicModifier($mod)
    {
        return (object) [
            'label' => $mod->descriptive_name,
            'url' => $mod->modifier->getROShowURL(),
            'amount' => 0,
            'variable' => $mod->is_variable,
            'passive' => ($mod->type == 'passive'),
        ];
    }

    /*
        Questa funzione viene invocata dai pannelli di prenotazione e consegna,
        ogni volta che viene apportata una modifica sulle quantità, e permette
        di controllare che le quantità immesse siano coerenti coi constraints
        imposti sui prodotti (quantità minima, quantità multipla...) e calcolare
        tutti i valori tenendo in considerazione tutti i modificatori esistenti.
        Eseguire tutti questi calcoli client-side in JS sarebbe complesso, e
        ridondante rispetto all'implementazione server-side che comunque sarebbe
        necessaria
    */
    public function dynamicModifiers(Request $request, $aggregate_id, $user_id)
    {
        $user = $request->user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->id != $user_id && $user->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        return app()->make('Locker')->execute('lock_aggregate_' . $aggregate_id, function() use ($request, $aggregate, $user, $user_id) {
            DB::beginTransaction();

            $bookings = [];
            $target_user = User::find($user_id);
            $delivering = $request->input('action') != 'booked';

            $ret = (object) [
                'bookings' => [],
            ];

            foreach($aggregate->orders as $order) {
                $booking = $this->readBooking($request, $order, $target_user, $delivering);
                if ($booking) {
                    $order->setRelation('aggregate', $aggregate);
                    $booking->setRelation('order', $order);

                    if ($delivering) {
                        $booking->status = 'shipped';
                        $booking->saveFinalPrices();
                    }
                    else {
                        $booking->status = 'pending';
                    }

                    $booking->save();
                    $bookings[] = $booking;
                }
            }

            foreach($bookings as $booking) {
                /*
                    Qui forzo sempre il ricalcolo dei modificatori, altrimenti
                    vengono letti quelli effettivamente salvati sul DB.
                    Nota bene: passo il parametro real = true perché qui sono
                    già all'interno di una transazione, ed i valori qui
                    calcolati devono esistere anche successivamente mentre
                    recupero i totali dei singoli prodotti.
                    La prenotazione è ancora in fase di consegna, lo status è
                    impostato temporaneamente a "shipped" ed andrebbe a leggere
                    quelli salvati anche se ancora non ce ne sono
                */
                $modified = $booking->calculateModifiers(null, true);

                $ret->bookings[$booking->id] = (object) [
                    'total' => printablePrice($booking->getValue('effective', false)),
                    'modifiers' => [],
                    'products' => $booking->products->reduce(function($carry, $product) use ($delivering) {
                        $carry[$product->product_id] = (object) [
                            'total' => printablePrice($product->getValue('effective')),

                            /*
                                Mentre computo il valore totale della prenotazione
                                in fase di modifica, controllo anche che le quantità
                                prenotate siano coerenti coi limiti imposti sul
                                prodotto prenotato (massimo, minimo,
                                disponibile...).
                                Lo faccio qui, server-side, per evitare problemi di
                                compatibilità client-side (è stato più volte
                                segnalato che su determinati browser mobile ci siano
                                problemi su questi controlli).
                                Ma solo se non sono in consegna: in quel caso è
                                ammesso immettere qualsiasi quantità
                            */
                            'quantity' => $delivering ? $product->delivered : $product->testConstraints($product->quantity),

                            'variants' => $product->variants->reduce(function($varcarry, $variant) use ($product, $delivering) {
                                $varcarry[] = (object) [
                                    'components' => $variant->components->reduce(function($componentcarry, $component) {
                                        $componentcarry[] = $component->value->id;
                                        return $componentcarry;
                                    }, []),

                                    'quantity' => $delivering ? $variant->delivered : $product->testConstraints($variant->quantity),
                                    'total' => printablePrice($delivering ? $variant->deliveredValue() : $variant->quantityValue()),
                                ];

                                return $varcarry;
                            }, []),

                            'modifiers' => [],
                        ];
                        return $carry;
                    }, []),
                ];

                foreach($modified as $mod) {
                    if ($mod->target_type == 'App\Product') {
                        if (!isset($ret->bookings[$booking->id]->products[$mod->target->product_id]->modifiers[$mod->modifier_id])) {
                            $ret->bookings[$booking->id]->products[$mod->target->product_id]->modifiers[$mod->modifier_id] = $this->initDynamicModifier($mod);
                        }

                        $ret->bookings[$booking->id]->products[$mod->target->product_id]->modifiers[$mod->modifier_id]->amount += $mod->effective_amount;
                    }
                    else {
                        if (!isset($ret->bookings[$booking->id]->modifiers[$mod->modifier_id])) {
                            $ret->bookings[$booking->id]->modifiers[$mod->modifier_id] = $this->initDynamicModifier($mod);
                        }

                        $ret->bookings[$booking->id]->modifiers[$mod->modifier_id]->amount += $mod->effective_amount;
                    }
                }
            }

            /*
                Lo scopo di questa funzione è ottenere una preview dei totali della
                prenotazione, dunque al termine invalido tutte le modifiche fatte
                sul database
            */
            DB::rollback();

            return response()->json($ret);
        });
    }

    public function destroy(Request $request, $aggregate_id, $user_id)
    {
        DB::beginTransaction();

        $user = $request->user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->id != $user_id && $user->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        foreach ($aggregate->orders as $order) {
            $booking = $order->userBooking($user_id);
            $booking->delete();
        }

        return $this->successResponse();
    }

    /*
        Questa funzione genera il "Dettaglio Consegne" per la prenotazione di
        uno specifico utente
    */
    public function document(Request $request, $aggregate_id, $user_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        $user = User::find($user_id);

        $bookings = [$aggregate->bookingBy($user_id)];
        foreach($user->friends as $friend) {
            $friend_booking = $aggregate->bookingBy($friend->id);
            if (!empty($friend_booking->bookings))
                $bookings[] = $friend_booking;
        }

        $names = [];
        foreach($aggregate->orders as $order) {
            $names[] = sprintf('%s %s', $order->supplier->name, $order->internal_number);
        }

        $names = join(' / ', $names);
        $filename = sanitizeFilename(_i('Dettaglio Consegne ordini %s.pdf', [$names]));

        $pdf = PDF::loadView('documents.personal_aggregate_shipping', [
            'aggregate' => $aggregate,
            'bookings' => $bookings,
        ]);

        return $pdf->download($filename);
    }

    /*
        Questa è la funzione che viene invocata per gli header delle
        prenotazioni nel pannello consegne (nome utente + icona dello stato)
    */
    public function objhead2(Request $request, $aggregate_id, $user_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);

        $user = User::findOrFail($user_id);
        if ($user->isFriend()) {
            return response()->json([
                'id' => $user->id,
                'header' => $user->printableFriendHeader($aggregate),
                'url' => URL::action('BookingUserController@show', ['booking' => $aggregate_id, 'user' => $user_id])
            ]);
        }
        else {
            $subject = $aggregate->bookingBy($user_id);

            return response()->json([
                'id' => $subject->id,
                'header' => $subject->printableHeader(),
                'url' => URL::action('BookingUserController@show', ['booking' => $aggregate_id, 'user' => $user_id])
            ]);
        }
    }
}
