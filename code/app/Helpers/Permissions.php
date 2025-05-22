<?php

function allPermissions()
{
    $ret = [
        'App\Gas' => [
            'gas.access' => __('permissions.permissions.maintenance_access'),
            'gas.permissions' => __('permissions.permissions.alter_permissions'),
            'gas.config' => __('permissions.permissions.alter_configs'),
            'supplier.add' => __('permissions.permissions.create_suppliers'),
            'supplier.book' => __('permissions.permissions.do_booking'),
            'supplier.view' => __('permissions.permissions.view_suppliers'),
            'order.view' => __('permissions.permissions.view_orders'),
            'users.self' => __('permissions.permissions.alter_self'),
            'users.selfdestroy' => __('permissions.permissions.delete_account'),
            'users.admin' => __('permissions.permissions.admin_users'),
            'users.view' => __('permissions.permissions.view_users'),
            'users.subusers' => __('permissions.permissions.sub_users'),
            'users.movements' => __('permissions.permissions.admin_user_movements'),
            'movements.admin' => __('permissions.permissions.admin_movements'),
            'movements.view' => __('permissions.permissions.view_movements'),
            'movements.types' => __('permissions.permissions.admin_movements_types'),
            'categories.admin' => __('permissions.permissions.admin_categories'),
            'measures.admin' => __('permissions.permissions.admin_measures'),
            'gas.statistics' => __('permissions.permissions.view_statistics'),
            'notifications.admin' => __('permissions.permissions.admin_notifications'),
        ],
        'App\Supplier' => [
            'supplier.modify' => __('permissions.permissions.alter_suppliers'),
            'supplier.orders' => __('permissions.permissions.open_orders'),
            'supplier.shippings' => __('permissions.permissions.do_deliveries'),
            'supplier.invoices' => __('permissions.permissions.admin_invoices'),
            'supplier.movements' => __('permissions.permissions.admin_supplier_movements'),
        ],
    ];

    /*
        In fase di prima installazione con Composer, per vie traverse si
        transita da questa funzione (durante l'inizializzazione dei Service
        Provider). Ma in tale sede la connessione al DB ragionevolmente non è
        ancora stata configurata, pertanto non è possibile attingere ad
        eventuali configurazioni dinamiche
    */
    try {
        $gas = currentAbsoluteGas();

        if (isset($gas)) {
            $gas = $gas->fresh();

            if ($gas->multigas) {
                $ret['App\Gas']['gas.multi'] = __('permissions.permissions.admin_multigas');
            }
        }
    }
    catch (\Exception $e) {
        // dummy
    }

    return $ret;
}

function allRoles()
{
    return App\Role::orderBy('name', 'asc')->get()->filter(function ($r) {
        return $r->isEnabled();
    });
}

function someoneCan($permission, $subject = null)
{
    $basic_roles = App\Role::havingAction($permission);

    foreach ($basic_roles as $br) {
        if (is_null($subject)) {
            $users = $br->users;
        }
        else {
            $users = $br->usersByTarget($subject);
        }

        if ($users->isEmpty() === false) {
            return true;
        }
    }

    return false;
}

function everybodyCan($permission, $subject = null)
{
    $ret = new Illuminate\Support\Collection();

    $basic_roles = App\Role::havingAction($permission);
    foreach ($basic_roles as $br) {
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
        if (isset($rules[$rule_id])) {
            return $class;
        }
    }

    return null;
}

function rolesByClass($asked_class)
{
    $roles = [];
    $all_permissions = allPermissions();
    $rules = $all_permissions[$asked_class] ?? [];

    foreach (allRoles() as $role) {
        foreach (array_keys($rules) as $identifier) {
            if ($role->enabledAction($identifier)) {
                $roles[] = $role;
                break;
            }
        }
    }

    return $roles;
}

function roleByIdentifier($identifier)
{
    return App\Role::where('identifier', $identifier)->first();
}

/*
    Questa funzione esiste sostanzialmente allo scopo di intercettare e
    aggiustare problemi con le configurazioni dei ruoli essenziali di sistema,
    che possono essere compromessi in molti modi (intervento inopportuno
    dell'utente, bug, inizializzazione incompleta o altro)
*/
function roleByFunction($identifier)
{
    $gas = currentAbsoluteGas();
    $role_id = $gas->roles[$identifier] ?? null;
    $ret = App\Role::find($role_id);

    if (is_null($ret)) {
        switch ($identifier) {
            case 'multigas':
                $ridentifier = 'secondary_admin';
                break;
            default:
                $ridentifier = $identifier;
                break;
        }

        $ret = roleByIdentifier($ridentifier);
        if (is_null($ret)) {
            $role_definition = systemParameters('Roles')[$ridentifier] ?? null;
            if ($role_definition) {
                \Log::info('Inizializzo ruolo di sistema non ancora inizializzato: ' . $identifier);
                $role_definition->create();

                return roleByFunction($identifier);
            }
            else {
                throw new \Exception('Impossibile ricostruire il ruolo ' . $identifier, 1);
            }
        }

        \Log::info('Aggiusto configurazione per i ruoli: ' . $identifier);
        $conf = (object) $gas->roles;
        $conf->$identifier = $ret->id;
        $gas->setConfig('roles', $conf);
    }

    return $ret;
}
