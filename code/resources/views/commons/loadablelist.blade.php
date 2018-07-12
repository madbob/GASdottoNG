<?php

if(isset($legend) == false)
    $legend = null;
if(isset($filters) == false)
    $filters = [];
if(isset($empty_message) == false)
    $empty_message = _i('Non ci sono elementi da visualizzare.');
if(isset($header_function) == false)
    $header_function = 'printableHeader';
if(isset($sorting_rules) == false)
    $sorting_rules = [];

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
            @if(!empty($sorting_rules))
                <div class="btn-group loadablelist-sorter" data-list-target="#{{ $identifier }}">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        {{ _i('Ordina Per') }} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($sorting_rules as $attribute => $info)
                            <li>
                                <a href="#" data-sort-by="{{ $attribute }}">{{ $info }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>&nbsp;
            @endif
            <div>
                @if(!empty($filters))
                    <div class="btn-group hidden-xs hidden-sm list-filters" role="group" aria-label="Filtri" data-list-target="#{{ $identifier }}">
                        @foreach($filters as $attribute => $info)
                            <button type="button" class="btn btn-default" data-filter-attribute="{{ $attribute }}"><span class="glyphicon glyphicon-{{ $info->icon }}" aria-hidden="true"></span>&nbsp;{{ $info->label }}</button>
                        @endforeach
                    </div>&nbsp;
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
            <?php

            if(isset($url))
                $u = url($url . '/' . $item->id);
            else
                $u = $item->getShowURL();

            $extra_class = '';
            $extra_attributes = [];

            foreach($filters as $attribute => $info) {
                if($item->$attribute != $info->value) {
                    $extra_class = 'hidden';
                    $extra_attributes[] = 'data-filtered-' . $attribute . '="true"';
                }
            }

            foreach($sorting_rules as $attribute => $info) {
                $extra_attributes[] = sprintf('data-sorting-%s="%s"', $attribute, $item->$attribute);
            }

            ?>

            <a data-element-id="{{ $item->id }}" {!! join(' ', $extra_attributes) !!} href="{{ $u }}" class="loadable-item list-group-item {{ $extra_class }}">{!! is_callable($header_function) ? $header_function($item) : $item->$header_function() !!}</a>
        @endforeach
    </div>
</div>
