<div class="form-group">
	<label class="col-sm-3 control-label">{{ $label }}</label>
	<div class="col-sm-9">
		<label class="control-label">
			<?php

			if ($obj) {
				if (strstr($obj->$name, '0000-00-00') != -1) {
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
