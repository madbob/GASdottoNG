<x-larastrap::modal :title="_i('Importa CSV')" :buttons="[['color' => 'success', 'label' => _i('Chiudi'), 'classes' => ['reloader'], 'attributes' => ['data-bs-dismiss' => 'modal']]]">
    <p>
        {{ $title }}:
    </p>

	<ul class="list-group">
        @if(empty($objects))
            <li>{{ _i('Nessuno') }}</li>
        @else
            @foreach($objects as $b)
                <li class="list-group-item">{{ $b->user->printableName() }}</li>
            @endforeach
        @endif
    </ul>

	@include('import.errors', ['errors' => $errors])
</x-larastrap::modal>
