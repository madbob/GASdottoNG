@extends($theme_layout)

@section('content')

<div class="row">
	<div class="col-md-12">
		@if($currentgas->userCan('notifications.admin'))

		@include('commons.addingbutton', [
			'template' => 'notification.base-edit',
			'typename' => 'notification',
			'typename_readable' => 'Notifica',
			'targeturl' => 'notifications'
		])

		@endif
	</div>

	<div class="clearfix"></div>
	<hr/>
</div>

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'notification-list', 'items' => $notifications, 'url' => url('notifications/')])
	</div>
</div>

@endsection
