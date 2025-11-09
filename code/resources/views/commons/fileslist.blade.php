@if($editable)
    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'attachment.base-edit',
                'typename' => 'attachment',
                'target_update' => 'attachment-list-' . $obj->id,
                'typename_readable' => __('texts.generic.file'),
                'targeturl' => 'attachments',
                'extra' => [
                    'target_type' => get_class($obj),
                    'target_id' => $obj->id
                ]
            ])
        </div>
    </div>

    <div class="clearfix"></div>
    <br />
    <div class="row">
        <div class="col">
            @include('commons.loadablelist', [
                'identifier' => 'attachment-list-' . $obj->id,
                'items' => $obj->attachments,
                'legend' => (object)[
                    'class' => App\Attachment::class
                ],
            ])
        </div>
    </div>
@else
    @if($obj->attachments->count() == 0)
        <x-larastrap::suggestion>
            {!! __('texts.generic.empty_list') !!}
        </x-larastrap::suggestion>
    @else
        <?php $images = [] ?>

        <div class="row">
            <div class="col">
                <div class="panel-body">
                    <div class="list-group">
                        @foreach($obj->attachments as $attachment)
                            @if($attachment->isImage())
                                <?php $images[] = $attachment ?>
                            @else
                                <a href="{{ $attachment->download_url }}" class="list-group-item">{{ $attachment->name }}</a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($images))
            <div class="row">
                <div class="col">
                    <div class="gallery">
                        @foreach($images as $img)
                            <?php $size = $img->getSize() ?>
                            <span style="--w: {{ $size[0] }}; --h: {{ $size[1] }}">
                                <img src="{{ $img->download_url }}" alt="{{ $obj->printableName() }}">
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif
@endcan
