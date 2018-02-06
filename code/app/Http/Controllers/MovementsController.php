<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Theme;
use Log;
use PDF;
use Session;
use Response;

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
        $obj->amount = $request->input('amount', 0);
        $obj->method = $request->input('method');
        $obj->type = $request->input('type');
        $obj->identifier = $request->input('identifier');
        $obj->notes = $request->input('notes');
        $obj->parseRequest($request);

        return $obj;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false && $user->can('movements.view', $user->gas) == false) {
            abort(503);
        }

        /*
            TODO sarebbe assai più efficiente usare with('sender') e
            with('target'), ma poi la relazione in Movement si spacca (cambiando
            in virtù del tipo di oggetto linkato). Sarebbe opportuno inrodurre
            un'altra relazione espressamente dedicata ai tipi di oggetto
            soft-deletable
        */
        $query = Movement::orderBy('date', 'desc');

        if ($request->has('startdate')) {
            $start = decodeDate($request->input('startdate'));
            $filtered = true;
        }
        else {
            $start = date('Y-m-d', strtotime('-1 weeks'));
            $filtered = false;
        }

        $query->where('date', '>=', $start);

        if ($request->has('enddate')) {
            $end = decodeDate($request->input('enddate'));
        }
        else {
            $end = date('Y-m-d');
        }

        $query->where('date', '<=', $end);

        if ($request->input('type', 'none') != 'none') {
            $query->where('type', $request->input('type'));
        }

        if ($request->input('method', 'all') != 'all') {
            $query->where('method', $request->input('method'));
        }

        if ($request->input('user_id', '0') != '0') {
            $user_id = $request->input('user_id');
            $generic_target = User::find($user_id);
            if ($generic_target)
                $query = $generic_target->queryMovements($query);
        }

        if ($request->input('supplier_id', '0') != '0') {
            $supplier_id = $request->input('supplier_id');
            $generic_target = Supplier::withTrashed()->find($supplier_id);
            if ($generic_target)
                $query = $generic_target->queryMovements($query);
        }

        if ($request->input('generic_target_id', '0') != '0') {
            $target_id = $request->input('generic_target_id');
            $target_type = $request->input('generic_target_type');
            $generic_target = $target_type::find($target_id);
            if ($generic_target) {
                $query = $generic_target->queryMovements($query);
                $data['main_target'] = $generic_target;
            }
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
            $data['balance'] = Auth::user()->gas->current_balance;
            return Theme::view('pages.movements', $data);
        }
        else {
            $format = $request->input('format', 'none');

            if ($format == 'none') {
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
            else if ($format == 'csv') {
                $filename = _i('Esportazione movimenti GAS %s.csv', date('d/m/Y'));
                $headers = [_i('Data Registrazione'), _i('Data Movimento'), _i('Tipo'), _i('Pagamento'), _i('Pagante'), _i('Pagato'), _i('Valore'), _i('Note')];
                return output_csv($filename, $headers, $data['movements'], function($mov) {
                    $row = [];
                    $row[] = $mov->registration_date;
                    $row[] = $mov->date;
                    $row[] = $mov->printableType();
                    $row[] = $mov->printablePayment();
                    $row[] = $mov->sender ? $mov->sender->printableName() : '';
                    $row[] = $mov->target ? $mov->target->printableName() : '';
                    $row[] = printablePrice($mov->amount);
                    $row[] = $mov->notes;
                    return $row;
                });
            }
            else if ($format == 'pdf') {
                $html = Theme::view('documents.movements_pdf', ['movements' => $data['movements']])->render();
                $title = _i('Esportazione movimenti GAS %s', date('d/m/Y'));
                $filename = $title . '.pdf';
                PDF::SetTitle($title);
                PDF::AddPage('L');
                PDF::writeHTML($html, true, false, true, false, '');
                PDF::Output($filename, 'D');
            }
        }
    }

    public function create(Request $request)
    {
        $type = $request->input('type', null);
        if ($type == null) {
            return Theme::view('movement.create');
        }

        if ($type == 'none') {
            return '';
        }

        $metadata = MovementType::types($type);
        $data = [];

        $data['payments'] = MovementType::paymentsByType($type);
        $default_method = MovementType::defaultPaymentByType($type);
        $data['payments'][$default_method]->checked = true;
        $data['default_method'] = $default_method;

        $data['fixed'] = $metadata->fixed_value;
        $data['default_notes'] = $metadata->default_notes;

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

        /*
            Attenzione!!! Qui ci va un controllo sulle autorizzazioni, ma non
            basta movements.admin in quanto anche gli addetti consegne devono
            poter creare movimenti (i pagamenti delle consegne, ovviamente solo
            dei fornitori che gli competono)
        */

        $m = $this->basicReadFromRequest($request);
        $m->save();

        if ($m->saved == false) {
            return $this->errorResponse(_i('Salvataggio fallito'));
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

    public function show_ro(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('movements.view', $user->gas) == false) {
            abort(503);
        }

        $movement = Movement::findOrFail($id);
        return Theme::view('movement.show', ['obj' => $movement]);
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false && $user->can('movements.view', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
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
                $users = User::sorted()->get();

                $group = $request->input('credit', 'all');
                if ($group == 'minor') {
                    $users = $users->filter(function($u) {
                        return $u->current_balance_amount < 0;
                    });
                }
                else if ($group == 'major') {
                    $users = $users->filter(function($u) {
                        return $u->current_balance_amount >= 0;
                    });
                }

                if ($subtype == 'csv') {
                    $filename = _i('Crediti al %s.csv', date('d/m/Y'));
                    $headers = [_i('ID'), _i('Nome'), _i('E-Mail'), _i('Credito Residuo')];
                    return output_csv($filename, $headers, $users, function($user) {
                        $row = [];
                        $row[] = $user->username;
                        $row[] = $user->printableName();
                        $row[] = $user->email;
                        $row[] = printablePrice($user->current_balance_amount, ',');
                        return $row;
                    });
                }
                else if ($subtype == 'rid') {
                    $filename = _i('SEPA del %s.xml', date('d/m/Y'));

                    $headers = [
                        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                        'Content-type' => 'text/xml',
                        'Content-Disposition' => 'attachment; filename=' . $filename,
                        'Cache-Control' => 'no-cache, no-store, must-revalidate',
                        'Expires' => '0',
                        'Pragma' => 'no-cache'
                    ];

                    return Response::stream(function() use ($users) {
                        $FH = fopen('php://output', 'w');
                        $contents = Theme::view('documents.credits_rid', ['users' => $users])->render();
                        fwrite($FH, $contents);
                        fclose($FH);
                    }, 200, $headers);
                }
                break;
        }
    }

    public function getBalance()
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false && $user->can('movements.view', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $balance = $user->gas->current_balance;
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
        $index = 0;

        do {
            $movements = Movement::where('archived', false)->take(100)->offset(100 * $index)->get();
            if ($movements->count() == 0)
                break;

            foreach($movements as $m) {
                $m->updated_at = $current_date;
                $m->save();
            }

            $index++;

        } while(true);
    }

    public function recalculate(Request $request)
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        DB::beginTransaction();
        $success = false;

        try {
            Session::put('movements-recalculating', true);
            $current_status = CreditableTrait::resetAllCurrentBalances();
            $this->recalculateCurrentBalance();
            Session::forget('movements-recalculating');
            $diffs = CreditableTrait::compareBalances($current_status);
            return $this->successResponse([
                'diffs' => $diffs
            ]);
        }
        catch(\Exception $e) {
            Log::error(_i('Errore nel ricalcolo saldi: %s', $e->getMessage()));
            Session::forget('movements-recalculating');
            return $this->errorResponse(_i('Errore'));
        }
    }

    public function closeBalance(Request $request)
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
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

            $index = 0;
            do {
                $movements = Movement::where('date', '<', $date)->where('archived', false)->take(100)->offset(100 * $index)->get();
                if ($movements->count() == 0)
                    break;

                foreach($movements as $m) {
                    $m->updated_at = $current_date;
                    $m->save();
                }

                $index++;

            } while(true);

            /*
                Archivio i movimenti più vecchi della data indicata
            */
            Movement::where('date', '<', $date)->where('archived', false)->update(['archived' => true]);

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

            Session::forget('movements-recalculating');
            return $this->successResponse();
        }
        catch(\Exception $e) {
            Log::error(_i('Errore nel ricalcolo saldi: %s', $e->getMessage()));
            return $this->errorResponse(_i('Errore'));
        }
    }
}
