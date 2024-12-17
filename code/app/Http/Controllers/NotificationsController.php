<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;


use App\Services\NotificationsService;

use App\Notification;

class NotificationsController extends BackedController
{
    public function __construct(NotificationsService $service)
    {
        $this->commonInit([
            'reference_class' => Notification::class,
            'service' => $service,
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
        return $this->easyExecute(function () use ($request, $id) {
            $n = $this->service->show($id);
            $user = $request->user();

            if ($user->can('notifications.admin', $user->gas)) {
                return view('notification.edit', ['notification' => $n]);
            }
            elseif ($n->hasUser($user)) {
                return view('notification.show', ['notification' => $n]);
            }
        });
    }

    public function markread($id)
    {
        return $this->easyExecute(function () use ($id) {
            $this->service->markread($id);
        });
    }
}
