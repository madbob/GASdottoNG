<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Log;

use App\Notification;
use App\Date;
use App\User;
use App\Order;
use App\Role;

class NotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Notification'
        ]);
    }

    private function getNotifications($startdate, $enddate)
    {
        $user = Auth::user();

        $notifications_query = Notification::orderBy('start_date', 'desc');

        if (!is_null($startdate))
            $notifications_query->where('end_date', '>=', $startdate);
        if (!is_null($enddate))
            $notifications_query->where('start_date', '<=', $enddate);

        if ($user->can('notifications.admin', $user->gas) == false) {
            $notifications_query->whereHas('users', function($query) use ($user) {
                $query->where('users.id', $user->id);
            });
        }

        $notifications = $notifications_query->get();

        $dates_query = Date::where('type', 'internal');
        if (!is_null($startdate))
            $dates_query->where('date', '>=', $startdate);
        if (!is_null($enddate))
            $dates_query->where('date', '<=', $enddate);
        $dates = $dates_query->get();

        $all = $notifications->merge($dates)->sort(function($a, $b) {
            if (is_a($a, 'App\Notification'))
                $a_date = $a->start_date;
            else
                $a_date = $a->date;

            if (is_a($b, 'App\Notification'))
                $b_date = $b->start_date;
            else
                $b_date = $b->date;

            return $b_date <=> $a_date;
        });

        return $all;
    }

    public function index()
    {
        $notifications = $this->getNotifications(date('Y-m-d', strtotime('-1 years')), null);
        return view('pages.notifications', ['notifications' => $notifications]);
    }

    public function search(Request $request)
    {
        $startdate = decodeDate($request->input('startdate'));
        $enddate = decodeDate($request->input('enddate'));

        $notifications = $this->getNotifications($startdate, $enddate);

        return view('commons.loadablelist', [
            'identifier' => 'notification-list',
            'items' => $notifications,
        ]);
    }

    private function syncUsers($notification, $request)
    {
        $users = $request->input('users', []);
        if (empty($users)) {
            $us = User::whereNull('parent_id')->get();
            foreach ($us as $u) {
                $users[] = $u->id;
            }
        }
        else {
            $map = [];

            foreach ($users as $u) {
                if (strrpos($u, 'special::', -strlen($u)) !== false) {
                    if (strrpos($u, 'special::role::', -strlen($u)) !== false) {
                        $role_id = substr($u, strlen('special::role::'));
                        $role = Role::find($role_id);
                        foreach ($role->users as $u) {
                            $map[] = $u->id;
                        }
                    }
                    elseif (strrpos($u, 'special::order::', -strlen($u)) !== false) {
                        $order_id = substr($u, strlen('special::order::'));
                        $order = Order::findOrFail($order_id);
                        foreach ($order->topLevelBookings() as $booking) {
                            $map[] = $booking->user->id;
                        }
                    }
                } else {
                    $map[] = $u;
                }
            }

            $users = array_unique($map);
        }

        $notification->users()->sync($users, ['done' => false]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('notifications.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        if ($request->input('type') == 'date') {
            $n = new Date();
            $n->target_type = 'App\Gas';
            $n->target_id = $user->gas->id;
            $n->description = $request->input('content');
            $n->type = 'internal';
            $n->date = decodeDate($request->input('start_date'));
            $n->save();
        }
        else {
            $n = new Notification();
            $n->creator_id = $user->id;
            $n->content = $request->input('content');
            $n->mailed = $request->has('mailed');
            $n->start_date = decodeDate($request->input('start_date'));
            $n->end_date = decodeDate($request->input('end_date'));
            $n->save();

            self::syncUsers($n, $request);
            $n->sendMail();
        }

        return $this->commonSuccessResponse($n);
    }

    public function show($id)
    {
        $n = Notification::findOrFail($id);
        $user = Auth::user();

        if ($user->can('notifications.admin', $user->gas)) {
            return view('notification.edit', ['notification' => $n]);
        }
        else if ($n->hasUser($user)) {
            return view('notification.show', ['notification' => $n]);
        }
        else {
            return $this->errorResponse(_i('Non autorizzato'));
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('notifications.admin', $user->gas) == false)
            return $this->errorResponse(_i('Non autorizzato'));

        $n = Notification::findOrFail($id);
        $n->creator_id = $user->id;
        $n->content = $request->input('content');
        $n->mailed = $request->has('mailed');
        $n->start_date = decodeDate($request->input('start_date'));
        $n->end_date = decodeDate($request->input('end_date'));
        $n->save();

        self::syncUsers($n, $request);
        $n->sendMail();

        return $this->commonSuccessResponse($n);
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('notifications.admin', $user->gas) == false)
            return $this->errorResponse(_i('Non autorizzato'));

        DB::beginTransaction();

        $n = Notification::findOrFail($id);
        $n->users()->sync([]);
        $n->delete();

        return $this->successResponse();
    }

    public function markread($id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $n = Notification::findOrFail($id);

        if ($n->hasUser($user)) {
            $n->users()->where('user_id', '=', $user->id)->withPivot('done')->update(['done' => true]);
            return $this->successResponse();
        }
        else {
            return $this->errorResponse(_i('Non autorizzato'));
        }
    }
}
