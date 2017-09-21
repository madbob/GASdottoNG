<?php

if (!isset($no_delete))
    $no_delete = false;
if (!isset($no_save))
    $no_save = false;

if (!isset($obj))
    $obj = null;

if (!isset($export_url))
    $export_url = false;

?>

<hr/>

<div class="row">
    <div class="col-md-12">
        @if($export_url)
            <a href="{{ $export_url }}" class="btn btn-default">Esporta</a>
        @endif

        <div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
            @if($no_delete == false)
                @if($obj && $obj->deleted_at != null)
                    <button type="button" class="btn btn-danger delete-button">Elimina Definitivamente</button>
                @else
                    <button type="button" class="btn btn-danger delete-button">Elimina</button>
                @endif
            @endif

            <button type="button" class="btn btn-default close-button">Chiudi</button>

            @if($no_save == false)
                <button type="submit" class="btn btn-success">Salva</button>
            @endif
        </div>
    </div>
</div>
