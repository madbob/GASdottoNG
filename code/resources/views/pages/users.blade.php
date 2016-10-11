@extends($theme_layout)

@section('content')

<div class="row">
	<div class="col-md-12">
		@if($currentgas->userCan('users.admin'))

		@include('commons.addingbutton', [
			'template' => 'user.base-edit',
			'typename' => 'user',
			'typename_readable' => 'Utente',
			'targeturl' => 'users'
		])

		@endif
	</div>
</div>

<div class="clearfix"></div>
<hr/>

@include('commons.iconslegend', ['class' => 'User'])

<div class="row">
	<div class="col-md-12">
		@include('commons.loadablelist', ['identifier' => 'user-list', 'items' => $users])
	</div>
</div>

@endsection
