<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Theme;
use Log;
use Session;

use App\Movement;
use App\Balance;
use App\Aggregate;

class MovementsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Movement'
        ]);
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

        if ($request->input('user_id', '0') != '0') {
            $user_id = $request->input('user_id');
            $query->where(function($query) use ($user_id) {
                $query->where(function($query) use ($user_id) {
                    $query->where('sender_type', 'App\User')->where('sender_id', $user_id);
                })->orWhere(function($query) use ($user_id) {
                    $query->where('target_type', 'App\User')->where('target_id', $user_id);
                });
            });
        }

        if ($request->input('supplier_id', '0') != '0') {
            $supplier_id = $request->input('supplier_id');
            $query->where(function($query) use ($supplier_id) {
                $query->where(function($query) use ($supplier_id) {
                    $query->where('sender_type', 'App\Supplier')->where('sender_id', $supplier_id);
                })->orWhere(function($query) use ($supplier_id) {
                    $query->where('target_type', 'App\Supplier')->where('target_id', $supplier_id);
                });
            });
        }

        if ($request->input('generic_target_id', '0') != '0') {
            $target_id = $request->input('generic_target_id');
            $target_type = $request->input('generic_target_type');

            $query->where(function($query) use ($target_id, $target_type) {
                $query->where(function($query) use ($target_id, $target_type) {
                    $query->where('sender_type', $target_type)->where('sender_id', $target_id);
                })->orWhere(function($query) use ($target_id, $target_type) {
                    $query->where('target_type', $target_type)->where('target_id', $target_id);
                });
            });

            $data['main_target'] = $target_type::find($target_id);
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

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false && $user->can('movements.view', $user->gas) == false) {
            abort(503);
        }

        $movement = Movement::findOrFail($id);
        return Theme::view('movement.modal', ['obj' => $movement, 'editable' => true]);
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false && $user->can('movements.view', $user->gas) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $movement = Movement::findOrFail($id);
        $movement->delete();

        return $this->successResponse();
    }

    private function resetBalance($gas)
    {
        $latest = $gas->balances()->first();
        $new = $latest->replicate();

        $latest->date = date('Y-m-d G:i:s');
        $latest->save();

        $new->save();
    }

    public function recalculate(Request $request)
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        DB::beginTransaction();

        try {
            Session::put('movements-recalculating', true);

            $gas = $user->gas;
            $gas->balances()->first()->delete();
            $latest = $gas->balances()->first();
            $this->resetBalance($gas);

            $current_date = date('Y-m-d G:i:s');
            $movements = Movement::where('created_at', '>=', $latest->date)->get();

            foreach($movements as $m) {
                $m->updated_at = $current_date;
                $m->save();
            }
        }
        catch(\Exception $e) {
            Log::error('Errore nel ricalcolo saldi: ' . $e->getMessage());
        }

        Session::forget('movements-recalculating');
        DB::commit();
        return redirect(url('/movements'));
    }

    public function closeBalance(Request $request)
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        DB::beginTransaction();

        $gas = $user->gas;
        $this->resetBalance($gas);

        DB::commit();
        return redirect(url('/movements'));
    }
}
