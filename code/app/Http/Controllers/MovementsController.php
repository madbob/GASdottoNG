<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use Log;
use PDF;
use Response;

use App\MovementType;

use App\Services\MovementsService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class MovementsController extends BackedController
{
    public function __construct(MovementsService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Movement',
            'endpoint' => 'movements',
            'service' => $service
        ]);
    }

    public function index(Request $request)
    {
        try {
            $data['movements'] = $this->service->list($request->all());

            if ($request->has('startdate') == false) {
                /*
                    Qui si finisce quando si accede alla pagina principale della
                    contabilità
                */
                $data['balance'] = Auth::user()->gas->current_balance;
                return view('pages.movements', $data);
            }
            else {
                $format = $request->input('format', 'none');

                if ($format == 'none') {
                    if ($request->input('generic_target_id', '0') != '0') {
                        $target_id = $request->input('generic_target_id');
                        $target_type = $request->input('generic_target_type');
                        $generic_target = $target_type::find($target_id);
                        if ($generic_target) {
                            $data['main_target'] = $generic_target;
                        }

                        return view('movement.bilist', $data);
                    }
                    else {
                        /*
                            Qui si finisce quando si aggiorna l'elenco di movimenti
                            nella pagina principale della contabilità
                        */
                        return view('movement.list', $data);
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
                    $html = view('documents.movements_pdf', ['movements' => $data['movements']])->render();
                    $title = _i('Esportazione movimenti GAS %s', date('d/m/Y'));
                    $filename = $title . '.pdf';
                    PDF::SetTitle($title);
                    PDF::AddPage('L');
                    PDF::writeHTML($html, true, false, true, false, '');
                    PDF::Output($filename, 'D');
                }
            }
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function create(Request $request)
    {
        $type = $request->input('type', null);
        if ($type == null) {
            return view('movement.create');
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

        return view('movement.selectors', $data);
    }

    public function show_ro(Request $request, $id)
    {
        try {
            $movement = $this->service->show($id);
            return view('movement.show', ['obj' => $movement]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function creditsTable()
    {
        $user = Auth::user();
        if ($user->can('movements.admin', $user->gas) == false && $user->can('movements.view', $user->gas) == false) {
            abort(503);
        }

        return view('movement.credits');
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
                        $contents = view('documents.credits_rid', ['users' => $users])->render();
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
        try {
            $this->service->recalculateCurrentBalance();
            return $this->successResponse();
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (\Exception $e) {
            return $this->errorResponse(_i('Errore'));
        }
    }

    public function recalculate()
    {
        try {
            $diffs = $this->service->recalculate();

            if ($diffs == null) {
                return $this->errorResponse(_i('Errore'));
            }
            else {
                return $this->successResponse([
                    'diffs' => $diffs
                ]);
            }
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (\Exception $e) {
            return $this->errorResponse(_i('Errore'));
        }
    }

    public function closeBalance(Request $request)
    {
        try {
            $this->service->closeBalance($request->all());
            return $this->successResponse();
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (\Exception $e) {
            return $this->errorResponse(_i('Errore'));
        }
    }
}
