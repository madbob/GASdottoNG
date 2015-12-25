<div class="form-group">
	@if($squeeze == false)
	<label for="{{ $prefix . $name . $postfix }}" class="col-sm-3 control-label">{{ $label }}</label>
	@endif

	<div class="col-sm-{{ $fieldsize }}">
		<input
			class="tagsinput"
			name="{{ $prefix . $name . $postfix }}"

			value="<?php

			if($obj) {
				$tags = [];

				foreach($obj->$name as $v)
					$tags[] = $v->$tagfield;

				echo join(',', $tags);
			}

			?>"

			autocomplete="off" />
	</div>
</div>
