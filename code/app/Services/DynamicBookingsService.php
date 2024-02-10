<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

use App\Services\Concerns\TranslatesBookings;
use App\User;
use App\Aggregate;

class DynamicBookingsService extends BookingsService
{
    use TranslatesBookings;

    public function __construct()
    {
        $this->break_on_contraint = false;
    }

    /*
        Questa funzione viene invocata dai pannelli di prenotazione e consegna,
        ogni volta che viene apportata una modifica sulle quantità, e permette
        di controllare che le quantità immesse siano coerenti coi constraints
        imposti sui prodotti (quantità minima, quantità multipla...) e calcolare
        tutti i valori tenendo in considerazione tutti i modificatori esistenti.
        Eseguire tutti questi calcoli client-side in JS sarebbe complesso, e
        ridondante rispetto all'implementazione server-side che comunque sarebbe
        necessaria
    */
    public function dynamicModifiers(array $request, $aggregate, $target_user)
    {
        /*
            Innanzitutto, qui sospendo l'esecuzione delle callback sui movimenti
            contabili. Nella fase di revisione della prenotazione capita che i
            relativi movimenti di pagamento vengano aggiunti, modificati o
            rimossi, ma considerando che tutto quel che viene calcolato a
            partire da questa funzione viene poi distrutto non val la pena stare
            ad effettuare tutti i calcoli sui saldi
        */
        app()->make('MovementsHub')->setSuspended(true);

        for ($i = 0; $i <= 3; $i++) {
            /*
                Se viene sollevata una eccezione, questo intero blocco viene
                reiterato almeno 3 volte. Questo per eventualmente aggirare
                problemi di lock sul database, considerando anche che sta tutto
                in transazioni.
                Per scrupolo ad ogni iterazione svuoto la cache dei modelli, che
                resta in RAM, per evitare che i risultati delle iterazioni
                precedenti vadano ad interferire
            */
            Artisan::call('modelCache:clear');

            DB::beginTransaction();

            try {
                $bookings = [];
                $delivering = $request['action'] != 'booked';

                $ret = (object) [
                    'bookings' => [],
                ];

                $orders = $aggregate->orders()->with(['products', 'products.measure', 'bookings', 'modifiers'])->get();
                $user = $this->testAccess($target_user, $orders, $delivering);

                foreach($orders as $order) {
                    $order->setRelation('aggregate', $aggregate);
                    $booking = $this->handleBookingUpdate($request, $user, $order, $target_user, $delivering);
                    if ($booking) {
                        $ret->bookings[$booking->id] = $this->translateBooking($booking, $delivering, true);
                    }
                }

                /*
                    Lo scopo di questa funzione è ottenere una preview dei
                    totali della prenotazione, dunque al termine invalido tutte
                    le modifiche fatte sul database
                */
                DB::rollback();

                return $ret;
            }
            catch(\Exception $e) {
                DB::rollback();

                if ($i == 3) {
                    \Log::warning('Errore in lettura dinamici della prenotazione: ' . $e->getMessage());
                    return (object) [
                        'target' => '',
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }

        return null;
    }
}
