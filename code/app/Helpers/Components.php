<?php

/*
    Funzioni usate per i components Larastrap personalizzati
*/

function flaxComplexOptions($array)
{
    $options = [];
    $values = [];

    foreach($array as $key => $meta) {
        $options[$key] = $meta->name;
        if ($meta->checked ?? false) {
            $values[] = $key;
        }
    }

    return [$options, $values];
}

function formatDateToComponent($component, $params)
{
    $mandatory = $params['required'];

    $defaults_now = $params['attributes']['defaults_now'] ?? false;
    if ($defaults_now) {
        $defaults_now = filter_var($defaults_now, FILTER_VALIDATE_BOOLEAN);
        unset($params['attributes']['defaults_now']);
    }

    if (empty($params['value'])) {
        if ($defaults_now) {
            $params['value'] = date('Y-m-d G:i:s');
        }
    }

    $params['value'] = printableDate($params['value']);
    if ($params['value'] == _i('Mai') && $mandatory) {
        $params['value'] = '';
    }

    $readonly = $params['disabled'] || $params['readonly'];
    if ($readonly) {
        $params['textappend'] = null;
    }

    return $params;
}

function formatObjectsToComponentRec($options)
{
    $ret = [];

    foreach($options as $option) {
        if (is_a($option, 'App\Hierarchic') && $option->children->count() != 0) {
            $ret[$option->id] = (object) [
                // printableName() è una funzione di GASModel, estesa da tutte
                // le classi che usano App\Hierarchic
                // @phpstan-ignore-next-line
                'label' => $option->printableName(),
                'children' => formatObjectsToComponentRec($option->children),
            ];
        }
        else {
            $ret[$option->id] = $option->printableName();
        }
    }

    return $ret;
}

function formatObjectsToComponent($component, $params)
{
    $translated = [];

    $extraitem = $params['attributes']['extraitem'] ?? false;
    if ($extraitem) {
        if (is_array($extraitem)) {
            $translated = $extraitem;
        }
        else {
            $translated['0'] = $extraitem;
        }

        unset($params['attributes']['extraitem']);
    }

    $translated = $translated + formatObjectsToComponentRec($params['options']);

    $params['options'] = $translated;

    $translated = [];

    if (!empty($params['value'])) {
        if (is_iterable($params['value'])) {
            foreach($params['value'] as $option) {
                $translated[] = $option->id;
            }
        }
        else {
            $translated[] = $params['value']->id ?? $params['value'];
        }
    }

    if (empty($translated) && $extraitem) {
        $translated[] = '0';
    }

    $params['value'] = $translated;

    return $params;
}

function formatPriceToComponent($component, $params)
{
    $value = printablePrice($params['value']);

    if (!isset($params['currency'])) {
        $currency = currentAbsoluteGas()->currency;
    }
    else {
        if ($params['currency'] != '0') {
            $c = App\Currency::find($params['currency']);
            $currency = $c->symbol;
        }
        else {
            $currency = '';
        }

        unset($params['currency']);
    }

    $params['value'] = printablePrice($params['value']);
    $params['textappend'] = $currency;

    return $params;
}

function formatDecimalToComponent($component, $params)
{
    $decimals = $params['decimals'];
    $params['classes'][] = 'trim-' . $decimals . '-ddigits';
    $params['value'] = sprintf('%.0' . $decimals . 'f', $params['value']);
    return $params;
}

function formatChecksComponentValues($component, $params)
{
    $values = [];
    $options = [];

    foreach($params['options'] as $val => $meta) {
        if (!is_object($meta)) {
            return $params;
        }

        $options[$val] = $meta->name;
        if ($meta->checked ?? false) {
            $values[] = $val;
        }
    }

    $params['options'] = $options;
    $params['value'] = $values;

    return $params;
}

function formatPeriodicToComponent($component, $params)
{
    $params['value'] = printablePeriodic($params['value']);
    return $params;
}

function formatMainFormButtons($component, $params)
{
    /*
        Da questa funzione passo due volte, ma la prima volta i pulsanti vengono
        passati all'eventuale modale che contiene il form e la seconda restano
        nel form stesso.
        Sicché forzo un flag tra i parametri, per tenere traccia
        dell'operazione, e fare in modo che i pulsanti restino da una parte sola
        se necessario.
    */
    if (isset($params['main_form_managed'])) {
        unset($params['main_form_managed']);
    }
    else {
        $params['main_form_managed'] = 'ongoing';

        $other_buttons = $params['attributes']['other_buttons'] ?? [];
        if (!empty($other_buttons)) {
            $buttons = $other_buttons;
        }
        else {
            $buttons = [];
        }

        $nodelete = filter_var($params['attributes']['nodelete'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!$nodelete) {
            $obj = $params['obj'];

            $buttons[] = [
                'color' => 'danger',
                'classes' => ['delete-button'],
                'label' => $obj && $obj->deleted_at != null ? _i('Elimina Definitivamente') : _i('Elimina'),
            ];
        }

        $nosave = filter_var($params['attributes']['nosave'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (!$nosave) {
            $buttons[] = [
                'color' => 'success',
                'classes' => ['save-button'],
                'label' => _i('Salva'),
                'attributes' => ['type' => 'submit'],
            ];
        }

        $params['buttons'] = $buttons;
    }

    unset($params['attributes']['other_buttons']);
    unset($params['attributes']['nodelete']);
    unset($params['attributes']['nosave']);

    return $params;
}

function formatForDuskTesting($component, $params)
{
    /*
        Questo viene settato nel file .env.dusk.local e si attiva eseguendo i
        test Dusk
    */
    if (env('DUSK_TESTING', false)) {
        $options = $params['options'];
        $new_options = [];

        foreach($options as $value => $option) {
            if (is_object($option)) {
                if (!isset($option->button_attributes)) {
                    $option->button_attributes = [];
                }
            }
            else {
                $option = (object) [
                    'label' => $option,
                    'button_attributes' => [],
                ];
            }

            $option->button_attributes['dusk'] = sprintf('%s-%s', $params['name'], $value);
            $new_options[$value] = $option;
        }

        $params['options'] = $new_options;
    }

    return $params;
}
