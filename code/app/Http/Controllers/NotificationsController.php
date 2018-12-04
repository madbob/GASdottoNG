<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Log;

use App\Notification;
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

    public function index()
    {
        $user = Auth::user();
        if ($user->can('notifications.admin', $user->gas) == true) {
            $data['notifications'] = Notification::orderBy('start_date', 'desc')->take(20)->get();
        }
        else {
            $data['notifications'] = $user->allnotifications;
        }

        return view('pages.notifications', $data);
    }

    private function syncUsers($notification, $request)
    {
        $users = $request->input('users', []);
        if (empty($users)) {
            $us = User::get();
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

        /*
            TODO: gran parte di questo codice dovrà essere spostato
            direttamente nel modello Notification, o in un comando
            dedicato, per essere facilmente riutilizzato altrove
            (e.g. creando le notifiche di riepilogo ordini)
        */
        $n = new Notification();
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

    public function show($id)
    {
        $n = Notification::findOrFail($id);
        $user = Auth::user();

        if ($user->can('notifications.admin', $user->gas)) {
            return view('notification.edit', ['notification' => $n]);
        }
        else if ($n->hasUser($user) == false) {
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
