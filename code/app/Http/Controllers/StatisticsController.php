<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;

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

    private function formatResults($data, $categories)
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

        usort($data, function($a, $b) {
            return ($a->name <=> $b->name) * -1;
        });

        usort($categories, function($a, $b) {
            return ($a->name <=> $b->name) * -1;
        });

        foreach ($data as $info) {
            $ret->expenses->labels[] = sprintf('%s<br>%s', $info->name, printablePriceCurrency($info->value));
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

    public function show(Request $request, $id)
    {
        $start = decodeDate($request->input('start'));
        $end = decodeDate($request->input('end'));
        $data = [];
        $categories = [];

        switch ($id) {
            case 'summary':
                $bookings = Booking::where('delivery', '!=', '0000-00-00')->where('delivery', '>=', $start)->where('delivery', '<=', $end)->with('order')->get();
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

                $products_cache = [];
                $data_for_categories = BookedProduct::selectRaw('product_id, SUM(final_price) as price')->whereIn('booking_id', $bookings->pluck('id'))->with('product')->groupBy('product_id')->get();
                foreach($data_for_categories as $dfc) {
                    if (!isset($products_cache[$dfc->product_id]))
                        $products_cache[$dfc->product_id] = $dfc->product->category_id;

                    $category_id = $products_cache[$dfc->product_id];

                    if (!isset($categories[$category_id])) {
                        $categories[$category_id] = (object) [
                            'value' => 0,
                            'name' => $dfc->product->category->printableName(),
                        ];
                    }

                    $categories[$category_id]->value += $dfc->price;
                }

                $data = $this->formatResults($data, $categories);
                break;

            case 'supplier':
                $supplier = $request->input('supplier');

                $bookings = Booking::where('delivery', '!=', '0000-00-00')->where('delivery', '>=', $start)->where('delivery', '<=', $end)->whereHas('order', function ($query) use ($supplier) {
                    $query->where('supplier_id', '=', $supplier);
                })->with('order')->get();

                foreach ($bookings as $booking) {
                    foreach ($booking->products as $product) {
                        $name = $product->product->id;

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

                $data = $this->formatResults($data, $categories);
                break;
        }

        return json_encode($data);
    }
}
