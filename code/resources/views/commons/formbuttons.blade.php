<?php

if (!isset($no_delete))
    $no_delete = false;
if (!isset($no_save))
    $no_save = false;

if (!isset($obj))
    $obj = null;

if (!isset($left_buttons))
    $left_buttons = false;

?>

<hr/>

<div class="row">
    <div class="col-md-12">
        @if($left_buttons)
            @foreach($left_buttons as $lb)
                @if(isset($lb->custom))
                    {!! $lb->custom !!}
                @else
                    <a href="{{ $lb->url }}" class="btn btn-default {{ $lb->class }}">{{ $lb->label }}</a>
                @endif
            @endforeach
        @endif

        <div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
            @if($no_delete == false)
                @if($obj && $obj->deleted_at != null)
                    <button type="button" class="btn btn-danger delete-button">{{ _i('Elimina Definitivamente') }}</button>
                @else
                    <button type="button" class="btn btn-danger delete-button">{{ _i('Elimina') }}</button>
                @endif
            @endif

            <button type="button" class="btn btn-default close-button">{{ _i('Chiudi') }}</button>

            @if($no_save == false)
                <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
            @endif
        </div>
    </div>
</div>
