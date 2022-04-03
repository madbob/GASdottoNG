<x-larastrap::mform :obj="$attachment" method="PUT" :action="route('attachments.update', $attachment->id)">
    <div class="row">
        <div class="col-md-12">
            @if($attachment->internal == false)
                <x-larastrap::text name="name" :label="_i('Nome')" required />
                <x-larastrap::file name="file" :label="_i('Sostituisci File')" />
            @else
                <x-larastrap::text name="name" :label="_i('Nome')" disabled readonly />
            @endif

            @include('commons.multipleusers', ['obj' => $attachment, 'name' => 'users', 'label' => _i('Destinatari')])

            <x-larastrap::field :label="_i('Scarica')">
                @if($attachment->isImage())
                    <img src="{{ $attachment->download_url }}" class="img-fluid mb-2">
                @endif

                <a class="btn btn-light" href="{{ $attachment->download_url }}">{{ _i('Clicca Qui') }} <i class="bi-download"></i></a>
            </x-larastrap::field>
        </div>
    </div>
</x-larastrap::mform>
