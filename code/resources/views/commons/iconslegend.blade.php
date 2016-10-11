<?php $icons = App\GASModel::iconsLegend($class) ?>

@if(!empty($icons))
        <div class="btn-group pull-right hidden-xs hidden-sm icons-legend" role="group" aria-label="Legenda">
                @foreach($icons as $icon => $label)
                        <button type="button" class="btn btn-default"><span class="glyphicon glyphicon-{{ $icon }}" aria-hidden="true"></span>&nbsp;{{ $label }}</button>
                @endforeach
        </div>
@endif
