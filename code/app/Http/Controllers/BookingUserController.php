<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use URL;
use Auth;
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

        if ($user->testUserAccess() == false && $request->user()->can('supplier.shippings', $aggregate) == false)
            abort(503);

        $required_mode = $request->input('enforce', '');
        if (empty($required_mode)) {
            $required_mode = $aggregate->isRunning() ? 'edit' : 'show';
        }

        return view('booking.' . $required_mode, ['aggregate' => $aggregate, 'user' => $user]);
    }

    public function update(Request $request, $aggregate_id, $user_id)
    {
        return $this->bookingUpdate($request, $aggregate_id, $user_id, false);
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

        $html = view('documents.personal_aggregate_shipping', [
            'aggregate' => $aggregate,
            'bookings' => $bookings,
        ])->render();

        $filename = sanitizeFilename(_i('Dettaglio Consegne ordini %s.pdf', [$names]));
        PDF::SetTitle(sprintf('Dettaglio Consegne ordini %s', $names));
        PDF::AddPage();
        PDF::writeHTML($html, true, false, true, false, '');
        PDF::Output($filename, 'D');
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
                'url' => URL::action('BookingUserController@show', ['aggregate' => $aggregate_id, 'user' => $user_id])
            ]);
        }
        else {
            $subject = $aggregate->bookingBy($user_id);

            return response()->json([
                'id' => $subject->id,
                'header' => $subject->printableHeader(),
                'url' => URL::action('BookingUserController@show', ['aggregate' => $aggregate_id, 'user' => $user_id])
            ]);
        }
    }
}
