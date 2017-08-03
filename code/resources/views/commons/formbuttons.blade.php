<?php

if (!isset($no_delete))
    $no_delete = false;
if (!isset($no_save))
    $no_save = false;

?>

<hr/>

<div class="row">
    <div class="col-md-12">
        <div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
            @if($no_delete == false)
                <button type="button" class="btn btn-danger delete-button">Elimina</button>
            @endif
            <button type="button" class="btn btn-default close-button">Chiudi</button>
            @if($no_save == false)
                <button type="submit" class="btn btn-success">Salva</button>
            @endif
        </div>
    </div>
</div>
