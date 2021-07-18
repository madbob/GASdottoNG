<x-larastrap::modal :title="_i('Importa CSV')" :buttons="[['color' => 'success', 'label' => _i('Chiudi'), 'classes' => ['reloader'], 'attributes' => ['data-bs-dismiss' => 'modal']]]">
    <p>
        {{ $title }}:
    </p>

    <ul class="list-group">
        @if(empty($objects))
            <li>{{ _i('Nessuno') }}</li>
        @else
            @foreach($objects as $m)
                <li class="list-group-item">{!! $m->printableName() !!}</li>
            @endforeach
        @endif
    </ul>

    @if(!empty($errors))
        <hr/>

        <p>
            {{ _i('Errori') }}:
        </p>

        <ul class="list-group">
            @foreach($errors as $e)
                <li class="list-group-item">{!! $e !!}</li>
            @endforeach
        </ul>
    @endif
</x-larastrap::modal>
