<div class="btn-group columns-selector" data-target="{{ $target }}">
    <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
        <i class="bi-layout-three-columns"></i>&nbsp;{{ _i('Colonne') }} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        @foreach($display_columns as $identifier => $metadata)
            <li>
                <div class="checkbox dropdown-item">
                    <label>
                        <input type="checkbox" value="{{ $identifier }}" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label }}
                    </label>
                </div>
            </li>
        @endforeach
    </ul>
</div>
