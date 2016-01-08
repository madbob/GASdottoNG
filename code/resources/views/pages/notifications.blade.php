@extends($theme_layout)

@section('content')

@if($currentgas->userCan('notifications.admin'))

@include('commons.addingbutton', [
	'template' => 'notification.base-edit',
	'typename' => 'notification',
	'typename_readable' => 'Notifica',
	'targeturl' => 'notifications'
])

<hr/>

@endif

<div class="row">
	<div class="loadmore-grid" data-url="{{ url('notifications') }}" data-offset="0">
		<div class="contents">
			@foreach($notifications as $notify)
			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">
							<span classe="title">{{ $notify->printableName() }}</span> / <span classe="date">{{ $notify->printableDate('created_at') }}</span>
						</h3>
					</div>
					<div class="panel-body">
						{{ $notify->content }}
					</div>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</div>

@endsection
