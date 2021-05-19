<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use DB;
use URL;
use Auth;
use Log;
use PDF;

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

    public function dynamicModifiers(Request $request, $aggregate_id, $user_id)
    {
        $user = $request->user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->id != $user_id && $user->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        DB::beginTransaction();

        $bookings = [];
        $target_user = User::find($user_id);

        $ret = (object) [
            'bookings' => [],
        ];

        foreach($aggregate->orders as $order) {
            $order->waybackProducts();

            $booking = $this->readBooking($request, $order, $target_user, false);
            if ($booking) {
                $order->setRelation('aggregate', $aggregate);
                $booking->setRelation('order', $order);
                $bookings[] = $booking;
            }
        }

        foreach($bookings as $booking) {
            $modified = $booking->applyModifiers();

            $ret->bookings[$booking->id] = (object) [
                'total' => printablePrice($booking->getValue('effective', false)),
                'modifiers' => [],
                'products' => $booking->products->reduce(function($carry, $product) {
                    $carry[$product->product_id] = (object) [
                        'total' => $product->getValue('effective'),
                        'modifiers' => [],
                    ];
                    return $carry;
                }, []),
            ];

            foreach($modified as $mod) {
                if ($mod->target_type == 'App\Product') {
                    if (!isset($ret->bookings[$booking->id]->products[$mod->target->product_id]->modifiers[$mod->modifier_id])) {
                        $ret->bookings[$booking->id]->products[$mod->target->product_id]->modifiers[$mod->modifier_id] = (object) [
                            'label' => $mod->modifier->modifierType->name,
                            'amount' => 0,
                            'variable' => $mod->is_variable,
                            'passive' => ($mod->type == 'passive'),
                        ];
                    }

                    $ret->bookings[$booking->id]->products[$mod->target->product_id]->modifiers[$mod->modifier_id]->amount += $mod->effective_amount;
                }
                else {
                    if (!isset($ret->bookings[$booking->id]->modifiers[$mod->modifier_id])) {
                        $ret->bookings[$booking->id]->modifiers[$mod->modifier_id] = (object) [
                            'label' => $mod->modifier->modifierType->name,
                            'amount' => 0,
                            'variable' => $mod->is_variable,
                            'passive' => ($mod->type == 'passive'),
                        ];
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
