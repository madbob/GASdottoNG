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
            'title' => __('texts.tour.welcome.title'),
            'content' => __('texts.tour.welcome.body'),
        ];

        $steps[] = (object) [
            'title' => __('texts.tour.profile.title'),
            'content' => __('texts.tour.profile.body'),
            'target' => '#menu_profile',
        ];

        if ($user->can('users.admin', $gas)) {
            $steps[] = (object) [
                'title' => __('texts.tour.users.title'),
                'content' => __('texts.tour.users.body'),
                'target' => '#menu_users',
            ];
        }

        if ($user->can('supplier.add', $gas) || $user->can('supplier.modify', null)) {
            $steps[] = (object) [
                'title' => __('texts.tour.suppliers.title'),
                'content' => __('texts.tour.suppliers.body'),
                'target' => '#menu_suppliers',
            ];
        }

        if ($user->can('supplier.orders', null) || $user->can('supplier.shippings', null)) {
            $steps[] = (object) [
                'title' => __('texts.tour.orders.title'),
                'content' => __('texts.tour.orders.body'),
                'target' => '#menu_orders',
            ];
        }

        if ($user->can('supplier.book', null)) {
            $steps[] = (object) [
                'title' => __('texts.tour.bookings.title'),
                'content' => __('texts.tour.bookings.body'),
                'target' => '#menu_bookings',
            ];
        }

        if ($user->can('movements.view', $gas) || $user->can('movements.admin', $gas)) {
            $steps[] = (object) [
                'title' => __('texts.tour.accounting.title'),
                'content' => __('texts.tour.accounting.body'),
                'target' => '#menu_accouting',
            ];
        }

        if ($user->can('gas.config', $gas)) {
            $steps[] = (object) [
                'title' => __('texts.tour.config.title'),
                'content' => __('texts.tour.config.body'),
                'target' => '#menu_config',
            ];
        }

        $steps[] = (object) [
            'title' => __('texts.tour.inline.title'),
            'content' => __('texts.tour.inline.body') . '<br><img class="img-fluid p-2 mt-2 bg-dark" src="' .  . '">',
        ];

        if ($user->can('users.admin', $gas)) {
            $steps[] = (object) [
                'title' => __('texts.tour.last.title'),
                'content' => __('texts.tour.last.body'),
                'target' => '#menu_help',
            ];
        }

        return response()->json([
            'dialogZ' => 2000,
            'nextLabel' => '>>',
            'prevLabel' => '<<',
            'finishLabel' => __('texts.tour.finished'),
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
