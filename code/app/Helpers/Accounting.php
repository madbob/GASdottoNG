<?php

function movementTypes($identifier = null, $with_trashed = false)
{
    static $types = null;

    if (is_null($types)) {
        $query = App\MovementType::orderBy('name', 'asc');
        if ($with_trashed) {
            $query = $query->withTrashed();
        }

        $types = App\MovementType::initSystemTypes($query->get());
    }

    if ($identifier) {
        $ret = $types->where('id', $identifier)->first();
        if (is_null($ret)) {
            \Log::error('Richiesto tipo di movimento non esistente: ' . $identifier);
        }
    }
    else {
        $ret = $types;
    }

    return $ret;
}

function paymentTypes()
{
    $ret = [
        'cash' => (object) [
            'name' => _i('Contanti'),
            'identifier' => false,
            'icon' => 'cash',
            'active_for' => null,
            'valid_config' => function($target) {
                return true;
            }
        ],
        'bank' => (object) [
            'name' => _i('Bonifico'),
            'identifier' => true,
            'icon' => 'bank',
            'active_for' => null,
            'valid_config' => function($target) {
                return true;
            }
        ],
        'credit' => (object) [
            'name' => _i('Credito Utente'),
            'identifier' => false,
            'icon' => 'person-badge',
            'active_for' => 'App\User',
            'valid_config' => function($target) {
                return true;
            }
        ],
    ];

    $gas = currentAbsoluteGas();

    if($gas->hasFeature('paypal')) {
        $ret['paypal'] = (object) [
            'name' => _i('PayPal'),
            'identifier' => true,
            'icon' => 'cloud-plus',
            'active_for' => 'App\User',
            'valid_config' => function($target) {
                return true;
            }
        ];
    }

    if($gas->hasFeature('satispay')) {
        $ret['satispay'] = (object) [
            'name' => _i('Satispay'),
            'identifier' => true,
            'icon' => 'cloud-plus',
            'active_for' => 'App\User',
            'valid_config' => function($target) {
                return true;
            }
        ];
    }

    if($gas->hasFeature('rid')) {
        $ret['sepa'] = (object) [
            'name' => _i('SEPA'),
            'identifier' => true,
            'icon' => 'cloud-plus',
            'active_for' => 'App\User',
            'valid_config' => function($target) {
                return (get_class($target) == 'App\User' && !empty($target->rid['iban']));
            }
        ];
    }

    return $ret;
}

function paymentsSimple()
{
    $payments = paymentTypes();

    $ret = [
        'none' => _i('Non Specificato'),
    ];

    foreach($payments as $identifier => $meta) {
        $ret[$identifier] = $meta->name;
    }

    return $ret;
}

function paymentMethodByType($type)
{
    $movement_methods = paymentTypes();
    return $movement_methods[$type] ?? null;
}

function paymentsByType($type)
{
    $function = null;

    if ($type != null) {
        $metadata = movementTypes($type);
        if ($metadata)
            $function = json_decode($metadata->function);
    }

    $movement_methods = paymentTypes();
    $ret = [];

    foreach ($movement_methods as $method_id => $info) {
        $found = false;

        if ($function) {
            foreach($function as $f) {
                if ($f->method == $method_id) {
                    $found = true;
                    break;
                }
            }
        }
        else {
            $found = true;
        }

        if ($found) {
            $ret[$method_id] = $info->name;
        }
    }

    return $ret;
}

function defaultPaymentByType($type)
{
    $metadata = movementTypes($type);
    $function = json_decode($metadata->function);

    foreach($function as $f) {
        if (isset($f->is_default) && $f->is_default) {
            return $f->method;
        }
    }

    if (empty($function)) {
        return null;
    }
    else {
        return $function[0]->method;
    }
}
