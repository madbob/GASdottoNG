<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\User;
use App\Order;
use App\Supplier;
use App\Category;
use App\Booking;
use App\BookedProduct;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('pages.statistics');
    }

    private function sortData(&$data, &$categories)
    {
        usort($data, function ($a, $b) {
            return ($a->name <=> $b->name) * -1;
        });

        usort($categories, function ($a, $b) {
            return ($a->name <=> $b->name) * -1;
        });
    }

    private function formatJSON($data, $categories)
    {
        $ret = (object) [
            'expenses' => (object) [
                'labels' => [],
                'series' => [[]],
            ],
            'users' => (object) [
                'labels' => [],
                'series' => [[]],
            ],
            'categories' => (object) [
                'labels' => [],
                'series' => [[]],
            ],
        ];

        foreach ($data as $info) {
            $ret->expenses->labels[] = sprintf("%s\n%s", $info->name, printablePriceCurrency($info->value));
            $ret->expenses->series[0][] = $info->value;
            $ret->users->labels[] = $info->name;
            $ret->users->series[0][] = $info->users;
        }

        foreach ($categories as $info) {
            $ret->categories->labels[] = $info->name;
            $ret->categories->series[0][] = $info->value;
        }

        return $ret;
    }

    private function formatCSV($data)
    {
        $ret = [];
        $data = array_reverse($data);

        foreach ($data as $info) {
            $ret[] = [
                $info->name,
                $info->value,
                $info->users,
            ];
        }

        return $ret;
    }

    private function createBookingQuery($query, $type, $start, $end, $target, $supplier)
    {
        if ($type == 'all') {
            $query->where('bookings.updated_at', '>=', $start)->where('bookings.updated_at', '<=', $end);
        }
        else {
            $query->where('bookings.delivery', '!=', '0000-00-00')->where('bookings.delivery', '>=', $start)->where('bookings.delivery', '<=', $end);
        }

        if ($supplier) {
            $query->whereHas('order', function ($query) use ($supplier) {
                $query->where('supplier_id', '=', $supplier);
            });
        }

        if (is_a($target, User::class)) {
            $query->where('user_id', $target->id);
        }

        return $query;
    }

    /*
        Laddove le statistiche sul valore consegnato possono essere rapidamente
        estrapolate usando i valori consolidati sul DB (nell'attributo
        final_price di BookedProduct), per il prenotato è necessario rifare
        tutti i calcoli daccapo. Per ottenere i prezzi storicizzati al momento
        della creazione dell'ordine, o per contemplare le differenze di prezzo
        delle varianti
    */
    private function basicSummaryForAll($start, $end, $target)
    {
        $data = [];
        $bookings = $this->createBookingQuery(Booking::query(), 'all', $start, $end, $target, null)->angryload()->get();

        $orders = [];
        foreach ($bookings as $booking) {
            if (isset($orders[$booking->order_id]) === false) {
                $orders[$booking->order_id] = [];
            }

            $orders[$booking->order_id][] = $booking;
        }

        foreach ($orders as $id => $bookings) {
            $order = Order::find($id);
            $summary = $order->reduxData(['bookings' => $bookings]);

            $name = $order->supplier_id;
            if (isset($data[$name]) === false) {
                $data[$name] = (object) [
                    'users' => 0,
                    'value' => 0,
                    'name' => $order->supplier->printableName(),
                ];
            }

            $data[$name]->value += $summary->price;
        }

        return $data;
    }

    private function basicSummaryForShipped($start, $end, $type, $target)
    {
        $data = [];

        $data_for_suppliers = BookedProduct::selectRaw('orders.supplier_id, SUM(final_price) as price')->whereHas('booking', function ($query) use ($type, $start, $end, $target) {
            $this->createBookingQuery($query, $type, $start, $end, $target, null);
        })->join('bookings', 'booked_products.booking_id', '=', 'bookings.id')->join('orders', 'bookings.order_id', '=', 'orders.id')->groupBy('orders.supplier_id')->get();

        foreach ($data_for_suppliers as $dfs) {
            $name = $dfs->supplier_id;
            if (isset($data[$name]) === false) {
                $data[$name] = (object) [
                    'users' => 0,
                    'value' => 0,
                    'name' => Supplier::tFind($name)->printableName(),
                ];
            }

            $data[$name]->value += $dfs->price;
        }

        return $data;
    }

    private function getSummary($start, $end, $type, $target)
    {
        if ($type == 'all') {
            $data = $this->basicSummaryForAll($start, $end, $target);
        }
        else {
            $data = $this->basicSummaryForShipped($start, $end, $type, $target);
        }

        $data_for_user = $this->createBookingQuery(Booking::query(), $type, $start, $end, $target, null)->whereHas('user', function ($query) {
            $query->whereNull('parent_id');
        })->selectRaw('supplier_id, COUNT(DISTINCT(bookings.user_id)) as total')->join('orders', 'bookings.order_id', '=', 'orders.id')->groupBy('supplier_id')->get();

        foreach ($data_for_user as $dfu) {
            $name = $dfu->supplier_id;
            if (isset($data[$name])) {
                $data[$name]->users += $dfu->total;
            }
        }

        $categories = [];

        if ($type == 'all') {
            $price_column = 'price';
        }
        else {
            $price_column = 'final_price';
        }

        $data_for_categories = BookedProduct::selectRaw('product_id, SUM(' . $price_column . ') as price, category_id')->whereHas('booking', function ($query) use ($type, $start, $end, $target) {
            $this->createBookingQuery($query, $type, $start, $end, $target, null);
        })->join('products', 'booked_products.product_id', '=', 'products.id')->groupBy('product_id', 'category_id')->get();

        $all_categories = Category::all();

        foreach ($data_for_categories as $dfc) {
            $category_id = $dfc->category_id;

            if (! isset($categories[$category_id])) {
                $category = $all_categories->find($category_id);
                $categories[$category_id] = (object) [
                    'value' => 0,
                    'name' => $category->printableName(),
                ];
            }

            $categories[$category_id]->value += $dfc->price;
        }

        return [$data, $categories];
    }

    private function getSupplier($start, $end, $type, $target, $supplier)
    {
        $data = [];
        $categories = [];

        $bookings = $this->createBookingQuery(Booking::query(), $type, $start, $end, $target, $supplier)->with(['order', 'products'])->get();

        foreach ($bookings as $booking) {
            foreach ($booking->products as $product) {
                $name = $product->product_id;

                if (isset($data[$name]) === false) {
                    $data[$name] = (object) [
                        'users' => [],
                        'value' => 0,
                        'name' => $product->product->printableName(),
                    ];
                }

                if (! isset($categories[$product->product->category_id])) {
                    $categories[$product->product->category_id] = (object) [
                        'value' => 0,
                        'name' => $product->product->category->printableName(),
                    ];
                }

                $data[$name]->users[$booking->user_id] = true;

                if ($type == 'all') {
                    $price = $product->product->price;
                }
                else {
                    $price = $product->final_price;
                }

                $data[$name]->value += $price;
                $categories[$product->product->category_id]->value += $price;
            }
        }

        foreach ($data as $product => $meta) {
            $data[$product]->users = count($meta->users);
        }

        return [$data, $categories];
    }

    public function show(Request $request, $id)
    {
        $start = decodeDate($request->input('startdate'));
        $end = decodeDate($request->input('enddate'));
        $target = fromInlineId($request->input('target'));
        $type = $request->input('type') ?: 'shipped';
        $csv_headers = [];

        if ($id == 'summary') {
            [$data, $categories] = $this->getSummary($start, $end, $type, $target);
            $csv_headers = [
                __('orders.supplier'),
                _i('Valore Ordini'),
                _i('Utenti Coinvolti')
            ];
        }
        else {
            $supplier = $request->input('supplier');
            [$data, $categories] = $this->getSupplier($start, $end, $type, $target, $supplier);

            $csv_headers = [
                __('products.name'),
                _i('Valore Ordini'),
                _i('Utenti Coinvolti')
            ];
        }

        $this->sortData($data, $categories);

        $format = $request->input('format', 'csv');
        if ($format == 'json') {
            $data = $this->formatJSON($data, $categories);

            return json_encode($data);
        }
        else {
            $data = $this->formatCSV($data);

            return output_csv(_i('Statistiche %s.csv', [date('Y-m-d')]), $csv_headers, $data, null);
        }
    }
}
