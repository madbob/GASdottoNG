<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use Auth;
use Artisan;
use Response;
use PDF;

use App\User;
use App\Supplier;
use App\Currency;
use App\Movement;

use App\Services\MovementsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AuthException;

class MovementsController extends BackedController
{
    public function __construct(MovementsService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Movement',
            'service' => $service,
        ]);
    }

    private function checkAuth()
    {
        $user = Auth::user();

        if ($user->can('movements.admin', $user->gas) === false && $user->can('movements.view', $user->gas) === false && $user->can('supplier.movements', null) === false && $user->can('supplier.invoices', null) === false) {
            abort(503);
        }

        return $user;
    }

    public function index(Request $request)
    {
        try {
            $data['movements'] = $this->service->list($request->all());
            $ret = null;

            if ($request->has('startdate') === false) {
                /*
                    Qui si finisce quando si accede alla pagina principale della
                    contabilità
                */
                $data['types'] = movementTypes();
                $ret = view('pages.movements', $data);
            }
            else {
                $format = $request->input('format', 'none');

                if ($format == 'none') {
                    if ($request->input('generic_target_id', '0') != '0') {
                        $target_id = $request->input('generic_target_id');
                        $target_type = $request->input('generic_target_type');
                        $data['main_target'] = $target_type::tFind($target_id);
                        $ret = view('movement.bilist', $data);
                    }
                    else {
                        /*
                            Qui si finisce quando si aggiorna l'elenco di movimenti
                            nella pagina principale della contabilità
                        */
                        $ret = view('movement.list', $data);
                    }
                }
                else {
                    if ($format == 'balance') {
                        $ret = app()->make('MovementsFormatService')->formatAsBalance($data['movements']);
                    }
                    else {
                        $ret = $this->exportMain($format, $data['movements']);
                    }
                }
            }

            return $ret;
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    private function exportMain($format, $movements)
    {
        $filename = sanitizeFilename(_i('Esportazione movimenti GAS %s.%s', [date('d/m/Y'), $format]));

        if ($format == 'csv') {
            $headers = [_i('Data Registrazione'), _i('Data Movimento'), _i('Tipo'), _i('Pagamento'), _i('Identificativo'), _i('Pagante'), _i('Pagato'), _i('Valore'), _i('Note')];

            return output_csv($filename, $headers, $movements, function ($mov) {
                $row = [];
                $row[] = $mov->registration_date;
                $row[] = $mov->date;
                $row[] = $mov->printableType();
                $row[] = $mov->printablePayment();
                $row[] = $mov->identifier;
                $row[] = $mov->sender ? $mov->sender->printableName() : '';
                $row[] = $mov->target ? $mov->target->printableName() : '';
                $row[] = printablePrice($mov->amount);
                $row[] = $mov->notes;

                return $row;
            });
        }
        elseif ($format == 'pdf') {
            $pdf = PDF::loadView('documents.movements_pdf', ['movements' => $movements]);

            return $pdf->download($filename);
        }
    }

    public function create(Request $request)
    {
        $type = $request->input('type', null);
        if (is_null($type)) {
            return view('movement.create');
        }

        if ($type == 'none') {
            return '';
        }

        $metadata = movementTypes($type);
        $data = [];

        $data['payments'] = paymentsByType($type);
        $data['default_method'] = defaultPaymentByType($type);

        $data['fixed'] = $metadata->fixed_value;
        $data['default_notes'] = $metadata->default_notes;

        $data['sender_type'] = $metadata->sender_type;
        if ($metadata->sender_type != null) {
            $st = $metadata->sender_type;
            $data['senders'] = $st::sorted()->creditable()->get();
        }
        else {
            $data['senders'] = [];
        }

        $data['target_type'] = $metadata->target_type;
        if ($metadata->target_type != null) {
            $tt = $metadata->target_type;
            $data['targets'] = $tt::sorted()->creditable()->get();
        }
        else {
            $data['targets'] = [];
        }

        $data['allow_negative'] = $metadata->allow_negative ?? false;

        return view('movement.selectors', $data);
    }

    /*
        È possibile passare l'ID di un movimento non esistente (ad esempio: 0)
        ed i parametri richiesti per ottenere il modale di creazione di un nuovo
        movimento
    */
    public function show(Request $request, $id)
    {
        $dom_id = $request->input('dom_id', rand());

        try {
            $user = Auth::user();
            $movement = $this->service->show($id);

            return view('movement.modal', [
                'dom_id' => $dom_id,
                'obj' => $movement,
                'editable' => $user->can('movements.admin', $user->gas),
            ]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (ModelNotFoundException $e) {
            $type = $request->input('type', null);
            if (is_null($type)) {
                abort(404);
            }

            $sender_id = $request->input('sender_id');
            $sender_type = $request->input('sender_type');
            $target_id = $request->input('target_id');
            $target_type = $request->input('target_type');
            $amount = $request->input('amount');

            $sender = $sender_type::tFind($sender_id);
            $target = $target_type::tFind($target_id);

            return view('movement.modal', [
                'dom_id' => $dom_id,
                'obj' => null,
                'extra' => $request->input('extra', []),
                'default' => Movement::generate($type, $sender, $target, $amount),
            ]);
        }
    }

    public function show_ro($id)
    {
        return $this->easyExecute(function () use ($id) {
            $movement = $this->service->show($id);

            return view('movement.show', ['obj' => $movement]);
        });
    }

    public function creditsTable($type)
    {
        $this->checkAuth();

        return view('movement.' . $type);
    }

    private function exportIntegralCES($gas, $objects, $filename, $body)
    {
        $currency = Currency::where('context', 'integralces')->first();

        return output_csv($filename, null, $objects, function ($object) use ($gas, $currency, $body) {
            $amount = $object->currentBalanceAmount($currency);
            if ($amount == 0) {
                return null;
            }

            $object_account = $object->contacts()->where('type', 'integralces')->first();

            if ($object_account) {
                if ($amount < 0) {
                    $input_account = $object_account->value;
                    $output_account = $gas->integralces['identifier'];
                }
                else {
                    $input_account = $gas->integralces['identifier'];
                    $output_account = $object_account->value;
                }

                return [
                    $input_account,
                    $output_account,
                    $body,
                    printablePrice($amount * -1),
                ];
            }
            else {
                return null;
            }
        });
    }

    public function askDelete(Request $request, $id)
    {
        $this->ensureAuth(['movements.admin' => 'gas']);
        $mov = Movement::findOrFail($id);

        return view('commons.deleteconfirm', [
            'url' => route('movements.destroy', $id),
            'password_protected' => true,
            'text' => _i('Vuoi davvero eliminare il movimento<br>%s?', [$mov->printableName()]),
            'extra' => [
                /*
                    Se sono nel contesto di un utente/fornitore/GAS ricarica il
                    pannello coi bilanci, se sono nel pannello di gestione delle
                    quote utente ricarica la riga del relativo utente
                */
                'reload-portion' => ['.balance-summary', '.holding-movement-' . $id],

                'post-saved-function' => ['closeAllModals', 'refreshFilter'],
            ],
        ]);
    }

    public function document(Request $request, $type, $subtype = 'none')
    {
        $user = $this->checkAuth();

        if ($type == 'credits') {
            $users = User::sorted()->topLevel()->get();

            $filtered_users = $this->collectedFilteredUsers($request);
            if (! empty($filtered_users)) {
                $users = $users->filter(function ($u) use ($filtered_users) {
                    return in_array($u->id, $filtered_users);
                });
            }

            if ($subtype == 'csv') {
                /*
                    Qui effettuo un controllo extra sulle quote pagate, per
                    aggiornare i dati che andranno nel CSV
                */
                Artisan::call('check:fees');

                $has_fee = ($user->gas->getConfig('annual_fee_amount') != 0);
                $has_shipping_place = $user->gas->hasFeature('shipping_places');
                $filename = sanitizeFilename(_i('Crediti al %s.csv', date('d/m/Y')));

                $headers = [_i('ID'), _i('Nome'), _i('E-Mail')];

                $currencies = Currency::enabled();
                foreach ($currencies as $curr) {
                    $headers[] = _i('Credito Residuo %s', $curr->symbol);
                }

                if ($has_fee) {
                    $headers[] = _i('Quota Pagata');
                }

                if ($has_shipping_place) {
                    $headers[] = _i('Luogo di Consegna');
                }

                return output_csv($filename, $headers, $users, function ($user) use ($currencies, $has_fee, $has_shipping_place) {
                    $row = [];
                    $row[] = $user->username;
                    $row[] = $user->printableName();
                    $row[] = $user->email;

                    foreach ($currencies as $curr) {
                        $row[] = printablePrice($user->currentBalanceAmount($curr));
                    }

                    if ($has_fee) {
                        $row[] = $user->fee != null ? _i('SI') : _i('NO');
                    }

                    if ($has_shipping_place) {
                        $row[] = $user->shippingplace != null ? $user->shippingplace->name : _i('Nessuno');
                    }

                    return $row;
                });
            }
            elseif ($subtype == 'rid') {
                $date = decodeDate($request->input('date'));
                $body = strtoupper($request->input('body'));
                $filename = sanitizeFilename(_i('SEPA del %s.xml', date('d/m/Y', strtotime($date))));

                $headers = [
                    'Content-type' => 'text/xml',
                    'Content-Disposition' => 'attachment; filename=' . $filename,
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Expires' => '0',
                    'Pragma' => 'no-cache',
                ];

                return Response::stream(function () use ($users, $date, $body) {
                    $stream = fopen('php://output', 'w');

                    $contents = view('documents.credits_rid', [
                        'users' => $users,
                        'date' => $date,
                        'body' => $body,
                    ])->render();

                    fwrite($stream, $contents);
                    fclose($stream);
                }, 200, $headers);
            }
            elseif ($subtype == 'integralces') {
                $body = strtoupper($request->input('body'));
                $filename = sanitizeFilename(_i('IntegralCES Utenti.csv'));

                return $this->exportIntegralCES($user->gas, $users, $filename, $body);
            }
        }
        else if ($type == 'suppliers') {
            $suppliers = $user->gas->suppliers;

            if ($subtype == 'csv') {
                $filename = sanitizeFilename(_i('Saldi Fornitori al %s.csv', date('d/m/Y')));
                $headers = [_i('ID'), _i('Nome')];

                $currencies = Currency::enabled();
                foreach ($currencies as $curr) {
                    $headers[] = _i('Saldo %s', $curr->symbol);
                }

                return output_csv($filename, $headers, $suppliers, function ($supplier) use ($currencies) {
                    $row = [];
                    $row[] = $supplier->id;
                    $row[] = $supplier->printableName();

                    foreach ($currencies as $curr) {
                        $row[] = printablePrice($supplier->currentBalanceAmount($curr));
                    }

                    return $row;
                });
            }
            elseif ($subtype == 'integralces') {
                $body = strtoupper($request->input('body'));
                $filename = sanitizeFilename(_i('IntegralCES Fornitori.csv'));

                return $this->exportIntegralCES($user->gas, $suppliers, $filename, $body);
            }
        }
    }

    public function getBalance(Request $request, $targetid)
    {
        $this->checkAuth();
        $obj = fromInlineId($targetid);

        return view('movement.summary', ['obj' => $obj]);
    }

    public function getHistory(Request $request, $targetid)
    {
        $this->checkAuth();
        $obj = fromInlineId($targetid);

        return view('movement.history', ['obj' => $obj]);
    }

    public function getHistoryDetails(Request $request)
    {
        $this->checkAuth();
        $date = $request->input('date');

        $users = $this->service->creditHistory(User::class, $date);
        $suppliers = $this->service->creditHistory(Supplier::class, $date);

        $format = $request->input('format');
        if ($format == 'csv') {
            $target = $request->input('target');
            if ($target == 'users') {
                $target = $users;
            }
            else {
                $target = $suppliers;
            }

            $data = [];

            foreach ($target as $name => $row) {
                $data[] = array_merge([$name], $row);
            }

            $filename = sanitizeFilename(_i('Storico Saldi al %s.csv', Carbon::now()->format('d/m/Y')));

            return output_csv($filename, null, $data, null);
        }
        else {
            return view('movement.historydetails', [
                'date' => $date,
                'users' => $users,
                'suppliers' => $suppliers,
            ]);
        }
    }

    public function recalculate()
    {
        return $this->easyExecute(function () {
            $diffs = $this->service->recalculate();

            if (is_null($diffs)) {
                return $this->errorResponse(_i('Errore'));
            }
            else {
                return $this->successResponse([
                    'diffs' => $diffs,
                ]);
            }
        });
    }

    public function closeBalance(Request $request)
    {
        return $this->easyExecute(function () use ($request) {
            $this->service->closeBalance($request->all());

            return $this->successResponse();
        });
    }

    public function askDeleteBalance($id)
    {
        $this->checkAuth();

        return view('movement.deletebalance', ['id' => $id]);
    }

    public function deleteBalance($id)
    {
        return $this->easyExecute(function () use ($id) {
            $this->service->deleteBalance($id);

            return $this->successResponse();
        });
    }
}
