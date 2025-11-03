<div class="btn-group columns-selector" data-target="{{ $target }}">
    <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
        <i class="bi-layout-three-columns"></i>&nbsp;{{ __('texts.export.data.columns') }} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        @foreach($display_columns as $identifier => $metadata)
            <li>
                <div class="checkbox dropdown-item">
                    <label>
                        <input type="checkbox" value="{{ $identifier }}" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label ?? $metadata->name }}
                    </label>
                </div>
            </li>
        @endforeach
    </ul>
</div>
