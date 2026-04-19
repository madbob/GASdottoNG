<?php

namespace App\Importers\CSV;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\User;
use App\Aggregate;
use App\Order;

class Deliveries extends CSVImporter
{
    public function fields()
    {
        return [
            'username' => (object) [
                'label' => __('texts.auth.username'),
                'mandatory' => true,
            ],
            'first' => (object) [
                'label' => __('texts.export.importing.deliveries.first_product'),
                'mandatory' => true,
                'explain' => __('texts.export.help.importing.deliveries.first_product'),
            ],
        ];
    }

    public function extraInformations()
    {
        return __('texts.export.importing.deliveries.instruction');
    }

    public function testAccess(array $request)
    {
        $user = Auth::user();
        $aggregate_id = $request['aggregate_id'];
        $aggregate = Aggregate::findOrFail($aggregate_id);

        return $user->can('supplier.shippings', $aggregate);
    }

    public function guess(array $request)
    {
        return $this->storeUploadedFile($request, [
            'type' => 'deliveries',
            'next_step' => 'select',
            'sorting_fields' => $this->fields(),
            'extra_fields' => [
                'aggregate_id' => $request['aggregate_id'],
            ],
            'extra_description' => [
                __('texts.export.importing.deliveries.notice'),
            ],
        ]);
    }

    private function translateProductsInLine($line, $first_product_index, $mapped_products): array
    {
        $datarow = [];

        for ($inner_index = $first_product_index; $inner_index < count($line); $inner_index++) {
            if (isset($mapped_products[$inner_index])) {
                $reference_product = $mapped_products[$inner_index];
                $quantity = guessDecimal($line[$inner_index]);
                $product_id = $reference_product->product->id;
                $datarow[$product_id] = $quantity;

                if ($reference_product->combo) {
                    if (isset($datarow['variant_quantity_' . $product_id]) === false) {
                        $datarow['variant_quantity_' . $product_id] = [];
                    }

                    foreach ($reference_product->combo->values as $val) {
                        $variant_id = $val->variant->id;

                        if (isset($datarow['variant_selection_' . $variant_id]) === false) {
                            $datarow['variant_selection_' . $variant_id] = [];
                        }

                        $datarow['variant_selection_' . $variant_id][] = $val->id;
                    }

                    $datarow['variant_quantity_' . $product_id][] = $quantity;
                }
            }
        }

        return $datarow;
    }

    public function select(array $request)
    {
        $user = Auth::user();
        $service = app()->make('BookingsService');
        $errors = [];

        $columns = $this->initRead($request);
        $target_separator = ',';

        $aggregate_id = $request['aggregate_id'];
        $aggregate = Aggregate::findOrFail($aggregate_id);

        $mapped_products = [];
        $target_order = null;

        [$first_product_index] = $this->getColumnsIndex($columns, ['first']);
        $csvdata = $this->getRecords();

        $header = $csvdata[0];

        for ($i = $first_product_index; $i < count($header); $i++) {
            $name = $header[$i];

            foreach ($aggregate->orders as $order) {
                if ($name == __('texts.orders.totals.total')) {
                    continue;
                }

                $p = productByString($name, $order->products);

                if ($p) {
                    if ($target_order && $target_order->id != $order->id) {
                        throw new \InvalidArgumentException('Operazione fallita: nel documento importato sono presenti prodotti di diversi ordini');
                    }

                    $target_order = $order;

                    $mapped_products[$i] = (object) [
                        'product' => $p[0],
                        'combo' => $p[1],
                    ];

                    break;
                }
                else {
                    $errors[] = __('texts.export.importing.deliveries.product_error', ['name' => $name]);
                }
            }
        }

        $bookings = [];
        $data = [];

        if (is_null($target_order)) {
            $errors[] = __('texts.export.importing.deliveries.order_error');
        }
        else {
            DB::beginTransaction();

            /*
                Qui salto le prime due righe, che nel CSV della Tabella Prodotti
                includono i nomi dei prodotti (che servono a rimapparli qui
                sulle relative quantità) ed i prezzi (che ignoro)
            */
            for ($i = 2; $i < count($csvdata); $i++) {
                $line = $csvdata[$i];

                try {
                    $datarow = [
                        'action' => 'saved',
                    ];

                    $target_user = null;

                    foreach ($columns as $index => $field) {
                        if ($field == 'username') {
                            $username = trim($line[$index]);
                            $target_user = User::where('username', $username)->first();
                            if ($target_user == null) {
                                break;
                            }
                        }
                        elseif ($index >= $first_product_index) {
                            $dataline = $this->translateProductsInLine($line, $first_product_index, $mapped_products);
                            $datarow = array_merge($datarow, $dataline);
                        }
                    }

                    if ($target_user) {
                        $booking = $service->handleBookingUpdate($datarow, $user, $target_order, $target_user, true);
                        if ($booking) {
                            $data[] = $datarow;
                            $bookings[] = (object) [
                                'user_id' => $target_user->id,
                                'user_name' => $target_user->printableName(),
                                'total' => $booking->getValue('effective', true),
                            ];
                        }
                    }
                }
                catch (\Exception $e) {
                    Log::warning('Errore in importazione consegne', ['exception' => $e]);
                    $errors[] = implode($target_separator, $line) . '<br/>' . $e->getMessage();
                }
            }

            DB::rollback();
        }

        return [
            'bookings' => $bookings,
            'aggregate_id' => $aggregate_id,
            'order_id' => $target_order ? $target_order->id : 0,
            'data' => $data,
            'errors' => $errors,
        ];
    }

    public function formatSelect($parameters)
    {
        return view('import.csvbookingsselect', $parameters);
    }

    private function pushBookingData($order, $data, $users, $delivering)
    {
        $user = Auth::user();
        $service = app()->make('BookingsService');

        $errors = [];
        $bookings = [];

        DB::beginTransaction();

        foreach ($data as $index => $datarow) {
            try {
                $target_user = User::find($users[$index]);
                $booking = $service->handleBookingUpdate($datarow, $user, $order, $target_user, $delivering);
                $bookings[] = $booking;
            }
            catch (\Exception $e) {
                $errors[] = $index . '<br/>' . $e->getMessage();
            }
        }

        DB::commit();

        return [$bookings, $errors];
    }

    public function run(array $request)
    {
        $data = json_decode($request['data'] ?? '[]', true);
        $users = $request['user'] ?? [];

        $order_id = $request['order_id'];
        $target_order = Order::findOrFail($order_id);

        list($bookings, $errors) = $this->pushBookingData($target_order, $data, $users, true);

        $action = $request['action'] ?? 'save';
        if ($action == 'close') {
            $user = Auth::user();
            app()->make('FastBookingsService')->fastShipping($user, $target_order->aggregate, null);
        }

        return [
            'title' => __('texts.export.importing.deliveries.done'),
            'objects' => $bookings,
            'errors' => $errors,
        ];
    }

    public function finalTemplate()
    {
        return 'import.csvimportbookingsfinal';
    }

    /**
     * Funzione di comodo per importare direttamente un file delle consegne in
     * modo non interattivo.
     * Si assume che il file CSV in ingresso abbia il formato della "Tabella
     * Complessiva Prodotti" con lo username nella prima colonna e tutti gli
     * altri dati a partire dalla seconda
     */
    public function directImportFromFile($order, $filepath)
    {
        /*
            Qui avrei potuto isolare da select() il codice di lettura del CSV
            e relativa conversione in prenotazioni, che vengono salvate con
            handleBookingUpdate() ma invalidate dalla transazione che isola gran
            parte della funzione stessa, ma in questo modo di fatto posso
            testare sia la funzione di lettura che di scrittura di tutto questo
            Importer
        */

        $parsed = $this->select([
            'aggregate_id' => $order->aggregate_id,
            'path' => $filepath,
            'column' => ['username', 'first'],
        ]);

        $data = $parsed['data'];
        $users = array_map(fn($b) => $b->user_id, $parsed['bookings']);

        $this->pushBookingData($order, $data, $users, false);
    }
}
