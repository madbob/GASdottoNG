@can('supplier.modify', $supplier)
    <div class="row">
        <div class="col-md-12">
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
@endcan

<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', ['identifier' => 'attachment-list-' . $supplier->id, 'items' => $supplier->attachments])
    </div>
</div>
