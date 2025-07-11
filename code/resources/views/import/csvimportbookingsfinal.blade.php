<x-larastrap::modal :buttons="[['color' => 'success', 'label' => __('texts.generic.close'), 'classes' => ['reloader'], 'attributes' => ['data-bs-dismiss' => 'modal']]]">
    <p>
        {{ $title }}:
    </p>

	<ul class="list-group">
        @if(empty($objects))
            <li>{{ __('texts.generic.none') }}</li>
        @else
            @foreach($objects as $b)
                <li class="list-group-item">{{ $b->user->printableName() }}</li>
            @endforeach
        @endif
    </ul>

	@include('import.errors', ['errors' => $errors])
</x-larastrap::modal>
