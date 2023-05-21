<?php

if(isset($legend) == false) {
    $legend = null;
}
if(isset($filters) == false) {
    $filters = [];
}
if(isset($empty_message) == false) {
    $empty_message = _i('Non ci sono elementi da visualizzare.');
}
if(isset($header_function) == false) {
    $header_function = 'printableHeader';
}
if(isset($sorting_rules) == false) {
    $sorting_rules = [];
}
if(!isset($extra_data)) {
    $extra_data = [];
}

$injected_items = [];

foreach($sorting_rules as $attribute => $info) {
    if (is_object($info)) {
        $get_headers = $info->get_headers;

        foreach($get_headers($items) as $c) {
            $injected_items[] = (object) [
                'label' => $c,
                'related_sorting' => $attribute
            ];
        }
    }
}

$no_filters = (empty($sorting_rules) && empty($filters) && is_null($legend));

?>

<div class="row d-none d-md-flex mb-1">
    <div class="col flowbox">
        <div class="form-group {{ $no_filters ? 'w-100' : 'mainflow' }} d-none d-xl-block">
            <input type="text" class="form-control list-text-filter" data-list-target="#{{ $identifier }}" placeholder="{{ _i('Filtra') }}">
        </div>

        @if(!empty($sorting_rules))
            <div class="dropdown loadablelist-sorter" data-list-target="#{{ $identifier }}">
                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    {{ _i('Ordina Per') }} <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    @foreach($sorting_rules as $attribute => $info)
                        <li>
                            @if(is_object($info))
                                <a href="#" class="dropdown-item" data-sort-by="{{ $attribute }}">{{ $info->label }}</a>
                            @else
                                <a href="#" class="dropdown-item" data-sort-by="{{ $attribute }}">{{ $info }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>&nbsp;
        @endif

        @if(!empty($filters))
            <div class="btn-group list-filters" data-list-target="#{{ $identifier }}">
                @foreach($filters as $attribute => $info)
                    <button type="button" class="btn btn-light" data-filter-attribute="{{ $attribute }}"><i class="bi-{{ $info->icon }}"></i>&nbsp;{{ $info->label }}</button>
                @endforeach
            </div>&nbsp;
        @endif

        @if(!is_null($legend))
            @include('commons.iconslegend', ['class' => $legend->class, 'target' => '#' . $identifier, 'contents' => $items])
        @endif
    </div>
</div>

<div id="wrapper-{{ $identifier }}">
    <div class="alert alert-info {{ count($items) != 0 ? 'd-none' : '' }}" role="alert" id="empty-{{ $identifier }}">
        {!! $empty_message !!}
    </div>

    <x-larastrap::accordion :id="$identifier" classes="loadable-list" :attributes="$extra_data">
        @foreach($injected_items as $item)
            <div class="loadable-sorting-header list-group-item hidden bg-light" data-sorting-{{ $item->related_sorting }}="{{ $item->label }}">{{ $item->label }}</div>
        @endforeach

        @foreach($items as $index => $item)
            <?php

            $row_identifier = $identifier . '-' . sanitizeId($index);

            if(isset($url)) {
                $u = url($url . '/' . $item->id);
            }
            else {
                $u = $item->getShowURL();
            }

            $extra_class = '';
            $extra_attributes = [
                'data-accordion-url' => $u,
                'data-element-id' => $item->id,
            ];

            foreach($filters as $attribute => $info) {
                if($item->$attribute != $info->value) {
                    $extra_class = 'd-none';
                    $extra_attributes['data-filtered-' . $attribute] = 'true';
                }
            }

            foreach($sorting_rules as $attribute => $info) {
                $extra_attributes['data-sorting-' . $attribute] = $item->$attribute;
            }

            $header = is_callable($header_function) ? $header_function($item) : $item->$header_function();

            ?>

            <x-larastrap::remoteaccordion :id="$row_identifier" :label_html="$header" :classes="$extra_class" :attributes="$extra_attributes" active="false">
            </x-larastrap::remoteaccordion>
        @endforeach
    </x-larastrap::accordion>
</div>
