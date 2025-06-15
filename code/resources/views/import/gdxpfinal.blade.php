<x-larastrap::modal>
    <div class="wizard_page">
        <p>{{ __('texts.imports.imported_suppliers') }}:</p>

        <ul class="list-group">
            @if(empty($data))
                <li>{{ __('texts.generic.none') }}</li>
            @else
                @foreach($data as $supplier)
                    <li class="list-group-item">{{ $supplier->printableName() }}</li>
                @endforeach
            @endif
        </ul>
    </div>
</x-larastrap::modal>
