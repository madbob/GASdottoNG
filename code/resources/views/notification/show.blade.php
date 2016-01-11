<div class="row">
	<div class="col-md-12">
		@include('commons.staticstringfield', ['obj' => $notification, 'name' => 'content', 'label' => 'Contenuto'])
		@include('commons.staticobjectslistfield', ['obj' => $notification, 'name' => 'users', 'label' => 'Destinatari'])
	</div>
</div>
