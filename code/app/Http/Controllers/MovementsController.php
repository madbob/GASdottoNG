<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Theme;
use App\Movement;
use App\Aggregate;

class MovementsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function basicReadFromRequest($request)
    {
        $user = Auth::user();

        $id = $request->input('id', '');
        if (empty($id)) {
            $obj = new Movement();
        } else {
            $obj = Movement::find($id);
            if ($obj == null) {
                $obj = new Movement();
            }
        }

        $obj->date = decodeDate($request->input('date'));
        $obj->registration_date = date('Y-m-d G:i:s');
        $obj->registerer_id = $user->id;
        $obj->sender_type = $request->input('sender_type');
        $obj->sender_id = $request->input('sender_id');
        $obj->target_type = $request->input('target_type');
        $obj->target_id = $request->input('target_id');
        $obj->amount = $request->input('amount');
        $obj->method = $request->input('method');
        $obj->type = $request->input('type');
        $obj->identifier = $request->input('identifier');
        $obj->notes = $request->input('notes');
        $obj->parseRequest($request);

        return $obj;
    }

    public function index(Request $request)
    {
        $query = Movement::orderBy('registration_date', 'desc');

        if ($request->has('startdate')) {
            $start = decodeDate($request->input('startdate'));
            $filtered = true;
        }
        else {
            $start = date('Y-m-d', strtotime('-1 months'));
            $filtered = false;
        }

        $query->where('registration_date', '>=', $start);

        if ($request->has('enddate')) {
            $end = decodeDate($request->input('enddate'));
        }
        else {
            $end = date('Y-m-d');
        }

        $query->where('registration_date', '<=', $end);

        if ($request->input('type', 'none') != 'none') {
            $query->where('type', $request->input('type'));
        }

        if ($request->input('amountstart', '') != '') {
            $query->where('amount', '>=', $request->input('amountstart'));
        }

        if ($request->input('amountend', '') != '') {
            $query->where('amount', '<=', $request->input('amountend'));
        }

        $data['movements'] = $query->get();

        if ($filtered == false) {
            $data['balance'] = Auth::user()->gas->balances()->first();
            return Theme::view('pages.movements', $data);
        }
        else {
            return Theme::view('movement.list', $data);
        }
    }

    public function create(Request $request)
    {
        $type = $request->input('type');
        if ($type == 'none') {
            return '';
        }

        $metadata = Movement::types($type);
        $data = [];

        $payments = [];
        $all_payments = Movement::payments();
        foreach ($metadata->methods as $identifier => $info) {
            $payments[$identifier] = $all_payments[$identifier];
        }

        $data['payments'] = $payments;
        $data['fixed'] = $metadata->fixed_value;

        $data['sender_type'] = $metadata->sender_type;
        if ($metadata->sender_type != null) {
            $st = $metadata->sender_type;
            $data['senders'] = $st::all();
        } else {
            $data['senders'] = [];
        }

        $data['target_type'] = $metadata->target_type;
        if ($metadata->target_type != null) {
            if ($type == 'booking-payment') {
                $data['targets'] = Aggregate::getByStatus('archived', true);
                $data['target_type'] = 'App\Aggregate';
            } else {
                $tt = $metadata->target_type;
                $data['targets'] = $tt::all();
            }
        } else {
            $data['targets'] = [];
        }

        return Theme::view('movement.selectors', $data);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $m = $this->basicReadFromRequest($request);
        $m->save();

        if ($m->saved == false) {
            return $this->errorResponse('Salvataggio fallito');
        } else {
            $printable_date = $m->printableDate('registration_date');
            return $this->successResponse([
                'id' => $m->id,
                'registration_date' => $printable_date,
                'printable_text' => $printable_date . ' <span class="glyphicon ' . $m->payment_icon . '" aria-hidden="true"></span>'
            ]);
        }
    }
}
