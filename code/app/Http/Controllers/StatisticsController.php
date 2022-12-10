<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
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
        usort($data, function($a, $b) {
            return ($a->name <=> $b->name) * -1;
        });

        usort($categories, function($a, $b) {
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
            $ret->users->series[0][] = count($info->users);
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
                count($info->users),
            ];
        }

        return $ret;
    }

    private function createBookingQuery($query, $start, $end, $target, $supplier)
    {
        $query->where('delivery', '!=', '0000-00-00')->where('delivery', '>=', $start)->where('delivery', '<=', $end);

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

    private function getSummary($start, $end, $target)
    {
        $data = [];
        $categories = [];

        $bookings = $this->createBookingQuery(Booking::query(), $start, $end, $target, null)->with('order', 'products')->get();

        foreach ($bookings as $booking) {
            $name = $booking->order->supplier_id;
            if (isset($data[$name]) == false) {
                $data[$name] = (object) [
                    'users' => [],
                    'value' => 0,
                    'name' => $booking->order->supplier->printableName()
                ];
            }

            $data[$name]->users[$booking->user_id] = true;
            $data[$name]->value += $booking->getValue('delivered', true);
        }

        $data_for_categories = BookedProduct::selectRaw('product_id, SUM(final_price) as price')->whereHas('booking', function($query) use ($start, $end, $target) {
            $this->createBookingQuery($query, $start, $end, $target, null);
        })->with('product', 'product.category')->groupBy('product_id')->get();

        foreach($data_for_categories as $dfc) {
            $category_id = $dfc->product->category_id;

            if (!isset($categories[$category_id])) {
                $categories[$category_id] = (object) [
                    'value' => 0,
                    'name' => $dfc->product->category->printableName(),
                ];
            }

            $categories[$category_id]->value += $dfc->price;
        }

        return [$data, $categories];
    }

    private function getSupplier($start, $end, $target, $supplier)
    {
        $data = [];
        $categories = [];

        $bookings = $this->createBookingQuery(Booking::query(), $start, $end, $target, $supplier)->with('order', 'products')->get();

        foreach ($bookings as $booking) {
            foreach ($booking->products as $product) {
                $name = $product->product_id;

                if (isset($data[$name]) == false) {
                    $data[$name] = (object) [
                        'users' => [],
                        'value' => 0,
                        'name' => $product->product->printableName(),
                    ];
                }

                if (!isset($categories[$product->product->category_id])) {
                    $categories[$product->product->category_id] = (object) [
                        'value' => 0,
                        'name' => $product->product->category->printableName(),
                    ];
                }

                $data[$name]->users[$booking->user_id] = true;
                $data[$name]->value += $product->final_price;
                $categories[$product->product->category_id]->value += $product->final_price;
            }
        }

        return [$data, $categories];
    }

    public function show(Request $request, $id)
    {
        $start = decodeDate($request->input('startdate'));
        $end = decodeDate($request->input('enddate'));
        $target = fromInlineId($request->input('target'));
        $csv_headers = [];

        switch ($id) {
            case 'summary':
                list($data, $categories) = $this->getSummary($start, $end, $target);
                $csv_headers = [_i('Fornitore'), _i('Valore Ordini'), _i('Utenti Coinvolti')];
                break;

            case 'supplier':
                $supplier = $request->input('supplier');
                list($data, $categories) = $this->getSupplier($start, $end, $target, $supplier);
                $csv_headers = [_i('Prodotto'), _i('Valore Ordini'), _i('Utenti Coinvolti')];
                break;
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
