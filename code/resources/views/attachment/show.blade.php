<x-larastrap::mform classes="attachment-editor" :buttons="[]">
    <div class="row">
        <div class="col-md-12">
            <x-larastrap::field :label="_i('Scarica')">
                <a class="btn btn-light" href="{{ $attachment->download_url }}">{{ _i('Clicca Qui') }} <i class="bi-download"></i></a>
            </x-larastrap::field>
        </div>
    </div>
</x-larastrap::mform>
