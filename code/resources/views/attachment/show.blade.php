<x-larastrap::mform :buttons="[]">
    <div class="row">
        <div class="col-md-12">
            <x-larastrap::field tlabel="generic.attachments.view">
                @if($attachment->isImage())
                    <img src="{{ $attachment->download_url }}" class="img-fluid mb-2" alt="{{ $attachment->name }}">
                @endif

                <a class="btn btn-light" href="{{ $attachment->download_url }}">{{ __('texts.generic.click_here') }} <i class="bi-download"></i></a>
            </x-larastrap::field>
        </div>
    </div>
</x-larastrap::mform>
