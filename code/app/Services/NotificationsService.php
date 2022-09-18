<?php

namespace App\Services;

use App\Notification;
use App\Date;
use App\User;
use App\Gas;

class NotificationsService extends BaseService
{
    public function list($start, $end)
    {
		$user = $this->ensureAuth();

		$notifications_query = Notification::orderBy('start_date', 'desc');

		if (!is_null($start)) {
			$notifications_query->where('end_date', '>=', $start);
		}

		if (!is_null($end)) {
			$notifications_query->where('start_date', '<=', $end);
		}

		if ($user->can('notifications.admin', $user->gas) == false) {
			$notifications_query->whereHas('users', function($query) use ($user) {
				$query->where('users.id', $user->id);
			});
		}

		$notifications = $notifications_query->get();

		$dates_query = Date::where('type', 'internal')->where('target_type', GAS::class)->where('target_id', $user->gas->id);

		if (!is_null($start)) {
			$dates_query->where('date', '>=', $start);
		}

		if (!is_null($end)) {
			$dates_query->where('date', '<=', $end);
		}

		$dates = $dates_query->get();

		$all = $notifications->merge($dates)->sort(function($a, $b) {
			if (is_a($a, Notification::class)) {
				$a_date = $a->start_date;
			}
			else {
				$a_date = $a->date;
			}

			if (is_a($b, Notification::class)) {
				$b_date = $b->start_date;
			}
			else {
				$b_date = $b->date;
			}

			return $b_date <=> $a_date;
		});

		return $all;
    }

    public function show($id)
    {
        return Notification::findOrFail($id);
    }

	private function syncUsers($notification, $request)
	{
		$users = $request['users'] ?? [];
		if (empty($users)) {
			$us = User::select('id')->whereNull('parent_id')->get();
			foreach ($us as $u) {
				$users[] = $u->id;
			}
		}
		else {
			$users = unrollSpecialSelectors($users);
		}

		$notification->users()->sync($users, ['done' => false]);
	}

    private function setCommonAttributes($notification, $request)
    {
		$this->setIfSet($notification, $request, 'content');
		$this->boolIfSet($notification, $request, 'mailed');
		$this->transformAndSetIfSet($notification, $request, 'start_date', "decodeDate");
		$this->transformAndSetIfSet($notification, $request, 'end_date', "decodeDate");
		$notification->save();

		$notification->attachByRequest($request);
		$this->syncUsers($notification, $request);
		$notification->sendMail();

		return $notification;
    }

    public function store(array $request)
    {
        $user = $this->ensureAuth(['notifications.admin' => 'gas']);

		/*
			Nota: le date sul calendario vengono create con lo stesso form per
			creare le notifiche, ma vengono poi visualizzare e modificate
			tramite DatesService
		*/
		if ($request['type'] == 'date') {
			$n = new Date();
			$n->target_type = Gas::class;
			$n->target_id = $user->gas->id;
			$n->description = $request['content'];
			$n->type = 'internal';
			$n->date = decodeDate($request['start_date']);
			$n->save();
		}
		else {
			$n = new Notification();
			$n->creator_id = $user->id;
			$n->gas_id = $user->gas_id;
			$n = $this->setCommonAttributes($n, $request);
		}

        return $n;
    }

    public function update($id, array $request)
    {
        $user = $this->ensureAuth(['notifications.admin' => 'gas']);
        $notification = Notification::findOrFail($id);
		$notification->creator_id = $user->id;
		$notification->gas_id = $user->gas_id;
        return $this->setCommonAttributes($notification, $request);
    }
}
