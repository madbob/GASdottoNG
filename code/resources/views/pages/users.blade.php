@extends($theme_layout)

@section('content')

@if($currentgas->userCan('users.admin'))

@include('commons.addingbutton', [
	'template' => 'user.base-edit',
	'typename' => 'user',
	'typename_readable' => 'Utente',
	'targeturl' => 'users'
])

<hr/>

@endif

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'user-list', 'items' => $users, 'url' => url('users/')])
	</div>
</div>

@endsection
