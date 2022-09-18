<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Carbon\Carbon;

use DB;
use Auth;

use App\Services\NotificationsService;

use App\Notification;

class NotificationsController extends BackedController
{
    public function __construct(NotificationsService $service)
    {
        $this->commonInit([
            'reference_class' => Notification::class,
            'service' => $service
        ]);
    }

    public function index()
    {
        $notifications = $this->service->list(Carbon::now()->subYears(1), null);
        return view('pages.notifications', ['notifications' => $notifications]);
    }

    public function search(Request $request)
    {
        $startdate = decodeDate($request->input('startdate'));
        $enddate = decodeDate($request->input('enddate'));

        $notifications = $this->service->list($startdate, $enddate);

        return view('commons.loadablelist', [
            'identifier' => 'notification-list',
            'items' => $notifications,
        ]);
    }

    public function show(Request $request, $id)
    {
        try {
            $n = $this->service->show($id);
            $user = $request->user();

            if ($user->can('notifications.admin', $user->gas)) {
                return view('notification.edit', ['notification' => $n]);
            }
            else if ($n->hasUser($user)) {
                return view('notification.show', ['notification' => $n]);
            }
        }
        catch (AuthException $e) {
            abort($e->status());
        }
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
