<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TourController extends Controller
{
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
}
