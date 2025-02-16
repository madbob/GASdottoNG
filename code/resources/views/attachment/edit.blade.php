<x-larastrap::mform :obj="$attachment" method="PUT" :action="route('attachments.update', $attachment->id)" :nodelete="$attachment->internal" :nosave="$attachment->internal">
    <div class="row">
        <div class="col-md-12">
            @if($attachment->internal == false)
                <x-larastrap::text name="name" :label="_i('Nome')" required />
                <x-larastrap::radios name="type" :label="_i('Tipo')" :options="['file' => _i('File'), 'url' => _i('URL')]" classes="selective-display" :attributes="['data-target' => '.attachment_type']" />

                <div class="attachment_type" data-type="file">
                    <x-larastrap::file name="file" :label="_i('Sostituisci File')" />
                </div>
                <div class="attachment_type" data-type="url">
                    <x-larastrap::url name="url" :label="_i('Sostituisci URL')" />
                </div>
            @else
                <x-larastrap::text name="name" :label="_i('Nome')" disabled readonly />
            @endif

            @include('commons.multipleusers', ['obj' => $attachment, 'name' => 'users', 'label' => _i('Destinatari')])

            <x-larastrap::field :label="_i('Visualizza o Scarica')">
                @if($attachment->isImage())
                    <img src="{{ $attachment->download_url }}" class="img-fluid mb-2" alt="{{ $attachment->name }}">
                @endif

                <a class="btn btn-light" href="{{ $attachment->download_url }}">{{ _i('Clicca Qui') }} <i class="bi-download"></i></a>
            </x-larastrap::field>
        </div>
    </div>
</x-larastrap::mform>
