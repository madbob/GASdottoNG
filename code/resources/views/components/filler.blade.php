<div class="form-horizontal form-filler" data-action="{{ $dataAction }}" data-toggle="validator" data-fill-target="{{ $dataFillTarget }}">
    {{ $slot }}

    <x-larastrap::field label="">
        <button type="submit" class="btn btn-info">{{ __('generic.search.all') }}</button>

		@foreach($actionButtons as $button)
            <button type="submit" data-action="{{ $button['link'] }}" class="btn btn-info form-action">{{ $button['label'] }}</button>
        @endforeach

        @foreach($downloadButtons as $button)
            <a href="{{ $button['link'] }}" class="btn btn-light form-download">{{ $button['label'] }} <i class="bi-download"></i></a>
        @endforeach
    </x-larastrap::field>
</div>
