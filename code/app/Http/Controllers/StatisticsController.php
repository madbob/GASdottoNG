<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;

use App\Booking;

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

    public function show(Request $request, $id)
    {
        $start = decodeDate($request->input('start'));
        $end = decodeDate($request->input('end'));
        $data = [];

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
                    $data[$name]->value += $booking->delivered;
                }

                $ret = (object) [
                    'expenses' => (object) [
                        'labels' => [],
                        'series' => [[]],
                    ],
                    'users' => (object) [
                        'labels' => [],
                        'series' => [[]],
                    ],
                ];

                krsort($data);

                foreach ($data as $info) {
                    $ret->expenses->labels[] = sprintf('%s<br>%s', $info->name, printablePriceCurrency($info->value));
                    $ret->expenses->series[0][] = $info->value;
                    $ret->users->labels[] = $info->name;
                    $ret->users->series[0][] = count($info->users);
                }

                $data = $ret;
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

                            $data[$name]->users[$booking->user_id] = true;
                            $data[$name]->value += $product->final_price;
                        }
                    }

                    $ret = (object) [
                        'expenses' => (object) [
                            'labels' => [],
                            'series' => [[]],
                        ],
                        'users' => (object) [
                            'labels' => [],
                            'series' => [[]],
                        ],
                    ];

                    usort($data, function($a, $b) {
                        return ($a->name <=> $b->name) * -1;
                    });

                    foreach ($data as $info) {
                        $ret->expenses->labels[] = sprintf('%s<br>%s', $info->name, printablePriceCurrency($info->value));
                        $ret->expenses->series[0][] = $info->value;
                        $ret->users->labels[] = $info->name;
                        $ret->users->series[0][] = count($info->users);
                    }

                    $data = $ret;
                    break;
        }

        return json_encode($data);
    }
}
