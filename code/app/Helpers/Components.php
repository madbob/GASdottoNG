<?php

/*
    Funzioni usate per i components Larastrap personalizzati
*/

function flaxComplexOptions($array)
{
    $options = [];
    $values = [];

    foreach ($array as $key => $meta) {
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

    $formatted_value = explode(' ', $params['value']);
    $formatted_value = $formatted_value[0] ?? '';

    $params['value'] = printableDate($formatted_value);
    if ($params['value'] == __('generic.never') && $mandatory) {
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

    foreach ($options as $option) {
        if (is_a($option, \App\Models\Concerns\HasChildren::class) && $option->children->count() != 0) {
            $ret[$option->id] = (object) [
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

function translateValueInComponent($params, $extraitem)
{
    $translated = [];

    if (! empty($params['value'])) {
        if (is_iterable($params['value'])) {
            foreach ($params['value'] as $option) {
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

    return $translated;
}

function formatObjectsToComponent($component, $params)
{
    $extraitem = $params['attributes']['extraitem'] ?? false;
    if ($extraitem) {
        if (is_array($extraitem)) {
            $params['extra_options'] = $extraitem;
        }
        else {
            $params['extra_options'] = [0 => $extraitem];
        }

        unset($params['attributes']['extraitem']);
    }

    $params['options'] = formatObjectsToComponentRec($params['options']);

    $params['value'] = translateValueInComponent($params, $extraitem);

    return $params;
}

function formatPriceToComponent($component, $params)
{
    if (isset($params['currency']) === false) {
        $currency = defaultCurrency()->symbol;
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
    $decimals = $params['attributes']['decimals'];
    $params['attributes']['data-trim-digits'] = $decimals;
    $params['value'] = sprintf('%.0' . $decimals . 'f', $params['value']);

    return $params;
}

function formatChecksComponentValues($component, $params)
{
    $values = [];
    $options = [];

    foreach ($params['options'] as $val => $meta) {
        if (! is_object($meta)) {
            return $params;
        }

        $options[$val] = $meta->name;
        $checked = $meta->checked ?? false;
        if ($checked) {
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

function formatUpdater($buttons, $params)
{
    $obj = $params['obj'];

    if ($obj && hasTrait($obj, \App\Models\Concerns\TracksUpdater::class)) {
        $exists = array_filter($buttons, fn ($b) => isset($b['element']) && $b['element'] == 'larastrap::updater');

        if (count($exists) == 0) {
            $buttons[] = [
                'element' => 'larastrap::updater',
            ];
        }
    }

    return $buttons;
}

function formatInnerLastUpdater($component, $params)
{
    $params['buttons'] = formatUpdater($params['buttons'], $params);

    return $params;
}

function appendSaveNotifier($params)
{
    $params['appendNodes'] = $params['appendNodes'] ?? [];

    /*
        Questo aggiunge al form la barra di notifica che viene attivata
        quando si modifica un qualche parametro ed è richiesto il
        salvataggio
    */
    $params['appendNodes'][] = sprintf('<div class="fixed-bottom bg-danger p-2 bottom-helper" hidden>
        <div class="row justify-content-end align-items-center">
            <div class="col-auto text-white">%s</div>
            <div class="col-auto">
                <button class="btn btn-success" type="submit">%s</button>
            </div>
        </div>
    </div>', __('generic.help.save_reminder'), __('generic.save'));

    return $params;
}

function mainFormButtons($params)
{
    $buttons = $params['attributes']['other_buttons'] ?? [];

    $buttons = formatUpdater($buttons, $params);
    $obj = $params['obj'];

    $nodelete = filter_var($params['attributes']['nodelete'] ?? false, FILTER_VALIDATE_BOOLEAN);
    if (! $nodelete) {
        $buttons[] = [
            'color' => 'danger',
            'classes' => ['delete-button'],
            'label' => $obj && $obj->deleted_at != null ? __('generic.definitive_delete') : __('generic.remove'),
        ];
    }

    $nosave = filter_var($params['attributes']['nosave'] ?? false, FILTER_VALIDATE_BOOLEAN);
    if (! $nosave) {
        $buttons[] = [
            'color' => 'success',
            'classes' => ['save-button'],
            'label' => __('generic.save'),
            'attributes' => ['type' => 'submit'],
        ];
    }

    $params['buttons'] = $buttons;

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
        $params = mainFormButtons($params);
        $params = appendSaveNotifier($params);
    }

    unset($params['attributes']['other_buttons'], $params['attributes']['nodelete'], $params['attributes']['nosave']);

    return $params;
}

function formatTabLabel($component, $params)
{
    if (isset($params['attributes']['icon'])) {
        if (strstr($params['attributes']['icon'], 'i class') === false) {
            $params['label'] = sprintf('<span class="d-none d-md-inline-block">%s</span><i class="%s d-block d-md-none"></i>', $params['label'], $params['attributes']['icon']);
        }
        else {
            $params['label'] = sprintf('<span class="d-none d-md-inline-block">%s</span>%s', $params['label'], $params['attributes']['icon']);
        }
    }

    return $params;
}
