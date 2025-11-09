@if($obj->attachments->isEmpty() == false)
    <x-larastrap::card header="generic.shared_files">
        <div class="list-group">
            @foreach($obj->attachments as $attachment)
                <a href="{{ $attachment->download_url }}" class="list-group-item list-group-item-action" target="_blank">
                    {{ $attachment->name }}
                    <i class="bi-download float-end"></i>
                </a>
            @endforeach
        </div>
    </x-larastrap::card>
@endif
