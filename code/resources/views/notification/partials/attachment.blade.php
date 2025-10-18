@if($notification && $notification->attachments->isEmpty() == false)
    <x-larastrap::field tlabel="generic.attachment">
        @foreach($notification->attachments as $attachment)
            <a class="btn btn-info" href="{{ $attachment->download_url }}">
                {{ $attachment->name }} <i class="bi-download"></i>
            </a>
        @endforeach
    </x-larastrap::field>
@else
    <x-larastrap::file name="file" tlabel="generic.attachment" />
@endif
