<?php

if (function_exists('recursiveOptionsSelectObj') == false) {
	function recursiveOptionsSelectObj($obj, $options, $indent, $name, $multiple) {
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
			}
			else {
				$selected = ($obj && $obj->$name == $o->id);
			}

			?>
			<option value="{{ $o->id }}"<?php if($selected) echo ' selected="selected"' ?>>{{ $option_prefix . $o->printableName() }}</option>
			<?php

			if (is_a($o, 'App\Hierarchic'))
				recursiveOptionsSelectObj($obj, $o->children, $indent + 1, $name, $multiple);
		}
	}
}

if ($multiple_select)
	$postfix = '[]';

?>

<div class="form-group">
	<label for="{{ $prefix . $name . $postfix }}" class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<select class="form-control<?php if($triggering_modal !== false) echo ' triggers-modal" data-trigger-modal="' . $triggering_modal ?>" name="{{ $prefix . $name . $postfix }}"<?php if($multiple_select) echo ' multiple size="10"' ?>>
			@if(!empty($extra_selection))
				@foreach($extra_selection as $value => $label)
				<option value="{{ $value }}">{{ $label }}</option>
				@endforeach
			@endif

			<?php recursiveOptionsSelectObj($obj, $objects, 0, $name, $multiple_select) ?>

			@if($triggering_modal !== false)
			<option value="run_modal">Crea Nuovo</option>
			@endif
		</select>

		@if(!empty($help_text))
		<span class="help-block">{{ $help_text }}</span>
		@endif
	</div>
</div>
