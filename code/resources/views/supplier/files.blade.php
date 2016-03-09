@if($supplier->userCan('supplier.modify'))

<div class="row">
	<div class="col-md-12">

		@include('commons.addingbutton', [
			'template' => 'attachment.base-edit',
			'typename' => 'attachment',
			'target_update' => 'attachment-list-' . $supplier->id,
			'typename_readable' => 'File',
			'targeturl' => 'attachments',
			'extra' => [
				'target_type' => 'App\Supplier',
				'target_id' => $supplier->id
			]
		])

	</div>
</div>

<div class="clearfix"></div>
<hr />

@endif

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'attachment-list-' . $supplier->id, 'items' => $supplier->attachments, 'url' => url('attachments/')])
	</div>
</div>
