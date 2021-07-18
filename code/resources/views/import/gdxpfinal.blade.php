<x-larastrap::modal :title="_i('Importa GDXP')">
    <div class="wizard_page">
        <p>
            {{ _i('Fornitori importati') }}:
        </p>

        <ul class="list-group">
            @if(empty($data))
                <li>{{ _i('Nessuno') }}</li>
            @else
                @foreach($data as $supplier)
                    <li class="list-group-item">{{ $supplier->printableName() }}</li>
                @endforeach
            @endif
        </ul>
    </div>
</x-larastrap::modal>
