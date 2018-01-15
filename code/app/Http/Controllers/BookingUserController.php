<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use URL;
use Auth;
use Theme;
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
        $aggregate = Aggregate::findOrFail($aggregate_id);
        if (Auth::user()->id != $user_id && $request->user()->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        $user = User::findOrFail($user_id);
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

    public function document(Request $request, $aggregate_id, $user_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        $subject = $aggregate->bookingBy($user_id);

        $names = [];
        foreach($aggregate->orders as $order) {
            $names[] = sprintf('%s %s', $order->supplier->name, $order->internal_number);
        }
        $names = join(' / ', $names);

        $html = Theme::view('documents.aggregate_shipping', [
            'aggregate' => $aggregate,
            'bookings' => [$subject]
        ])->render();

        $filename = sprintf('Dettaglio Consegne ordini %s.pdf', $names);
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
