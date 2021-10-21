<?php

function allPermissions()
{
    return [
        'App\Gas' => [
            'gas.access' => _i('Accesso consentito anche in manutenzione'),
            'gas.permissions' => _i('Modificare tutti i permessi'),
            'gas.config' => _i('Modificare le configurazioni del GAS'),
            'supplier.add' => _i('Creare nuovi fornitori'),
            'supplier.book' => _i('Effettuare ordini'),
            'supplier.view' => _i('Vedere tutti i fornitori'),
            'order.view' => _i('Vedere tutti gli ordini'),
            'users.self' => _i('Modificare la propria anagrafica'),
            'users.admin' => _i('Amministrare gli utenti'),
            'users.view' => _i('Vedere tutti gli utenti'),
            'users.subusers' => _i('Avere sotto-utenti con funzioni limitate'),
            'users.movements' => _i('Amministrare i movimenti contabili degli utenti'),
            'movements.admin' => _i('Amministrare tutti i movimenti contabili'),
            'movements.view' => _i('Vedere i movimenti contabili'),
            'movements.types' => _i('Amministrare i tipi dei movimenti contabili'),
            'categories.admin' => _i('Amministrare le categorie'),
            'measures.admin' => _i('Amministrare le unità di misura'),
            'gas.statistics' => _i('Visualizzare le statistiche'),
            'notifications.admin' => _i('Amministrare le notifiche'),
            'gas.multi' => _i('Amministrare i GAS su questa istanza'),
        ],
        'App\Supplier' => [
            'supplier.modify' => _i('Modificare i fornitori assegnati'),
            'supplier.orders' => _i('Aprire e modificare ordini'),
            'supplier.shippings' => _i('Effettuare le consegne'),
            'supplier.movements' => _i('Amministrare i movimenti contabili'),
        ],
    ];
}

function someoneCan($permission, $subject = null)
{
    $basic_roles = App\Role::havingAction($permission);
    foreach($basic_roles as $br) {
        if (is_null($subject)) {
            $users = $br->users;
            if ($users->isEmpty() == false)
                return true;
            else
                return false;
        }
        else {
            $users = $br->usersByTarget($subject);
            if ($users->isEmpty() == false)
                return true;
        }
    }

    return false;
}

function everybodyCan($permission, $subject = null)
{
    $ret = new Illuminate\Support\Collection();

    $basic_roles = App\Role::havingAction($permission);
    foreach($basic_roles as $br) {
        $users = $br->users;

        if ($subject != null) {
            $users = $br->usersByTarget($subject);
        }

        $ret = $ret->merge($users);
    }

    return $ret->unique('id');
}

function classByRule($rule_id)
{
    $all_permissions = allPermissions();
    foreach ($all_permissions as $class => $rules) {
        foreach ($rules as $identifier => $name) {
            if ($rule_id == $identifier) {
                return $class;
            }
        }
    }

    return null;
}

function rolesByClass($asked_class)
{
    $roles = [];
    $all_permissions = allPermissions();

    foreach (App\Role::all() as $role) {
        foreach ($all_permissions as $class => $rules) {
            if ($class == $asked_class) {
                foreach ($rules as $identifier => $name) {
                    if ($role->enabledAction($identifier)) {
                        $roles[] = $role;
                        break;
                    }
                }
            }
        }
    }

    return $roles;
}
