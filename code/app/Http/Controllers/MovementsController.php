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
use App\MovementType;
use App\Balance;
use App\User;
use App\Supplier;
use App\Aggregate;
use App\CreditableTrait;

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
            $generic_target = User::find($user_id);
            $query = $generic_target->queryMovements($query);
        }

        if ($request->input('supplier_id', '0') != '0') {
            $supplier_id = $request->input('supplier_id');
            $generic_target = Supplier::find($supplier_id);
            $query = $generic_target->queryMovements($query);
        }

        if ($request->input('generic_target_id', '0') != '0') {
            $target_id = $request->input('generic_target_id');
            $target_type = $request->input('generic_target_type');
            $generic_target = $target_type::find($target_id);
            $query = $generic_target->queryMovements($query);
            $data['main_target'] = $generic_target;
            $bilist = true;
        }
        else {
            $bilist = false;
        }

        if ($request->input('amountstart', '0') != '0') {
            $query->where('amount', '>=', $request->input('amountstart'));
        }

        if ($request->input('amountend', '0') != '0') {
            $query->where('amount', '<=', $request->input('amountend'));
        }

        $data['movements'] = $query->get();

        if ($filtered == false) {
            /*
                Qui si finisce quando si accede alla pagina principale della
                contabilità
            */
            $data['balance'] = Auth::user()->gas->balances()->first();
            return Theme::view('pages.movements', $data);
        }
        else {
            if ($bilist) {
                /*
                    Qui si finisce quando si aggiorna l'elenco di movimenti
                    facenti riferimento ad un soggetto specifico
                */
                return Theme::view('movement.bilist', $data);
            }
            else {
                /*
                    Qui si finisce quando si aggiorna l'elenco di movimenti
                    nella pagina principale della contabilità
                */
                return Theme::view('movement.list', $data);
            }
        }
    }

    public function create(Request $request)
    {
        $type = $request->input('type');
        if ($type == 'none') {
            return '';
        }

        $metadata = MovementType::types($type);
        $data = [];

        $payments = [];
        $all_payments = MovementType::payments();
        $functions = json_decode($metadata->function);
        foreach ($functions as $info) {
            $payments[$info->method] = $all_payments[$info->method];
        }

        $data['payments'] = $payments;
        $data['fixed'] = $metadata->fixed_value;

        $data['sender_type'] = $metadata->sender_type;
        if ($metadata->sender_type != null) {
            $st = $metadata->sender_type;
            $data['senders'] = $st::sorted()->get();
        } else {
            $data['senders'] = [];
        }

        $data['target_type'] = $metadata->target_type;
        if ($metadata->target_type != null) {
            $tt = $metadata->target_type;
            $data['targets'] = $tt::sorted()->get();
        }
        else {
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
                'printable_text' => $m->printableName()
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

    public function creditsTable()
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false && $user->can('movements.view', $user->gas) == false) {
            abort(503);
        }

        return Theme::view('movement.credits');
    }

    public function document(Request $request, $type, $subtype = 'none')
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false && $user->can('movements.view', $user->gas) == false) {
            abort(503);
        }

        switch ($type) {
            case 'credits':
                $users = App\User::sorted()->get();

                if ($subtype == 'csv') {
                    $filename = sprintf('Crediti al %s.csv', date('d/m/Y'));
                    http_csv_headers($filename);
                    return Theme::view('documents.credits_table_csv', ['users' => $users]);
                }
                else if ($subtype == 'rid') {
                    $filename = sprintf('RID Debiti al %s.txt', date('d/m/Y'));
                    header('Content-Type: plain/text');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Cache-Control: no-cache, no-store, must-revalidate');
                    header('Pragma: no-cache');
                    header('Expires: 0');
                    return Theme::view('documents.credits_rid', ['users' => $users]);
                }
                break;
        }
    }

    public function getBalance()
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false && $user->can('movements.view', $user->gas) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $balance = $user->gas->balances()->first();
        $obj = (object)[
            'bank' => $balance->bank,
            'cash' => $balance->cash,
            'gas' => $balance->gas,
            'suppliers' => $balance->suppliers,
            'deposits' => $balance->deposits
        ];
        return response()->json($obj, 200);
    }

    public function recalculateCurrentBalance()
    {
        $current_date = date('Y-m-d');
        $movements = Movement::where('archived', false)->get();
        foreach($movements as $m) {
            $m->updated_at = $current_date;
            $m->save();
        }
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
            CreditableTrait::resetAllCurrentBalances();
            $this->recalculateCurrentBalance();
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

        try {
            DB::beginTransaction();

            Session::put('movements-recalculating', true);
            $date = decodeDate($request->input('date'));

            /*
                Azzero tutti i saldi
            */
            CreditableTrait::resetAllCurrentBalances();

            /*
                Ricalcolo i movimenti fino alla data desiderata
            */
            $current_date = date('Y-m-d');
            $movements = Movement::where('date', '<', $date)->where('archived', false)->get();
            foreach($movements as $m) {
                $m->updated_at = $current_date;
                $m->archived = true;
                $m->save();
            }

            /*
                Duplico i saldi appena calcolati, e alle copie precedenti
                assegno la data della chiusura del bilancio
            */
            CreditableTrait::duplicateAllCurrentBalances($date);

            /*
                Ricalcolo i saldi correnti, che a questo punto saranno dalla
                data di chiusura alla data corrente
            */
            $this->recalculateCurrentBalance();

            DB::commit();
        }
        catch(\Exception $e) {
            Log::error('Errore nel ricalcolo saldi: ' . $e->getMessage());
        }

        Session::forget('movements-recalculating');
        return redirect(url('/movements'));
    }
}
