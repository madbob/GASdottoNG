<x-larastrap::mform :obj="$attachment" method="PUT" :action="route('attachments.update', $attachment->id)" :nodelete="$attachment->internal" :nosave="$attachment->internal">
    <div class="row">
        <div class="col-md-12">
            @if($attachment->internal == false)
                <x-larastrap::text name="name" tlabel="generic.name" required />
                <x-larastrap::radios name="type" tlabel="generic.type" :options="['file' => __('generic.file'), 'url' => __('generic.url')]" classes="selective-display" :attributes="['data-target' => '.attachment_type']" />

                <div class="attachment_type" data-type="file">
                    <x-larastrap::file name="file" tlabel="generic.attachments.replace_file" />
                </div>
                <div class="attachment_type" data-type="url">
                    <x-larastrap::url name="url" tlabel="generic.attachments.replace_url" />
                </div>
            @else
                <x-larastrap::text name="name" tlabel="generic.name" disabled readonly />
            @endif

            @include('commons.multipleusers', ['obj' => $attachment, 'name' => 'users', 'label' => __('generic.recipients')])

            <x-larastrap::field tlabel="generic.attachments.view">
                @if($attachment->isImage())
                    <img src="{{ $attachment->download_url }}" class="img-fluid mb-2" alt="{{ $attachment->name }}">
                @endif

                <a class="btn btn-light" href="{{ $attachment->download_url }}">{{ __('generic.click_here') }} <i class="bi-download"></i></a>
            </x-larastrap::field>
        </div>
    </div>
</x-larastrap::mform>
