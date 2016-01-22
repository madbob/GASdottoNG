<div class="form-group">
	<label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
	<div class="col-sm-{{ $fieldsize }}">
		<label class="static-label text-muted">
			<?php

			if ($obj) {
				if (strstr($obj->$name, '0000-00-00') !== false) {
					echo 'Mai';
				}
				else {
					$d = strtotime($obj->$name);
					echo strftime('%A %d %B %G', $d);
				}
			}

			?>
		</label>
	</div>
</div>
