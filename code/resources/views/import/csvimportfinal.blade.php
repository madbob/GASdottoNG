<x-larastrap::modal :buttons="[['color' => 'success', 'label' => __('texts.generic.close'), 'classes' => ['reloader'], 'attributes' => ['data-bs-dismiss' => 'modal']]]">
    <p>
        {{ $title }}:
    </p>

    <ul class="list-group">
        @if(empty($objects))
            <li>{{ __('texts.generic.none') }}</li>
        @else
            @foreach($objects as $m)
                <li class="list-group-item">{!! $m->printableName() !!}</li>
            @endforeach
        @endif
    </ul>

    @include('import.errors', ['errors' => $errors])
</x-larastrap::modal>
