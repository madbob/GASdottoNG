<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use DB;
use URL;
use Auth;

use App\Services\BookingsService;
use App\Services\DynamicBookingsService;
use App\Printers\AggregateBooking as Printer;

use App\User;
use App\Aggregate;

class BookingUserController extends Controller
{
	private $booking_service;
	private $dynamic_service;

    public function __construct(BookingsService $booking_service, DynamicBookingsService $dynamic_service)
    {
        $this->middleware('auth');
        $this->booking_service = $booking_service;
        $this->dynamic_service = $dynamic_service;
    }

    /*
        Questo è il pannello per la gestione delle consegne
    */
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
        if ($extended == 'true') {
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
        $target_user = User::find($user_id);
        $aggregate = Aggregate::findOrFail($aggregate_id);

        $this->booking_service->bookingUpdate($request->all(), $aggregate, $target_user, false);

        $user = $request->user();

        if ($target_user->id != $user->id && $target_user->isFriend() && $target_user->parent_id == $user->id) {
            /*
                Ho effettuato una prenotazione per un amico
            */
            return $this->successResponse([
                'id' => $aggregate->id,
                'header' => $target_user->printableFriendHeader($aggregate),
                'url' => route('booking.user.show', ['booking' => $aggregate->id, 'user' => $target_user->id]),
            ]);
        }
        else {
            /*
                Ho effettuato una prenotazione per me o per un utente di primo
                livello (non un amico)
            */
            return $this->successResponse([
                'id' => $aggregate->id,
                'header' => $aggregate->printableUserHeader(),
                'url' => route('booking.show', $aggregate->id),
            ]);
        }
    }

    /*
        Cfr. DynamicBookingsService::dynamicModifiers()
    */
    public function dynamicModifiers(Request $request, $aggregate_id, $user_id)
    {
        $user = User::find($user_id);
        $aggregate = Aggregate::findOrFail($aggregate_id);
        $ret = $this->dynamic_service->dynamicModifiers($request->all(), $aggregate, $user);
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
        $printer = new Printer();
        $aggregate = Aggregate::findOrFail($aggregate_id);
        $booking = $aggregate->bookingBy($user_id);
        return $printer->document($booking, '', $request->all());
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
                'url' => route('booking.user.show', ['booking' => $aggregate_id, 'user' => $user_id])
            ]);
        }
        else {
            $subject = $aggregate->bookingBy($user_id);

            return response()->json([
                'id' => $subject->id,
                'header' => $subject->printableHeader(),
                'url' => route('booking.user.show', ['booking' => $aggregate_id, 'user' => $user_id]),
            ]);
        }
    }
}
