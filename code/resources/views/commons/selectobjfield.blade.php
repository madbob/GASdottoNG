<?php

if (function_exists('recursiveOptionsSelectObj') == false) {
    function recursiveOptionsSelectObj($obj, $options, $indent, $name, $multiple, $datafields)
    {
        $option_prefix = str_repeat('&nbsp;&nbsp;', $indent);
        foreach ($options as $o) {
            if ($multiple) {
                $selected = false;

                if ($obj) {
                    foreach ($obj->$name as $n) {
                        if ($n->id == $o->id) {
                            $selected = true;
                            break;
                        }
                    }
                }
            } else {
                $selected = ($obj && $obj->$name == $o->id);
            }

            $attributes = [];
            if ($selected)
                $attributes[] = 'selected="selected"';
            foreach($datafields as $d)
                $attributes[] = 'data-' . $d . '="' . $o->$d . '"';

            echo '<option value="' . $o->id . '" ' . join(' ', $attributes) . '>' . $option_prefix . $o->printableName() . '</option>';

            if (is_a($o, 'App\Hierarchic')) {
                recursiveOptionsSelectObj($obj, $o->children, $indent + 1, $name, $multiple, $datafields);
            }
        }
    }
}

if ($multiple_select) {
    $postfix = '[]';
}

if (!isset($datafields)) {
    $datafields = [];
}

$select_class = 'form-control';
if ($extra_class) {
    $select_class .= ' '.$extra_class;
}
if ($triggering_modal !== false) {
    $select_class .= ' triggers-modal';
}

?>

<div class="form-group">
    @if($squeeze == false)
        <label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    @endif

    <div class="col-sm-{{ $fieldsize }}">
        <select class="{{ $select_class }}"
            @if($triggering_modal !== false)
                data-trigger-modal="{{ $triggering_modal }}"
            @endif

            @if($multiple_select)
                multiple size="10"
            @endif

            name="{{ $prefix . $name . $postfix }}">

                @if(!empty($extra_selection))
                    @foreach($extra_selection as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                @endif

                <?php recursiveOptionsSelectObj($obj, $objects, 0, $name, $multiple_select, $datafields) ?>

                @if($triggering_modal !== false)
                    <option value="run_modal">Crea Nuovo</option>
                @endif
        </select>

        @if(!empty($help_text))
            <span class="help-block">{{ $help_text }}</span>
        @endif
    </div>
</div>
