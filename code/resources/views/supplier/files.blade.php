@can('supplier.modify', $supplier)
    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'attachment.base-edit',
                'typename' => 'attachment',
                'target_update' => 'attachment-list-' . $supplier->id,
                'typename_readable' => _i('File'),
                'targeturl' => 'attachments',
                'extra' => [
                    'target_type' => 'App\Supplier',
                    'target_id' => $supplier->id
                ]
            ])
        </div>
    </div>

    <div class="clearfix"></div>
    <br />
    <div class="row">
        <div class="col">
            @include('commons.loadablelist', [
                'identifier' => 'attachment-list-' . $supplier->id,
                'items' => $supplier->attachments,
                'legend' => (object)[
                    'class' => App\Attachment::class
                ],
            ])
        </div>
    </div>
@else
    <?php $images = [] ?>

    <div class="row">
        <div class="col">
            <div class="panel-body">
                <div class="list-group">
                    @foreach($supplier->attachments as $attachment)
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
                            <img src="{{ $img->download_url }}">
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endcan
