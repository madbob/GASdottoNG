<?php

if (isset($target_update) == false) {
    $target_update = $typename.'-list';
}

?>

<button type="button" class="btn btn-warning pull-right" data-toggle="modal" data-target="#create{{ ucfirst($typename) }}">Crea Nuovo {{ $typename_readable }}</button>

<div class="modal fade" id="create{{ ucfirst($typename) }}" tabindex="-1" role="dialog" aria-labelledby="create{{ ucfirst($typename) }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal creating-form" method="POST" action="/{{ $targeturl }}" data-toggle="validator">
                <input type="hidden" name="update-list" value="{{ $target_update }}">
                @if(isset($extra))
                    @foreach($extra as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                @endif

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Crea Nuovo {{ $typename_readable }}</h4>
                </div>
                <div class="modal-body">
                    @include($template, [$typename => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>
