<?php

if (function_exists('recursiveOptionsSelectObj') == false) {
	function recursiveOptionsSelectObj($obj, $options, $indent) {
		$option_prefix = str_repeat('&nbsp;&nbsp;', $indent);
		foreach ($options as $o) {
			?>
			<option value="{{ $o->id }}"<?php if($obj && $obj->$name == $o->id) echo ' selected="selected"' ?>>{{ $option_prefix . $o->printableName() }}</option>
			<?php

			if (is_a($o, 'App\Hierarchic'))
				recursiveOptionsSelectObj($obj, $o->children, $indent + 1);
		}
	}
}

?>

<div class="form-group">
	<label for="{{ $prefix . $name }}" class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<select class="form-control<?php if($triggering_modal !== false) echo ' triggers-modal" data-trigger-modal="' . $triggering_modal ?>" name="{{ $prefix . $name }}">
			@if(!empty($none_selection))
			<option value="null"<?php if($obj == null) echo ' selected="selected"' ?>>{{ $none_selection }}</option>
			@endif

			<?php recursiveOptionsSelectObj($obj, $objects, 0) ?>

			@if($triggering_modal !== false)
			<option value="run_modal">Crea Nuovo</option>
			@endif
		</select>
	</div>
</div>
