<x-larastrap::mform :buttons="[]">
    <div class="row">
        <div class="col-md-12">
            <x-larastrap::field :label="_i('Visualizza o Scarica')">
                @if($attachment->isImage())
                    <img src="{{ $attachment->download_url }}" class="img-fluid mb-2">
                @endif

                <a class="btn btn-light" href="{{ $attachment->download_url }}">{{ _i('Clicca Qui') }} <i class="bi-download"></i></a>
            </x-larastrap::field>
        </div>
    </div>
</x-larastrap::mform>
