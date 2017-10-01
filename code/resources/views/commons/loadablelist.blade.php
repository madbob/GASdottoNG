<?php

if(isset($legend) == false)
    $legend = null;
if(isset($filters) == false)
    $filters = [];
if(isset($empty_message) == false)
    $empty_message = 'Non ci sono elementi da visualizzare.';
if(isset($header_function) == false)
    $header_function = 'printableHeader';

$data = [];
if(isset($extra_data)) {
    foreach($extra_data as $name => $value)
        $data[] = sprintf('%s="%s"', $name, $value);
}
$data = join(' ', $data);

?>

@if(!empty($filters) || !is_null($legend))
    <div class="row">
        <div class="col-md-12 flowbox">
            <div class="form-group mainflow hidden-md">
                <input type="text" class="form-control list-text-filter" data-list-target="#{{ $identifier }}">
            </div>
            <div>
                @if(!empty($filters))
                    <div class="btn-group hidden-xs hidden-sm list-filters" role="group" aria-label="Filtri" data-list-target="#{{ $identifier }}">
                        @foreach($filters as $attribute => $info)
                            <button type="button" class="btn btn-default" data-filter-attribute="{{ $attribute }}"><span class="glyphicon glyphicon-{{ $info->icon }}" aria-hidden="true"></span>&nbsp;{{ $info->label }}</button>
                        @endforeach
                    </div>
                @endif

                @if(!is_null($legend))
                    @include('commons.iconslegend', ['class' => $legend->class, 'target' => '#' . $identifier])
                @endif
            </div>
        </div>
    </div>
@endif

<div id="wrapper-{{ $identifier }}">
    <div class="alert alert-info {{ count($items) != 0 ? 'hidden' : '' }}" role="alert" id="empty-{{ $identifier }}">
        {!! $empty_message !!}
    </div>

    <div class="list-group loadablelist" id="{{ $identifier }}" {!! $data !!}>
        @foreach($items as $item)
            @if(isset($url))
                <?php $u = url($url.'/'.$item->id) ?>
            @else
                <?php $u = $item->getShowURL() ?>
            @endif

            <?php

            $extra_class = '';
            $extra_attributes = '';

            foreach($filters as $attribute => $info) {
                if($item->$attribute != $info->value) {
                    $extra_class = 'hidden';
                    $extra_attributes = 'data-filtered-' . $attribute . '="true"';
                }
            }

            ?>

            <a data-element-id="{{ $item->id }}" {!! $extra_attributes !!} href="{{ $u }}" class="loadable-item list-group-item {{ $extra_class }}">{!! $item->$header_function() !!}</a>
        @endforeach
    </div>
</div>
