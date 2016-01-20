<form class="form-horizontal main-form" method="PUT" action="{{ url('users/' . $user->id) }}">
	<div class="row">
		<div class="col-md-6">
			@include('user.base-edit', ['user' => $user])
			@include('commons.datefield', ['obj' => $user, 'name' => 'birthday', 'label' => 'Data di Nascita'])
			@include('commons.textfield', ['obj' => $user, 'name' => 'taxcode', 'label' => 'Codice Fiscale'])
			@include('commons.textfield', ['obj' => $user, 'name' => 'family_members', 'label' => 'Persone in Famiglia'])
		</div>
		<div class="col-md-6">
			@include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => 'Membro da'])

			@if($currentgas->oneCan('movements.view|movements.admin'))
				@include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => 'Numero Tessera'])
			@endif

			@include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => 'Ultimo Login'])
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
		</div>
	</div>

	@include('commons.formbuttons')
</form>
