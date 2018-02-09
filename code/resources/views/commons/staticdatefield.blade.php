<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    <div class="col-sm-{{ $fieldsize }}">
        <label class="static-label text-muted">
        <?php

            if ($obj) {
                if ($obj->$name == null || $obj->$name == '0000-00-00') {
                    echo _i('Mai');
                } else {
                    echo $obj->printableDate($name);
                }
            }

        ?>
        </label>
    </div>
</div>
