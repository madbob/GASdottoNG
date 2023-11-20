<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\User;
use App\Aggregate;

use App\Services\UsersService;
use App\Formatters\User as UserFormatter;
use App\Exceptions\AuthException;

class UsersController extends BackedController
{
    public function __construct(UsersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\User',
            'service' => $service
        ]);
    }

    public function index(Request $request)
    {
        return $this->easyExecute(function() use ($request) {
            $user = $request->user();
            $users = $this->service->list('', $user->can('users.admin', $user->gas));
            return view('pages.users', ['users' => $users]);
        });
    }

    /*
        Il middleware InactiveUser forza un redirect su questa rotta quando
        l'utente non è ancora stato approvato
    */
    public function blocked(Request $request)
    {
        if ($request->user()->pending == false) {
            return redirect()->route('dashboard');
        }
        else {
            return view('user.blocked');
        }
    }

    public function revisioned(Request $request, $id)
    {
        return $this->easyExecute(function() use ($id, $request) {
            $status = $request->input('action');
            $this->service->revisioned($id, $status == 'approve');
            return $this->successResponse(['action' => $status]);
        });
    }

    public function promote(Request $request, $id)
    {
        return $this->easyExecute(function() use ($id) {
            $subject = $this->service->promoteFriend($id);
            return $this->commonSuccessResponse($subject);
        });
    }

    public function reassign(Request $request, $id)
    {
        return $this->easyExecute(function() use ($id, $request) {
            $new_parent = $request->input('parent_id');
            $this->service->reassignFriend($id, $new_parent);
            return $this->successResponse();
        });
    }

    public function search(Request $request)
    {
        return $this->easyExecute(function() use ($request) {
            $term = $request->input('term');
            $users = $this->service->list($term);
            $users = $this->toJQueryAutocompletionFormat($users);
            return json_encode($users);
        });
    }

    public function export(Request $request)
    {
        $user = $request->user();
        if ($user->can('users.admin', $user->gas) == false) {
            abort(503);
        }

        $fields = $request->input('fields', []);
        $headers = UserFormatter::getHeaders($fields);
        $users = $this->service->list('', true);

        return output_csv(_i('utenti.csv'), $headers, $users, function($user) use ($fields) {
            return UserFormatter::format($user, $fields);
        });
    }

    private function getOrders($user_id, $supplier_id, $start, $end)
    {
        return Aggregate::whereHas('orders', function($query) use ($user_id, $supplier_id, $start, $end) {
            $query->whereHas('bookings', function($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });

            if ($start) {
                $query->where('start', '>=', $start);
            }

            if ($end) {
                $query->where('end', '<=', $end);
            }

            if ($supplier_id != '0') {
                $query->where('supplier_id', $supplier_id);
            }
        })->with('orders')->get();
    }

    public function profile(Request $request)
    {
        return $this->easyExecute(function() use ($request) {
            $id = $request->user()->id;
            $active_tab = $request->input('tab');
            $user = $this->service->show($id);
            return view('pages.profile', ['user' => $user, 'active_tab' => $active_tab]);
        });
    }

    public function searchOrders(Request $request, $id)
    {
        $supplier_id = $request->input('supplier_id');
        $start = decodeDate($request->input('startdate'));
        $end = decodeDate($request->input('enddate'));
        $orders = $this->getOrders($id, $supplier_id, $start, $end);
        return view('commons.orderslist', ['orders' => $orders]);
    }

    public function show($id)
    {
        return $this->easyExecute(function() use ($id) {
            $user = $this->service->show($id);
            return view('user.edit', ['user' => $user]);
        });
    }

    public function show_ro($id)
    {
        return $this->easyExecute(function() use ($id) {
            $user = $this->service->show($id);
            return view('user.edit', ['user' => $user, 'read_only' => true]);
        });
    }

    public function picture($id)
    {
        return $this->easyExecute(function() use ($id) {
            return $this->service->picture($id);
        });
    }

    public function startTour(Request $request)
    {
        $user = $request->user();
        $gas = $user->gas;

        $steps = [];

        /*
            Gli identificativi dei pulsanti devono corrispondere a quelli
            assegnati in MenuServiceProvider
        */

        $steps[] = (object) [
			'title' => _i('Benvenuto in GASdotto!'),
			'content' => _i("Qui ti diamo qualche suggerimento per iniziare ad utilizzare questa nuova piattaforma..."),
		];

        $steps[] = (object) [
            'title' => _i('I tuoi dati'),
            'content' => _i("Cliccando qui accedi al pannello dei tuoi dati personali, da cui poi cambiare il tuo indirizzo email, la tua password di accesso e molto altro."),
            'target' => '#menu_profile',
        ];

        if ($user->can('users.admin', $gas)) {
            $steps[] = (object) [
                'title' => _i('Gli altri utenti'),
                'content' => _i("Da qui consulti l'elenco degli utenti, ne modifichi i parametri, e ne puoi invitare di nuovi (o li puoi importare da un file CSV)."),
                'target' => '#menu_users',
            ];
        }

        if ($user->can('supplier.add', $gas) || $user->can('supplier.modify', null)) {
            $steps[] = (object) [
                'title' => _i('I fornitori e i listini'),
                'content' => _i("Cliccando qui puoi consultare l'elenco dei fornitori, crearne di nuovi, modificarli, e per ciascuno caricare o modificare il relativo listino."),
                'target' => '#menu_suppliers',
            ];
        }

        if ($user->can('supplier.orders', null) || $user->can('supplier.shippings', null)) {
            $steps[] = (object) [
                'title' => _i('Gli ordini'),
                'content' => _i("Da questa pagina accedi all'elenco degli ordini, da cui crearli e modificarli. Cliccando su ciascun ordine puoi trovare anche la tab 'Consegne' per tenere traccia delle consegne e generare i movimenti contabili di pagamento."),
                'target' => '#menu_orders',
            ];
        }

        if ($user->can('supplier.book', null)) {
            $steps[] = (object) [
                'title' => _i('Le prenotazioni'),
                'content' => _i("Qui trovi l'elenco degli ordini attualmente in corso, e puoi sottoporre le tue prenotazioni: clicca su ciascun ordine, e specifica la quantità desiderata per ogni prodotto."),
                'target' => '#menu_bookings',
            ];
        }

        if ($user->can('movements.view', $gas) || $user->can('movements.admin', $gas)) {
            $steps[] = (object) [
                'title' => _i('La contabilità'),
                'content' => _i("In questa pagina trovi tutti i movimenti contabili ed i relativi strumenti di amministrazione."),
                'target' => '#menu_accouting',
            ];
        }

        if ($user->can('gas.config', $gas)) {
            $steps[] = (object) [
                'title' => _i('Tutte le configurazioni'),
                'content' => _i("Cliccando qui trovi una moltitudine di parametri per personalizare il comportamento di questa istanza GASdotto."),
                'target' => '#menu_config',
            ];
        }

        $steps[] = (object) [
            'title' => _i('Help in linea'),
            'content' => _i("Aprendo i diversi pannelli di GASdotto, accanto a molti parametri trovi una icona blu: passandoci sopra il cursore del mouse, o pigiandoci sopra con il dito usando lo smartphone, ti viene mostrato un breve testo descrittivo che te ne illustra i dettagli.") . '<br><img class="img-fluid p-2 mt-2 bg-dark" src="' . asset('images/inline_help.gif') . '">',
        ];

        if ($user->can('users.admin', $gas)) {
            $steps[] = (object) [
    			'title' => _i('Dubbi?'),
    			'content' => _i("Se hai un dubbio sull'utilizzo di GASdotto, o una segnalazione, o una richiesta, cliccando qui trovi i nostri contatti."),
                'target' => '#menu_help'
    		];
        }

        return response()->json([
            'dialogZ' => 2000,
            'nextLabel' => '>>',
            'prevLabel' => '<<',
            'finishLabel' => _i('Finito'),
            'steps' => $steps,
        ]);
    }

    public function finishTour(Request $request)
    {
        $user = $request->user();
        $user->tour = true;
        $user->save();
        return $this->successResponse();
    }

    private function testInternalFunctionsAccess($requester, $target, $type)
    {
        $admin_editable = $requester->can('users.admin', $target->gas);
        $access = ($admin_editable || $requester->id == $target->id || $target->parent_id == $requester->id);

        if ($access == false) {
            switch($type) {
                case 'accounting':
                    $access = $requester->can('movements.admin', $target->gas) || $requester->can('movements.view', $target->gas);
                    break;

                case 'friends':
                    $access = $target->can('users.subusers', $target->gas);
                    break;
            }
        }

        if ($access == false) {
            throw new AuthException(403);
        }
    }

    public function bookings(Request $request, $id)
    {
        return $this->easyExecute(function() use ($request, $id) {
            $user = $this->service->show($id);
            $this->testInternalFunctionsAccess($request->user(), $user, 'bookings');
            $booked_orders = $this->getOrders($id, 0, date('Y-m-d', strtotime('-1 months')), '2100-01-01');
            return view('user.bookings', ['user' => $user, 'booked_orders' => $booked_orders]);
        });
    }

    public function statistics(Request $request, $id)
    {
        return $this->easyExecute(function() use ($request, $id) {
            $user = $this->service->show($id);
            $this->testInternalFunctionsAccess($request->user(), $user, 'accounting');
            return view('commons.statspage', ['target' => $user]);
        });
    }

    public function accounting(Request $request, $id)
    {
        return $this->easyExecute(function() use ($request, $id) {
            $user = $this->service->show($id);
            $this->testInternalFunctionsAccess($request->user(), $user, 'accounting');
            return view('user.accounting', ['user' => $user]);
        });
    }

    public function friends(Request $request, $id)
    {
        return $this->easyExecute(function() use ($request, $id) {
            $user = $this->service->show($id);
            $this->testInternalFunctionsAccess($request->user(), $user, 'friends');
            return view('user.friends', ['user' => $user]);
        });
    }

    private function toJQueryAutocompletionFormat($users)
    {
        $ret = [];
        foreach ($users as $user) {
            $fullname = $user->printableName();
            $u = (object)array(
                'id' => $user->id,
                'label' => $fullname,
                'value' => $fullname
            );
            $ret[] = $u;
        }
        return $ret;
    }

    /*
        Per ottenere il modale dello "Stato Quote"
    */
    public function fees(Request $request)
    {
        return $this->easyExecute(function() {
            $this->ensureAuth(['users.admin' => 'gas', 'users.movements' => 'gas']);
            $users = $this->service->list('', true);
            $users->loadMissing(['fee', 'gas']);
            return view('user.fees', ['users' => $users]);
        });
    }

    /*
        Per ricaricare una singola riga nel modale "Stato Quote", solitamente se
        un movimento di pagamento quota viene eliminato.
        Ogni riga della tabella riporta questo URL come indirizzo per il
        ricaricamento dell'area (funzione reload-portion)
    */
    public function feeRow(Request $request, $id)
    {
        return $this->easyExecute(function() use ($id) {
            $this->ensureAuth(['users.admin' => 'gas', 'users.movements' => 'gas']);
            $user = User::findOrFail($id);
            return view('user.partials.fee_row', ['user' => $user]);
        });
    }

    public function feesSave(Request $request)
    {
        $user = $request->user();

        if ($user->can('users.admin') || $user->can('users.movements')) {
            $users = $request->input('user_id');

            foreach($users as $user_id) {
                $user = User::tFind($user_id);
                $user->setStatus($request->input('status' . $user_id), $request->input('deleted_at' . $user_id), $request->input('suspended_at' . $user_id));
                $user->save();
            }

            return redirect()->route('users.index');
        }
        else {
            abort(401);
        }
    }

    public function notifications(Request $request, $id)
    {
        return $this->easyExecute(function() use ($request, $id) {
            $this->service->notifications($id, $request->input('suppliers'));
            return $this->successResponse();
        });
    }

    public function changePassword(Request $request)
    {
        if ($request->user()->enforce_password_change == false) {
            return redirect()->route('dashboard');
        }

        return view('user.change_password');
    }
}
