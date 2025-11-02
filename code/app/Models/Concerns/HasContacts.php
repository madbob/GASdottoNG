<?php

/*
    Usato per gestire i contatti relativi ad un ordine
*/

namespace App\Models\Concerns;

use Illuminate\Support\Collection;

use App\User;
use App\Role;

trait HasContacts
{
    public function showableContacts()
    {
        $gas = currentAbsoluteGas();
        $ret = null;

        switch ($gas->booking_contacts) {
            case 'none':
                $ret = new Collection();
                break;

            case 'manual':
                $ret = $this->users;
                break;

            default:
                $role = Role::find($gas->booking_contacts);
                if ($role) {
                    $ret = $role->usersByTarget($this->supplier);
                }
                else {
                    $ret = new Collection();
                }
        }

        return $ret;
    }

    public function enforcedContacts()
    {
        return $this->innerCache('enforced_contacts', function ($obj) {
            $contacts = $obj->showableContacts();
            if ($contacts->isEmpty()) {
                $contacts = everybodyCan('supplier.orders', $obj->supplier);
            }

            return $contacts;
        });
    }

    public function notifiableUsers($gas)
    {
        $order = $this;

        if ($gas->notify_all_new_orders) {
            $query_users = User::whereNull('parent_id');
        }
        else {
            $query_users = User::whereHas('suppliers', function ($query) use ($order) {
                $query->where('suppliers.id', $order->supplier->id);
            });
        }

        $query_users->fullEnabled();

        $user_circles = $order->circles()->whereHas('group', function ($query) {
            $query->where('context', 'user')->where('filters_orders', true);
        })->pluck('id');

        if ($user_circles->isEmpty() === false) {
            $query_users->whereHas('circles', function ($query) use ($user_circles) {
                $query->whereIn('id', $user_circles);
            });
        }

        $query_users->whereHas('contacts', function ($query) {
            $query->where('type', 'email');
        });

        return $query_users->get();
    }
}
