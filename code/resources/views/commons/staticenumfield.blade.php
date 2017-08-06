<?php

if (!isset($empty_label))
    $empty_label = 'Nessuno';

?>

<div class="form-group">
    <label class="col-sm-{{ $labelsize }} control-label">{{ $label }}</label>
    <div class="col-sm-{{ $fieldsize }}">
        <label class="static-label text-muted">
            <?php

            $found = false;
            foreach($values as $v) {
                if($obj && $obj->$name == $v['value']) {
                    echo $v['label'];
                    $found = true;
                }
            }

            if ($found == false)
                echo $empty_label;

            ?>
        </label>
    </div>
</div>
